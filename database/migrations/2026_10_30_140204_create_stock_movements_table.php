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
        if (!Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('ingredient_id')->constrained();
                $table->foreignId('stock_location_id')->constrained('stock_locations');
                $table->string('movement_type', 40);
                $table->string('reference_type', 50)->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->decimal('quantity_in', 15, 3)->default(0);
                $table->decimal('quantity_out', 15, 3)->default(0);
                $table->decimal('unit_cost', 15, 6)->default(0);
                $table->decimal('balance_after', 15, 3);
                $table->dateTime('movement_date');
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()
                    ->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(
                    ['ingredient_id', 'stock_location_id', 'movement_date'],
                    'stock_movements_ing_loc_date_idx',
                );
                $table->index(['reference_type', 'reference_id']);
            });

            return;
        }

        $indexes = collect(Schema::getIndexes('stock_movements'));

        if (!$indexes->contains(fn (array $index): bool => $index['columns'] === [
            'ingredient_id',
            'stock_location_id',
            'movement_date',
        ])) {
            Schema::table('stock_movements', function (Blueprint $table): void {
                $table->index(
                    ['ingredient_id', 'stock_location_id', 'movement_date'],
                    'stock_movements_ing_loc_date_idx',
                );
            });
        }

        if (!$indexes->contains(fn (array $index): bool => $index['columns'] === [
            'reference_type',
            'reference_id',
        ])) {
            Schema::table('stock_movements', function (Blueprint $table): void {
                $table->index(['reference_type', 'reference_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
