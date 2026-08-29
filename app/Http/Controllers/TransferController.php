<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\Transfer\IndexRequest;
use App\Http\Requests\Transfer\ResendOtpRequest;
use App\Http\Requests\Transfer\StoreRequest;
use App\Http\Resources\AccountResource;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Auth\OtpService;
use App\Services\Banking\TransferService;
use App\Services\Risk\Exceptions\RiskChallengeRequiredException;
use App\Services\Risk\Exceptions\TransactionHeldForReviewException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class TransferController extends Controller
{
    public function index(IndexRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $account = $user->account;

        $filters = $request->validated();
        $filters['search'] = $filters['search'] ?? null;
        $filters['type'] = $filters['type'] ?? null;

        $query = Transaction::query()
            ->whereHas('ledgerEntries', fn ($q) => $q->where('account_id', $account?->id))
            ->with(['initiator', 'ledgerEntries.account'])
            ->orderBy('created_at', 'desc');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'ilike', "%{$search}%")
                    ->orWhere('id', 'ilike', "%{$search}%");
            });
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        $transactions = $query->paginate(config('app.feature.pagination'))->withQueryString();

        return inertia('Transfers/Index', [
            'account' => $account ? AccountResource::make($account) : null,
            'list' => Inertia::defer(fn () => TransactionResource::collection($transactions)),
            'filters' => $filters,
        ]);
    }

    public function store(StoreRequest $request, TransferService $transferService)
    {
        /** @var User $sender */
        $sender = Auth::user();
        $senderAccount = $sender->account;
        $senderAccount->load('account.user');

        if (! $senderAccount) {
            throw ValidationException::withMessages([
                'amount' => 'You do not have an active wallet account.',
            ]);
        }

        $recipientInput = trim($request->validated('recipient'));

        /** @var User|null $recipient */
        $recipient = User::where('email', $recipientInput)
            ->orWhere('phone', $recipientInput)
            ->first();

        if (! $recipient) {
            throw ValidationException::withMessages([
                'recipient' => 'No user found with the provided email address or phone number.',
            ]);
        }

        if ($recipient->id === $sender->id) {
            throw ValidationException::withMessages([
                'recipient' => 'You cannot transfer funds to your own wallet.',
            ]);
        }

        $recipientAccount = $recipient->account;
        if (! $recipientAccount) {
            throw ValidationException::withMessages([
                'recipient' => 'The recipient wallet is not currently active.',
            ]);
        }

        $amount = toPaisa($request->validated('amount'));
        $idempotencyKey = $request->validated('idempotency_key');
        $otpCode = $request->validated('otp_code');
        $note = $request->validated('note');

        try {
            $transaction = $transferService->executeWithRiskCheck(
                fromAccount: $senderAccount,
                toAccount: $recipientAccount,
                amount: $amount,
                type: TransactionType::TRANSFER,
                idempotencyKey: $idempotencyKey,
                initiatedByUserId: $sender->id,
                metadata: array_filter(['note' => $note]),
                otpCode: $otpCode,
            );

            $formattedAmount = formatPaisa($amount);

            return back()->with('success', "Successfully transferred {$formattedAmount} BDT to {$recipient->name}. (Ref: {$transaction->reference})");
        } catch (RiskChallengeRequiredException $e) {
            return back()->with([
                'challenge_required' => true,
                'risk_score' => $e->assessment->score,
                'info' => $e->getMessage(),
            ]);
        } catch (TransactionHeldForReviewException $e) {
            return back()->with([
                'transaction_held' => true,
                'risk_score' => $e->assessment->score,
                'warning' => 'Your transfer has been temporarily held for security review.',
            ]);
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'amount' => $e->getMessage(),
            ]);
        }
    }

    public function resendOtp(ResendOtpRequest $request, OtpService $otpService)
    {
        /** @var User $user */
        $user = Auth::user();

        try {
            $otpService->generateAndSend($user, 'transfer');

            return back()->with('success', 'A new verification code has been dispatched.');
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'otp_code' => $e->getMessage(),
            ]);
        }
    }
}
