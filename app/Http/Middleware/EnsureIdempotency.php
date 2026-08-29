<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class EnsureIdempotency
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only for write operations
        if (! in_array($request->method(), ['POST', 'PATCH', 'PUT'])) {
            return $next($request);
        }

        $idempotencyKey = $request->header('X-Idempotency-Key') ?? $request->input('idempotency_key');

        // Missing key validation
        if (! $idempotencyKey) {
            throw ValidationException::withMessages([
                'idempotency_key' => 'An idempotency key is required for this transaction.',
            ]);
        }

        // key generation based on payload
        $userId = $request->user()?->id ?? 'guest';
        $payloadHash = hash('sha256', $request->path().'|'.json_encode($request->except(['_token', 'idempotency_key'])));

        $redisKey = "idempotency:{$userId}:{$idempotencyKey}";
        $lockKey = "lock:idempotency:{$userId}:{$idempotencyKey}";

        // Check if settled
        $existing = Redis::get($redisKey);
        if ($existing) {
            $cached = json_decode($existing, true);

            if ($cached['payload_hash'] !== $payloadHash) {
                throw ValidationException::withMessages([
                    'transaction' => 'This idempotency key was previously used with different details.',
                ]);
            }

            if ($request->header('X-Inertia')) {
                return back()->with('info', 'Transaction already confirmed.');
            }

            return response($cached['body'], $cached['status'])->withHeaders(array_merge($cached['headers'], ['X-Cache-Lookup' => 'HIT']));
        }

        // Concurrency Lock: 10s TTL
        $acquired = Redis::set($lockKey, 'PROCESSING', 'EX', 10, 'NX');
        if (! $acquired) {
            throw ValidationException::withMessages([
                'transaction' => 'A transaction is currently in progress. Please wait a moment.',
            ]);
        }

        try {
            /** @var Response $response */
            $response = $next($request);

            if ($response->getStatusCode() < 400 && ! $request->header('X-Inertia')) {
                $cacheData = [
                    'payload_hash' => $payloadHash,
                    'status' => $response->getStatusCode(),
                    'headers' => ['Content-Type' => $response->headers->get('Content-Type')],
                    'body' => $response->getContent(),
                ];
                Redis::setex($redisKey, 86400, json_encode($cacheData));
            }

            return $response;
        } finally {
            Redis::del($lockKey);
        }
    }
}
