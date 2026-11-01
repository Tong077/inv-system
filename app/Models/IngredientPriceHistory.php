<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IngredientPriceHistory extends Model
{
    protected $fillable = [
        'ingredient_id', 'supplier_id', 'package_price',
        'package_quantity', 'package_unit_id',
        'unit_cost', 'effective_date', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'package_price' => 'decimal:2',
            'package_quantity' => 'decimal:3',
            'unit_cost' => 'decimal:6',
            'effective_date' => 'date',
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function packageUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'package_unit_id');
    }
}