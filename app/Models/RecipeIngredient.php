<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeIngredient extends Model
{
    protected $fillable = [
        'recipe_version_id', 'ingredient_id', 'quantity',
        'unit_id', 'waste_percentage', 'unit_cost_snapshot',
        'line_cost_snapshot', 'sort_order', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'waste_percentage' => 'decimal:2',
            'unit_cost_snapshot' => 'decimal:6',
            'line_cost_snapshot' => 'decimal:2',
        ];
    }

    public function recipeVersion(): BelongsTo
    {
        return $this->belongsTo(RecipeVersion::class);
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