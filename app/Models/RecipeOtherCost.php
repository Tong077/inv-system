<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeOtherCost extends Model
{
    protected $fillable = [
        'recipe_version_id', 'name', 'amount',
        'cost_type', 'notes', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function recipeVersion(): BelongsTo
    {
        return $this->belongsTo(RecipeVersion::class);
    }
}