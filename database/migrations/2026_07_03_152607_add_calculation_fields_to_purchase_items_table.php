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
            $table->decimal('packets', 10, 2)->default(0)->after('rate');
            $table->decimal('mrp', 10, 2)->default(0)->after('packets');
            $table->decimal('taxable_value', 12, 2)->default(0)->after('mrp');
            $table->decimal('tax_amount', 12, 2)->default(0)->after('taxable_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn(['packets', 'mrp', 'taxable_value', 'tax_amount']);
        });
    }
};
