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
        if(!Schema::hasTable('recipe_versions')) {
            Schema::create('recipe_versions', function (Blueprint $table) {
            $table->id();
                   $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number')->default(1);
            $table->decimal('yield_quantity', 15, 3)->default(1);
            $table->foreignId('yield_unit_id')->nullable()->constrained('units');
            $table->boolean('is_current')->default(false);
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['menu_item_id', 'version_number']);
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_versions');
    }
};
