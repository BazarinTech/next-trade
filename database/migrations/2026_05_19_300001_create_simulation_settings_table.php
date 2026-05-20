<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulation_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->enum('difficulty', ['easy', 'normal', 'hard', 'extreme'])->unique();
            $table->decimal('win_probability', 5, 2)->default(50.00);
            $table->decimal('volatility_multiplier', 8, 4)->default(1.0000);
            $table->decimal('trend_strength', 8, 4)->default(0.5000);
            $table->decimal('max_profit_multiplier', 8, 4)->default(0.8500);
            $table->decimal('min_profit_multiplier', 8, 4)->default(0.6000);
            $table->decimal('max_loss_multiplier', 8, 4)->default(1.0000);
            $table->integer('candle_speed_seconds')->default(3);
            $table->boolean('is_active')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulation_settings');
    }
};
