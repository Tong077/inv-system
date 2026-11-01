<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $fillable = [
        'name', 'abbreviation', 'dimension',
        'conversion_factor_to_base', 'is_base_unit', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'conversion_factor_to_base' => 'decimal:6',
            'is_base_unit' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function baseUnitIngredients(): HasMany
    {
        return $this->hasMany(Ingredient::class, 'base_unit_id');
    }

    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(IngredientPriceHistory::class, 'package_unit_id');
    }
}