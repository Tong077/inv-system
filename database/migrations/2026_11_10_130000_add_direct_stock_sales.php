<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('menu_items', 'sale_mode')) {
                $table->string('sale_mode', 20)->default('recipe')->after('selling_price');
            }
            if (! Schema::hasColumn('menu_items', 'stock_ingredient_id')) {
                $table->foreignId('stock_ingredient_id')->nullable()->after('sale_mode')->constrained('ingredients')->nullOnDelete();
            }
            if (! Schema::hasColumn('menu_items', 'stock_quantity')) {
                $table->decimal('stock_quantity', 15, 3)->nullable()->after('stock_ingredient_id');
            }
        });

        Schema::table('sale_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('sale_items', 'stock_ingredient_id')) {
                $table->foreignId('stock_ingredient_id')->nullable()->after('recipe_version_id')->constrained('ingredients')->nullOnDelete();
            }
            if (! Schema::hasColumn('sale_items', 'stock_quantity')) {
                $table->decimal('stock_quantity', 15, 3)->nullable()->after('stock_ingredient_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table): void {
            if (Schema::hasColumn('sale_items', 'stock_ingredient_id')) {
                $table->dropConstrainedForeignId('stock_ingredient_id');
            }
            if (Schema::hasColumn('sale_items', 'stock_quantity')) {
                $table->dropColumn('stock_quantity');
            }
        });

        Schema::table('menu_items', function (Blueprint $table): void {
            if (Schema::hasColumn('menu_items', 'stock_ingredient_id')) {
                $table->dropConstrainedForeignId('stock_ingredient_id');
            }
            if (Schema::hasColumn('menu_items', 'sale_mode')) {
                $table->dropColumn('sale_mode');
            }
            if (Schema::hasColumn('menu_items', 'stock_quantity')) {
                $table->dropColumn('stock_quantity');
            }
        });
    }
};
