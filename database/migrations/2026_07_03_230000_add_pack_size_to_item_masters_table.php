<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_masters', function (Blueprint $table) {
            $table->integer('pack_size')->default(1)->after('opening_stock');
        });
    }

    public function down(): void
    {
        Schema::table('item_masters', function (Blueprint $table) {
            $table->dropColumn('pack_size');
        });
    }
};
