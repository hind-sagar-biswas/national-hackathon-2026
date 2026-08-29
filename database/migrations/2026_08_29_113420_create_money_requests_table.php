<?php

use App\Enums\RequestStatus;
use App\Models\Account;
use App\Models\Hold;
use App\Models\Transaction;
use HindBiswas\ModelUtils\Utils\EnumUtil;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('money_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Account::class, 'requester_account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignIdFor(Account::class, 'payer_account_id')->constrained('accounts')->cascadeOnDelete();
            $table->bigInteger('amount');
            $table->enum('status', EnumUtil::toArray(RequestStatus::class))->default(RequestStatus::PENDING->value)->index();
            $table->foreignIdFor(Hold::class)->nullable()->constrained('holds')->nullOnDelete();
            $table->foreignIdFor(Transaction::class)->nullable()->constrained('transactions')->nullOnDelete();
            $table->timestamp('expires_at')->index();
            $table->timestamps();

            $table->index(['payer_account_id', 'status']);
            $table->index(['requester_account_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('money_requests');
    }
};
