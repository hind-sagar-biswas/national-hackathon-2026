<?php

use App\Enums\DepositProvider;
use App\Enums\DepositStatus;
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
        Schema::create('deposit_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->enum('provider', EnumUtil::toArray(DepositProvider::class))->index();
            $table->string('provider_ref')->index();
            $table->bigInteger('amount');
            $table->enum('status', EnumUtil::toArray(DepositStatus::class))->default(DepositStatus::PENDING->value)->index();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposit_requests');
    }
};
