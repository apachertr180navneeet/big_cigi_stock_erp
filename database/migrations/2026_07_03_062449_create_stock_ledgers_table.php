<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('item_masters')->onDelete('cascade');
            $table->string('transaction_type'); // 'purchase', 'sale'
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->decimal('quantity', 10, 2); // can be positive or negative
            $table->decimal('running_balance', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_ledgers');
    }
};
