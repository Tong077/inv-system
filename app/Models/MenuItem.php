<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'menu_category_id', 'code', 'name',
        'description', 'selling_price', 'currency', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'selling_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }

    public function recipeVersions(): HasMany
    {
        return $this->hasMany(RecipeVersion::class);
    }

    public function currentRecipe()
    {
        return $this->hasOne(RecipeVersion::class)->where('is_current', true);
    }
}