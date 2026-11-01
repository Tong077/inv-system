<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockWaste extends Model
{
    protected $fillable = [
        'waste_number', 'stock_location_id', 'waste_date',
        'reason', 'status', 'created_by', 'approved_by',
    ];

    protected function casts(): array
    {
        return ['waste_date' => 'date'];
    }

    public function stockLocation(): BelongsTo
    {
        return $this->belongsTo(StockLocation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockWasteItem::class);
    }
}