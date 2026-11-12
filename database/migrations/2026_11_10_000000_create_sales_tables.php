<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table): void {
            $table->id();
            $table->string('receipt_number', 40)->unique();
            $table->foreignId('stock_location_id')->constrained('stock_locations');
            $table->string('status', 20)->default('posted');
            $table->dateTime('sold_at');
            $table->string('currency', 10);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('total', 12, 2);
            $table->text('notes')->nullable();
            $table->foreignId('sold_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'sold_at']);
        });

        Schema::create('sale_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('recipe_version_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_code', 50);
            $table->string('item_name', 200);
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();

            $table->index(['sale_id', 'menu_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
