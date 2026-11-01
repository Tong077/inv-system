<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockWasteItem extends Model
{
    protected $fillable = [
        'stock_waste_id', 'ingredient_id', 'quantity',
        'unit_id', 'unit_cost', 'total_cost', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_cost' => 'decimal:6',
            'total_cost' => 'decimal:2',
        ];
    }

    public function stockWaste(): BelongsTo
    {
        return $this->belongsTo(StockWaste::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}