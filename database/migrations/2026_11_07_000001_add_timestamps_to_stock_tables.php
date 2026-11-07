<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['stock_locations', 'stock_adjustments', 'stock_adjustment_items'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            $hasCreatedAt = Schema::hasColumn($tableName, 'created_at');
            $hasUpdatedAt = Schema::hasColumn($tableName, 'updated_at');

            if (! $hasCreatedAt || ! $hasUpdatedAt) {
                Schema::table($tableName, function (Blueprint $table) use ($hasCreatedAt, $hasUpdatedAt): void {
                    if (! $hasCreatedAt) {
                        $table->timestamp('created_at')->nullable();
                    }
                    if (! $hasUpdatedAt) {
                        $table->timestamp('updated_at')->nullable();
                    }
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['stock_locations', 'stock_adjustments', 'stock_adjustment_items'] as $tableName) {
            if (Schema::hasTable($tableName)
                && Schema::hasColumn($tableName, 'created_at')
                && Schema::hasColumn($tableName, 'updated_at')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->dropTimestamps();
                });
            }
        }
    }
};
