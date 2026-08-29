<?php

use App\Enums\AccountType;
use App\Enums\AccountOwner;
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
        //
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->enum('owner_type', EnumUtil::toArray(AccountOwner::class))->default(AccountOwner::USER->value)->index();
            $table->foreignIdFor(User::class)->nullable()->unique()->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('slug')->nullable()->unique();
            $table->enum('category', EnumUtil::toArray(AccountType::class))->default(AccountType::LIABILITY->value)->index();
            $table->bigInteger('cleared_balance')->default(0);      // Source of truth settled balance
            $table->bigInteger('available_balance')->default(0);    // Balance considering holds/pending debits
            $table->string('currency', 3)->default('BDT');
            $table->boolean('is_system')->default(false)->index();
            $table->timestamps();

            $table->index(['owner_type', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
