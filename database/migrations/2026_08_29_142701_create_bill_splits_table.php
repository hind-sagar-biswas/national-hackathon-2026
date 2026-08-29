<?php

use App\Enums\BillSplitMode;
use App\Enums\BillSplitStatus;
use App\Models\Account;
use App\Models\User;
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
        Schema::create('bill_splits', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'initiator_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(Account::class, 'initiator_account_id')->constrained('accounts')->cascadeOnDelete();
            $table->string('title');
            $table->bigInteger('total_amount');
            $table->string('mode')->default(BillSplitMode::EQUAL->value);
            $table->string('status')->default(BillSplitStatus::PENDING->value)->index();
            $table->foreignIdFor(Account::class, 'merchant_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('merchant_name')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_splits');
    }
};
