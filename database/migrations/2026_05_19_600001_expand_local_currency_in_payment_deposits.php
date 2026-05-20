<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_deposits', function (Blueprint $table) {
            $table->string('local_currency', 10)->default('KES')->change();
        });
    }

    public function down(): void
    {
        Schema::table('payment_deposits', function (Blueprint $table) {
            $table->string('local_currency', 3)->default('KES')->change();
        });
    }
};
