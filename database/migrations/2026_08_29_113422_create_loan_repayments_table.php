<?php

use App\Models\Loan;
use App\Models\Transaction;
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
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Loan::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Transaction::class)->constrained();
            $table->bigInteger('amount');
            $table->timestamps();

            $table->index(['loan_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_repayments');
    }
};
