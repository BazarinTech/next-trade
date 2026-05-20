<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('daily_roi_percent', 8, 4);
            $table->decimal('min_investment', 15, 2);
            $table->decimal('max_investment', 15, 2)->nullable();
            $table->integer('duration_days')->nullable();
            $table->enum('risk_level', ['low', 'medium', 'high', 'extreme'])->default('medium');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_plans');
    }
};
