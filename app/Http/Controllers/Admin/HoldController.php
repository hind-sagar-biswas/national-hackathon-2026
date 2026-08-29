<?php

namespace App\Http\Controllers\Admin;

use App\Enums\HoldStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Hold\IndexRequest;
use App\Http\Resources\HoldResource;
use App\Models\Hold;
use App\Services\Banking\HoldService;
use HindBiswas\ModelUtils\Utils\EnumUtil;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class HoldController extends Controller
{
    public function index(IndexRequest $request)
    {
        $filters = $request->validated();
        $filters['status'] = $filters['status'] ?? null;
        $filters['search'] = $filters['search'] ?? null;
        $filters['date_from'] = $filters['date_from'] ?? null;
        $filters['date_to'] = $filters['date_to'] ?? null;

        $query = Hold::query()
            ->with(['account.user'])
            ->orderBy('created_at', 'desc');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'ilike', "%{$search}%")
                    ->orWhereHas('account.user', function ($uq) use ($search) {
                        $uq->where('name', 'ilike', "%{$search}%")
                            ->orWhere('email', 'ilike', "%{$search}%")
                            ->orWhere('phone', 'ilike', "%{$search}%");
                    });
            });
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $holds = $query->paginate(config('app.feature.pagination'))->withQueryString();

        $totalHeldActive = (int) Hold::where('status', HoldStatus::ACTIVE)->sum('amount');
        $activeCount = Hold::where('status', HoldStatus::ACTIVE)->count();
        $capturedCount = Hold::where('status', HoldStatus::CAPTURED)->count();
        $releasedCount = Hold::where('status', HoldStatus::RELEASED)->count();

        return inertia('Admin/Holds/Index', [
            'list' => Inertia::defer(fn () => HoldResource::collection($holds)),
            'filters' => $filters,
            'statusOptions' => EnumUtil::toOptions(HoldStatus::class),
            'metrics' => [
                'total_held_active' => $totalHeldActive,
                'active_count' => $activeCount,
                'captured_count' => $capturedCount,
                'released_count' => $releasedCount,
            ],
        ]);
    }

    public function release(Hold $hold, HoldService $holdService)
    {
        if ($hold->status !== HoldStatus::ACTIVE) {
            throw ValidationException::withMessages([
                'hold' => "Hold #{$hold->id} is already in '{$hold->status->value}' status.",
            ]);
        }

        try {
            $holdService->releaseHold($hold);

            return back()->with('success', "Hold #{$hold->id} for {$hold->amount} BDT has been released back to user wallet.");
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'hold' => $e->getMessage(),
            ]);
        }
    }
}
