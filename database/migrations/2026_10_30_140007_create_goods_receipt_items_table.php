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
        if(!Schema::hasTable('goods_receipt_items')) {
            Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')
                ->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_order_item_id')->nullable()
                ->constrained()->nullOnDelete();
            $table->foreignId('ingredient_id')->constrained();
            $table->decimal('quantity_ordered', 15, 3)->default(0);
            $table->decimal('quantity_received', 15, 3);
            $table->foreignId('unit_id')->constrained('units');
            $table->decimal('unit_price', 12, 2);
            $table->date('expiry_date')->nullable();
            $table->string('batch_number', 100)->nullable();
            $table->timestamps();
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_items');
    }
};
