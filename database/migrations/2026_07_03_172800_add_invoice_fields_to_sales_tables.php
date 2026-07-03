<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing item-level fields to sale_items
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('discount_percent', 5, 2)->default(0)->after('rate');
            $table->decimal('other_discount', 10, 2)->default(0)->after('discount_amount');
            $table->decimal('free_qty', 10, 2)->default(0)->after('quantity');
        });

        // Add bill-level fields to sales
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('round_off', 10, 2)->default(0)->after('total_amount');
            $table->decimal('tcs_amount', 10, 2)->default(0)->after('round_off');
            $table->decimal('credit_adj', 10, 2)->default(0)->after('tcs_amount');
            $table->decimal('net_payable', 12, 2)->default(0)->after('credit_adj');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(['discount_percent', 'other_discount', 'free_qty']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['round_off', 'tcs_amount', 'credit_adj', 'net_payable']);
        });
    }
};
