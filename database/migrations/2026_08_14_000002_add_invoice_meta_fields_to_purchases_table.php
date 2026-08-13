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
        Schema::table('purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('purchases', 'eway_bill_no')) {
                $table->string('eway_bill_no')->nullable()->after('bill_date');
            }
            if (!Schema::hasColumn('purchases', 'supplier_invoice_no')) {
                $table->string('supplier_invoice_no')->nullable()->after('eway_bill_no');
            }
            if (!Schema::hasColumn('purchases', 'supplier_invoice_date')) {
                $table->date('supplier_invoice_date')->nullable()->after('supplier_invoice_no');
            }
            if (!Schema::hasColumn('purchases', 'other_references')) {
                $table->string('other_references')->nullable()->after('supplier_invoice_date');
            }
            if (!Schema::hasColumn('purchases', 'discount_allowed')) {
                $table->decimal('discount_allowed', 12, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('purchases', 'cgst_total')) {
                $table->decimal('cgst_total', 12, 2)->default(0)->after('discount_allowed');
            }
            if (!Schema::hasColumn('purchases', 'sgst_total')) {
                $table->decimal('sgst_total', 12, 2)->default(0)->after('cgst_total');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'eway_bill_no',
                'supplier_invoice_no',
                'supplier_invoice_date',
                'other_references',
                'discount_allowed',
                'cgst_total',
                'sgst_total',
            ]);
        });
    }
};
