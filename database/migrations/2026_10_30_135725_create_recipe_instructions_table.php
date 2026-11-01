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
        if(!Schema::hasTable('recipe_instructions')) {
            Schema::create('recipe_instructions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_version_id')
                ->constrained()->cascadeOnDelete();
            $table->unsignedInteger('step_number');
            $table->text('instruction');
            $table->unsignedInteger('preparation_time_minutes')->nullable();
            $table->timestamps();

            $table->unique(['recipe_version_id', 'step_number']);
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_instructions');
    }
};
