<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('wallet_id');
            $table->enum('method', ['mpesa', 'usdt_trc20']);
            $table->enum('status', [
                'pending', 'approved', 'processing',
                'successful', 'failed', 'rejected', 'cancelled',
            ])->default('pending');

            $table->decimal('usd_amount', 15, 8);
            $table->decimal('local_amount', 15, 8)->nullable();
            $table->string('local_currency', 10)->nullable();
            $table->decimal('exchange_rate', 15, 6)->nullable();

            $table->string('phone')->nullable();
            $table->string('crypto_network')->nullable();
            $table->string('crypto_address')->nullable();
            $table->string('txid')->nullable();

            $table->string('account_reference')->unique();
            $table->string('provider_reference')->nullable();
            $table->string('provider_status')->nullable();

            $table->decimal('fee_amount', 15, 8)->default(0);
            $table->decimal('net_amount', 15, 8);

            $table->string('rejection_reason', 1000)->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamp('requested_at');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('wallet_id')->references('id')->on('wallets')->cascadeOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index(['method', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
