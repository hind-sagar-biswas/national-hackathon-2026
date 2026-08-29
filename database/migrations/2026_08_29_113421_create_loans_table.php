<?php

use App\Enums\LoanStatus;
use App\Models\Transaction;
use App\Models\User;
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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'lender_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'borrower_user_id')->constrained('users')->cascadeOnDelete();
            $table->bigInteger('principal_amount');
            $table->bigInteger('outstanding_amount');
            $table->enum('status', EnumUtil::toArray(LoanStatus::class))->default(LoanStatus::ACTIVE->value)->index();
            $table->foreignIdFor(Transaction::class, 'disbursement_txn_id')->constrained('transactions');
            $table->timestamp('due_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['lender_user_id', 'status']);
            $table->index(['borrower_user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
