<?php

use App\Enums\TransactionStatus;
use App\Models\Account;
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
        // Operation events are used to track detailed the status of an operation.
        Schema::create('operation_events', function (Blueprint $table) {
            $table->id();
            $table->string('operation_key')->index();
            $table->enum('status', EnumUtil::toArray(TransactionStatus::class))->index();
            $table->foreignIdFor(Account::class, 'from_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignIdFor(Account::class, 'to_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->bigInteger('amount')->default(0);
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->unique(['operation_key', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operation_events');
    }
};
