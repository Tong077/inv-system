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
        if(!Schema::hasTable('recipe_other_costs')) {
            Schema::create('recipe_other_costs', function (Blueprint $table) {
            $table->id();
             $table->foreignId('recipe_version_id')
                ->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->decimal('amount', 12, 2);
            $table->enum('cost_type', ['fixed', 'percentage'])->default('fixed');
            $table->text('notes')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_other_costs');
    }
};
