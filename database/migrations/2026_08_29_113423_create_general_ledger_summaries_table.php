<?php

use App\Enums\AccountType;
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
        Schema::create('general_ledger_summaries', function (Blueprint $table) {
            $table->id();
            $table->enum('category', EnumUtil::toArray(AccountType::class))->index();
            $table->bigInteger('total');
            $table->timestamp('as_of')->index();
            $table->timestamps();

            $table->index(['category', 'as_of']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_ledger_summaries');
    }
};
