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
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('no_of_package', 10, 2)->default(0)->after('item_id');
            $table->string('uom')->nullable()->after('no_of_package');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('rate');
            $table->decimal('cgst_rate', 5, 2)->default(20.00)->after('taxable_value');
            $table->decimal('cgst_amount', 12, 2)->default(0)->after('cgst_rate');
            $table->decimal('sgst_rate', 5, 2)->default(20.00)->after('cgst_amount');
            $table->decimal('sgst_amount', 12, 2)->default(0)->after('sgst_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn([
                'no_of_package',
                'uom',
                'discount_amount',
                'cgst_rate',
                'cgst_amount',
                'sgst_rate',
                'sgst_amount'
            ]);
        });
    }
};
