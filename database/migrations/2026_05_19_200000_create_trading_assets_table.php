<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trading_assets', function (Blueprint $table) {
            $table->id();
            $table->string('symbol', 20)->unique();
            $table->string('name', 100);
            $table->enum('type', ['forex', 'crypto', 'synthetic', 'stock']);
            $table->decimal('base_price', 20, 8);
            $table->decimal('current_price', 20, 8);
            $table->decimal('volatility', 10, 6)->default(0.002000);
            $table->enum('trend_bias', ['bullish', 'bearish', 'neutral'])->default('neutral');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trading_assets');
    }
};
