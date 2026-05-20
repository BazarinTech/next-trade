<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bot_plan_id')->constrained()->cascadeOnDelete();
            $table->enum('wallet_type', ['demo', 'live'])->default('demo');
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('daily_roi_percent', 8, 4);
            $table->decimal('total_earned', 15, 2)->default(0);
            $table->decimal('total_withdrawn', 15, 2)->default(0);
            $table->timestamp('started_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('last_earning_at')->nullable();
            $table->enum('status', ['active', 'completed', 'cancelled', 'paused'])->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_investments');
    }
};
