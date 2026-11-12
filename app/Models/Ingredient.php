<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Ingredient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'ingredient_category_id',
        'base_unit_id',
        'description',
        'image_path',
        'minimum_stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'minimum_stock' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(IngredientPriceHistory::class);
    }

    public function latestOrderablePriceHistory(): HasOne
    {
        return $this->hasOne(IngredientPriceHistory::class)
            ->whereHas('supplier', fn ($query) => $query->where('is_active', true))
            ->whereHas('packageUnit', fn ($query) => $query->where('is_active', true))
            ->latestOfMany('effective_date');
    }

    public function latestPriceHistory(): HasOne
    {
        return $this->hasOne(IngredientPriceHistory::class)->latestOfMany('effective_date');
    }

    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    public function stockBalances(): HasMany
    {
        return $this->hasMany(StockBalance::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function goodsReceiptItems(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function ingredientCategory(): BelongsTo
    {
        return $this->belongsTo(IngredientCategory::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path !== null
            ? Storage::disk('public')->url($this->image_path)
            : null;
    }
}
