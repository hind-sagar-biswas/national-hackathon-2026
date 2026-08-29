<?php

namespace App\Http\Controllers;

use App\Enums\BillSplitMode;
use App\Enums\BillSplitParticipantStatus;
use App\Enums\BillSplitStatus;
use App\Http\Requests\BillSplit\IndexRequest;
use App\Http\Requests\BillSplit\StoreRequest;
use App\Http\Resources\BillSplitResource;
use App\Models\BillSplit;
use App\Models\BillSplitParticipant;
use App\Models\User;
use App\Services\Banking\BillSplitService;
use HindBiswas\ModelUtils\Utils\EnumUtil;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class BillSplitController extends Controller
{
    public function index(IndexRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $filters = $request->validated();
        $tab = $filters['tab'] ?? 'participating';
        $filters['tab'] = $tab;
        $filters['status'] = $filters['status'] ?? null;
        $filters['mode'] = $filters['mode'] ?? null;
        $filters['search'] = $filters['search'] ?? null;

        $query = BillSplit::query()
            ->with(['initiatorUser', 'participants.user'])
            ->orderBy('created_at', 'desc');

        if ($tab === 'created') {
            $query->where('initiator_user_id', $user->id);
        } else {
            $query->whereHas('participants', fn ($q) => $q->where('user_id', $user->id));
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['mode'])) {
            $query->where('mode', $filters['mode']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('merchant_name', 'ilike', "%{$search}%")
                    ->orWhere('note', 'ilike', "%{$search}%");
            });
        }

        $billSplits = $query->paginate(config('app.feature.pagination'))->withQueryString();

        $pendingCount = BillSplit::whereHas('participants', fn ($q) => $q->where('user_id', $user->id)->where('status', BillSplitParticipantStatus::PENDING))
            ->where('status', BillSplitStatus::PENDING)
            ->count();

        return inertia('BillSplits/Index', [
            'list' => Inertia::defer(fn () => BillSplitResource::collection($billSplits)),
            'filters' => $filters,
            'modeOptions' => EnumUtil::toOptions(BillSplitMode::class),
            'statusOptions' => EnumUtil::toOptions(BillSplitStatus::class),
            'counts' => [
                'pending_action' => $pendingCount,
            ],
        ]);
    }

    public function show(BillSplit $billSplit)
    {
        /** @var User $user */
        $user = Auth::user();

        $isParticipant = $billSplit->participants()->where('user_id', $user->id)->exists();
        if (! $isParticipant && (int) $billSplit->initiator_user_id !== (int) $user->id && ! $user->hasRole('admin')) {
            abort(403, 'You are not authorized to view this bill split.');
        }

        $billSplit->load(['initiatorUser', 'participants.user', 'participants.hold']);

        $currentUserParticipant = $billSplit->participants->firstWhere('user_id', $user->id);

        return inertia('BillSplits/Show', [
            'billSplit' => BillSplitResource::make($billSplit),
            'currentUserParticipant' => $currentUserParticipant ? [
                'id' => $currentUserParticipant->id,
                'status' => $currentUserParticipant->status->value,
                'share_amount' => [
                    'raw' => $currentUserParticipant->share_amount,
                    'formatted' => formatPaisa($currentUserParticipant->share_amount),
                ],
                'is_initiator' => $currentUserParticipant->is_initiator,
            ] : null,
        ]);
    }

    public function store(StoreRequest $request, BillSplitService $billSplitService)
    {
        /** @var User $user */
        $user = Auth::user();

        $data = $request->validated();
        $data['total_amount'] = toPaisa($data['total_amount']);

        try {
            $billSplit = $billSplitService->createBillSplit($user, $data);

            $formattedTotal = formatPaisa($billSplit->total_amount);

            return back()->with('success', "Bill split \"{$billSplit->title}\" of {$formattedTotal} BDT created and invitations sent to participants.");
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'title' => $e->getMessage(),
            ]);
        }
    }

    public function accept(BillSplit $billSplit, BillSplitService $billSplitService)
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var BillSplitParticipant|null $participant */
        $participant = $billSplit->participants()->where('user_id', $user->id)->first();

        if (! $participant) {
            abort(403, 'You are not a participant in this bill split.');
        }

        try {
            $updatedBillSplit = $billSplitService->acceptParticipant($participant);

            if ($updatedBillSplit->status === BillSplitStatus::COMPLETED) {
                return back()->with('success', "All participants have accepted! Bill split \"{$updatedBillSplit->title}\" has completed and settled.");
            }

            return back()->with('success', 'You have accepted the split. Your share has been reserved on hold until all participants accept.');
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'bill_split' => $e->getMessage(),
            ]);
        }
    }

    public function reject(BillSplit $billSplit, BillSplitService $billSplitService)
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var BillSplitParticipant|null $participant */
        $participant = $billSplit->participants()->where('user_id', $user->id)->first();

        if (! $participant) {
            abort(403, 'You are not a participant in this bill split.');
        }

        try {
            $billSplitService->rejectParticipant($participant, request('reason'));

            return back()->with('success', 'You have declined the bill split request.');
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'bill_split' => $e->getMessage(),
            ]);
        }
    }

    public function destroy(BillSplit $billSplit, BillSplitService $billSplitService)
    {
        /** @var User $user */
        $user = Auth::user();

        try {
            $billSplitService->cancelBillSplit($billSplit, $user);

            return back()->with('success', 'Bill split has been cancelled and any reserved holds have been released.');
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'bill_split' => $e->getMessage(),
            ]);
        }
    }
}
