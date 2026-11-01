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
        if(!Schema::hasTable('ingredient_price_histories')) {
            Schema::create('ingredient_price_histories', function (Blueprint $table) {
            $table->id();
             $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('package_price', 12, 2);
            $table->decimal('package_quantity', 15, 3);
            $table->foreignId('package_unit_id')->constrained('units');
            $table->decimal('unit_cost', 15, 6);
            $table->date('effective_date');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['ingredient_id', 'effective_date']);
            
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredient_price_histories');
    }
};
