<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('stock_waste_items')) {
            Schema::create('stock_waste_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('stock_waste_id')
                    ->constrained()->cascadeOnDelete();
                $table->foreignId('ingredient_id')->constrained();
                $table->decimal('quantity', 15, 3);
                $table->foreignId('unit_id')->constrained('units');
                $table->decimal('unit_cost', 15, 6)->default(0);
                $table->decimal('total_cost', 15, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_waste_items');
    }
};
