<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_deposits', function (Blueprint $table) {
            $table->string('crypto_network')->nullable()->after('exchange_rate');
            $table->string('crypto_address')->nullable()->after('crypto_network');
            $table->string('txid', 255)->nullable()->unique()->after('crypto_address');
            $table->string('proof_path')->nullable()->after('txid');
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('proof_path');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->string('rejection_reason', 1000)->nullable()->after('reviewed_at');
            $table->text('admin_notes')->nullable()->after('rejection_reason');

            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payment_deposits', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn([
                'crypto_network', 'crypto_address', 'txid', 'proof_path',
                'reviewed_by', 'reviewed_at', 'rejection_reason', 'admin_notes',
            ]);
        });
    }
};
