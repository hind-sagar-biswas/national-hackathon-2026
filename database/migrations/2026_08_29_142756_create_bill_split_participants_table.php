<?php

use App\Enums\BillSplitParticipantStatus;
use App\Models\Account;
use App\Models\BillSplit;
use App\Models\Hold;
use App\Models\MoneyRequest;
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
        Schema::create('bill_split_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(BillSplit::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Account::class)->constrained()->cascadeOnDelete();
            $table->bigInteger('share_amount');
            $table->decimal('share_value', 10, 2)->default(0);
            $table->boolean('is_initiator')->default(false);
            $table->string('status')->default(BillSplitParticipantStatus::PENDING->value)->index();
            $table->foreignIdFor(Hold::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(MoneyRequest::class)->nullable()->constrained()->nullOnDelete();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->unique(['bill_split_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_split_participants');
    }
};
