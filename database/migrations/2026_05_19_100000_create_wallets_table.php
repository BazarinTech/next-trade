<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['demo', 'live'])->default('demo');
            $table->string('currency', 10)->default('USD');
            $table->decimal('balance', 20, 8)->default(0);
            $table->decimal('locked_balance', 20, 8)->default(0);
            $table->decimal('total_deposited', 20, 8)->default(0);
            $table->decimal('total_withdrawn', 20, 8)->default(0);
            $table->decimal('total_profit', 20, 8)->default(0);
            $table->decimal('total_loss', 20, 8)->default(0);
            $table->enum('status', ['active', 'frozen'])->default('active');
            $table->timestamps();

            $table->unique(['user_id', 'type']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
