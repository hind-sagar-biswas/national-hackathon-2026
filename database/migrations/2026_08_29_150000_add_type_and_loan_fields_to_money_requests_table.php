<?php

use App\Enums\MoneyRequestType;
use App\Models\Loan;
use App\Models\MoneyRequest;
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
        Schema::table('money_requests', function (Blueprint $table) {
            $table->enum('type', EnumUtil::toArray(MoneyRequestType::class))->default(MoneyRequestType::STANDARD->value)->after('amount')->index();
            $table->timestamp('due_at')->nullable()->after('expires_at');
            $table->text('note')->nullable()->after('due_at');
            $table->foreignIdFor(Loan::class)->nullable()->after('transaction_id')->constrained('loans')->nullOnDelete();
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->foreignIdFor(MoneyRequest::class)->nullable()->after('disbursement_txn_id')->constrained('money_requests')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeignIdFor(MoneyRequest::class);
            $table->dropColumn('money_request_id');
        });

        Schema::table('money_requests', function (Blueprint $table) {
            $table->dropForeignIdFor(Loan::class);
            $table->dropColumn(['type', 'due_at', 'note', 'loan_id']);
        });
    }
};
