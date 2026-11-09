<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IngredientCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Get ingredients belonging to this category
    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class);
    }

    // Get parent category
    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            IngredientCategory::class,
            'parent_id'
        );
    }

    // Get child categories
    public function children(): HasMany
    {
        return $this->hasMany(
            IngredientCategory::class,
            'parent_id'
        );
    }
}