<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trading_asset_id')->constrained()->cascadeOnDelete();
            $table->enum('wallet_type', ['demo', 'live']);
            $table->enum('direction', ['buy', 'sell']);
            $table->decimal('stake_amount', 20, 8);
            $table->decimal('entry_price', 20, 8);
            $table->decimal('exit_price', 20, 8)->nullable();
            $table->decimal('displacement', 20, 8)->nullable();
            $table->decimal('profit_loss', 20, 8)->nullable();
            $table->decimal('payout', 20, 8)->nullable();
            $table->integer('expiry_seconds');
            $table->timestamp('opened_at');
            $table->timestamp('expires_at');
            $table->timestamp('closed_at')->nullable();
            $table->enum('status', ['open', 'won', 'lost', 'draw', 'cancelled'])->default('open');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['expires_at', 'status']);
            $table->index('wallet_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
