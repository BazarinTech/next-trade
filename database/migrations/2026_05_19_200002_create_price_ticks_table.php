<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_ticks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trading_asset_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 20, 8);
            $table->decimal('previous_price', 20, 8)->nullable();
            $table->enum('direction', ['up', 'down', 'flat']);
            $table->timestamp('tick_time');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['trading_asset_id', 'tick_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_ticks');
    }
};
