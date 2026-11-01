<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecipeVersion extends Model
{
    protected $fillable = [
        'menu_item_id', 'version_number', 'yield_quantity',
        'yield_unit_id', 'is_current', 'effective_from',
        'effective_until', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'yield_quantity' => 'decimal:3',
            'is_current' => 'boolean',
            'effective_from' => 'date',
            'effective_until' => 'date',
        ];
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function yieldUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'yield_unit_id');
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    public function instructions(): HasMany
    {
        return $this->hasMany(RecipeInstruction::class)->orderBy('step_number');
    }

    public function otherCosts(): HasMany
    {
        return $this->hasMany(RecipeOtherCost::class);
    }
}