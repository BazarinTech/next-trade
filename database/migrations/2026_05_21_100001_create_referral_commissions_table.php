<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('deposit_id')->unique()->constrained('payment_deposits')->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->decimal('deposit_amount_usd', 20, 8);
            $table->decimal('rate', 5, 2);
            $table->decimal('commission_amount_usd', 20, 8);
            $table->enum('status', ['paid', 'failed'])->default('paid');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('referrer_id');
            $table->index(['referrer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_commissions');
    }
};
