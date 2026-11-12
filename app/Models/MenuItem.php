<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class MenuItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'menu_category_id', 
        'code', 
        'name',
        'description', 
        'image_path',
        'barcode',
        'selling_price', 
        'sale_mode',
        'stock_ingredient_id',
        'stock_quantity',
        'currency', 
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'selling_price' => 'decimal:2',
            'stock_quantity' => 'decimal:3',
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

    public function stockIngredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class, 'stock_ingredient_id');
    }

    public function isDirectStockSale(): bool
    {
        return $this->sale_mode === 'stock_item';
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path !== null
            ? Storage::disk('public')->url($this->image_path)
            : null;
    }
}
