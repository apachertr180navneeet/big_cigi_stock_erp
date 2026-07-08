<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['tcs_amount', 'credit_adj', 'round_off']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('round_off', 10, 2)->default(0)->after('net_payable');
            $table->decimal('tcs_amount', 10, 2)->default(0)->after('round_off');
            $table->decimal('credit_adj', 10, 2)->default(0)->after('tcs_amount');
        });
    }
};
