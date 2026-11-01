<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'ingredient_id', 'stock_location_id', 'movement_type',
        'reference_type', 'reference_id', 'quantity_in',
        'quantity_out', 'unit_cost', 'balance_after',
        'movement_date', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity_in' => 'decimal:3',
            'quantity_out' => 'decimal:3',
            'unit_cost' => 'decimal:6',
            'balance_after' => 'decimal:3',
            'movement_date' => 'datetime',
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function stockLocation(): BelongsTo
    {
        return $this->belongsTo(StockLocation::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}