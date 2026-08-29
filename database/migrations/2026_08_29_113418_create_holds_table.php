<?php

use App\Enums\HoldStatus;
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
        Schema::create('holds', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Account::class)->constrained()->cascadeOnDelete();
            $table->bigInteger('amount');
            $table->string('reason');
            $table->nullableMorphs('reference');    // the model/cause that is connected to the hold
            $table->enum('status', EnumUtil::toArray(HoldStatus::class))->default(HoldStatus::ACTIVE->value)->index();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holds');
    }
};
