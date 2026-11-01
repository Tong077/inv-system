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
        if (!Schema::hasTable('units')) { {
                Schema::create('units', function (Blueprint $table) {
                    $table->id();
                    $table->string('name', 100);
                    $table->string('abbreviation', 20)->unique();
                    $table->string('dimension', 30); // mass, volume, count
                    $table->decimal('conversion_factor_to_base', 15, 6)->default(1);
                    $table->boolean('is_base_unit')->default(false);
                    $table->boolean('is_active')->default(true);
                    $table->timestamps();
                });
            }
        }


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
