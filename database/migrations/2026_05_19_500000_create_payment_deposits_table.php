<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->enum('wallet_type', ['demo', 'live'])->default('live');
            $table->enum('provider', ['palpluss', 'usdt_manual'])->default('palpluss');
            $table->enum('method', ['mpesa_stk', 'crypto_usdt_trc20'])->default('mpesa_stk');
            $table->decimal('local_amount', 15, 2);
            $table->string('local_currency', 3)->default('KES');
            $table->decimal('usd_amount', 15, 8);
            $table->decimal('exchange_rate', 15, 6);
            $table->string('phone', 20)->nullable();
            $table->string('account_reference', 100)->unique();
            $table->string('provider_transaction_id', 255)->nullable()->unique();
            $table->string('provider_request_id', 255)->nullable();
            $table->string('provider_checkout_id', 255)->nullable();
            $table->string('mpesa_receipt', 100)->nullable();
            $table->string('provider_status', 50)->nullable();
            $table->enum('status', ['pending', 'successful', 'failed', 'cancelled'])->default('pending');
            $table->string('result_code', 20)->nullable();
            $table->text('result_description')->nullable();
            $table->json('raw_initiation_response')->nullable();
            $table->json('raw_callback_response')->nullable();
            $table->json('raw_status_response')->nullable();
            $table->timestamp('credited_at')->nullable();
            $table->timestamp('manual_refresh_available_at')->nullable();
            $table->timestamp('last_status_checked_at')->nullable();
            $table->integer('status_check_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_deposits');
    }
};
