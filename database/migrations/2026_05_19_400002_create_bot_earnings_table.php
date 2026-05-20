<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bot_investment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bot_plan_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 8);
            $table->decimal('roi_percent', 8, 4);
            $table->date('earning_date');
            $table->enum('status', ['pending', 'credited', 'failed'])->default('pending');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['bot_investment_id', 'earning_date']);
            $table->index(['user_id', 'earning_date']);
            $table->index(['status', 'earning_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_earnings');
    }
};
