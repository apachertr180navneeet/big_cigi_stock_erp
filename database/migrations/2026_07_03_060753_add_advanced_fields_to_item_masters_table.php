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
        Schema::table('item_masters', function (Blueprint $table) {
            $table->string('purchase_uom')->nullable()->after('brand_code');
            $table->string('sales_uom')->nullable()->after('purchase_uom');
            $table->decimal('conversion_factor', 10, 4)->default(1)->after('sales_uom');
            $table->decimal('purchase_rate', 10, 2)->default(0)->after('mrp');
            $table->decimal('sales_rate', 10, 2)->default(0)->after('purchase_rate');
            $table->decimal('cgst_percentage', 5, 2)->default(0)->after('sales_rate');
            $table->decimal('sgst_percentage', 5, 2)->default(0)->after('cgst_percentage');
            $table->decimal('cess_percentage', 5, 2)->default(0)->after('sgst_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_masters', function (Blueprint $table) {
            $table->dropColumn([
                'purchase_uom',
                'sales_uom',
                'conversion_factor',
                'purchase_rate',
                'sales_rate',
                'cgst_percentage',
                'sgst_percentage',
                'cess_percentage'
            ]);
        });
    }
};
