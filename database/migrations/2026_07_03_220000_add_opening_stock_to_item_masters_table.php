<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_masters', function (Blueprint $table) {
            $table->decimal('opening_stock', 10, 2)->default(0)->after('current_stock');
        });
    }

    public function down(): void
    {
        Schema::table('item_masters', function (Blueprint $table) {
            $table->dropColumn('opening_stock');
        });
    }
};
