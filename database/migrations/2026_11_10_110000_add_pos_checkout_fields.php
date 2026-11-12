<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('menu_items', 'barcode')) {
            Schema::table('menu_items', function (Blueprint $table): void {
                $table->string('barcode', 100)->nullable()->unique();
            });
        }

        Schema::table('sales', function (Blueprint $table): void {
            if (! Schema::hasColumn('sales', 'payment_method')) {
                $table->string('payment_method', 20)->default('cash')->after('currency');
            }
            if (! Schema::hasColumn('sales', 'amount_tendered')) {
                $table->decimal('amount_tendered', 12, 2)->default(0)->after('total');
            }
            if (! Schema::hasColumn('sales', 'change_due')) {
                $table->decimal('change_due', 12, 2)->default(0)->after('amount_tendered');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            $columns = array_filter(['payment_method', 'amount_tendered', 'change_due'], fn (string $column): bool => Schema::hasColumn('sales', $column));
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        if (Schema::hasColumn('menu_items', 'barcode')) {
            Schema::table('menu_items', function (Blueprint $table): void {
                $table->dropUnique(['barcode']);
                $table->dropColumn('barcode');
            });
        }
    }
};
