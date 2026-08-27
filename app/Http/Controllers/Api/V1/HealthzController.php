<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class HealthzController extends Controller
{
    public function __invoke()
    {
        $s3Status = 'error';
        try {
            Storage::disk('s3')->files();
            $s3Status = 'connected (MinIO)';
        } catch (\Throwable $e) {
            $s3Status = 'error: '.$e->getMessage();
        }

        $dbStatus = 'error';
        try {
            $dbStatus = DB::connection()->getPdo() ? 'connected (PgBouncer)' : 'error';
        } catch (\Throwable $e) {
            $dbStatus = 'error: '.$e->getMessage();
        }

        $valkeyStatus = 'error';
        try {
            $valkeyStatus = Redis::connection()->ping() ? 'connected' : 'error';
        } catch (\Throwable $e) {
            $valkeyStatus = 'error: '.$e->getMessage();
        }

        return response()->json([
            'octane' => app()->bound('octane') ? 'active (FrankenPHP)' : 'fallback (standard CLI/FPM)',
            'database' => $dbStatus,
            'valkey' => $valkeyStatus,
            's3_storage' => $s3Status,
        ]);
    }
}
