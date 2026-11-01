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
        if(!Schema::hasTable('stock_wastes')) {
            Schema::create('stock_wastes', function (Blueprint $table) {
            $table->id();
            $table->string('waste_number', 50)->unique();
            $table->foreignId('stock_location_id')->constrained();
            $table->date('waste_date');
            $table->string('reason', 200);
            $table->string('status', 30)->default('draft');
            $table->foreignId('created_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_wastes');
    }
};
