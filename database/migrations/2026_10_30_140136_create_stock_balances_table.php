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
        if (!Schema::hasTable('stock_balances')) {
            Schema::create('stock_balances', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('ingredient_id')->constrained();
                $table->foreignId('stock_location_id')->constrained('stock_locations');
                $table->decimal('quantity_on_hand', 15, 3)->default(0);
                $table->decimal('average_unit_cost', 15, 6)->default(0);
                $table->timestamp('last_movement_at')->nullable();
                $table->timestamps();
            });

            return;
        }

        $hasStockLocationForeignKey = collect(Schema::getForeignKeys('stock_balances'))
            ->contains(fn (array $foreignKey): bool => $foreignKey['columns'] === ['stock_location_id']
                && $foreignKey['foreign_table'] === 'stock_locations');

        if (!$hasStockLocationForeignKey) {
            Schema::table('stock_balances', function (Blueprint $table): void {
                $table->foreign('stock_location_id')
                    ->references('id')
                    ->on('stock_locations');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_balances');
    }
};
