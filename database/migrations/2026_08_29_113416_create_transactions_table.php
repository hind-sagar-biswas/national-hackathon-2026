<?php

use App\Enums\TransactionType;
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
        Schema::create('transactions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('reference');
            $table->enum('type', EnumUtil::toArray(TransactionType::class))->index();
            $table->string('idempotency_key')->unique();
            $table->foreignIdFor(User::class, 'initiated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
