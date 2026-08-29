<?php

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
        Schema::create('reconciliation_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('last_ledger_entry_id')->default(0)->index();
            $table->bigInteger('total_debits')->default(0);
            $table->bigInteger('total_credits')->default(0);
            $table->boolean('is_balanced')->default(true);
            $table->jsonb('account_snapshots')->nullable();
            $table->timestamp('as_of')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reconciliation_checkpoints');
    }
};
