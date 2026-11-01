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
        if(!Schema::hasTable('stock_counts')) {
            Schema::create('stock_counts', function (Blueprint $table) {
            $table->id();
             $table->string('count_number', 50)->unique();
            $table->foreignId('stock_location_id')->constrained();
            $table->date('count_date');
            $table->string('status', 30)->default('draft');
            $table->foreignId('counted_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_counts');
    }
};
