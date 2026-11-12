<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\Unit;
use Illuminate\Support\Carbon;

class RecipeCostService
{
    public function __construct(private readonly UnitConversionService $units) {}

    /** @return array{unit_cost_snapshot: float, line_cost_snapshot: float} */
    public function ingredientSnapshot(
        Ingredient $ingredient,
        Unit $inputUnit,
        float $quantity,
        float $wastePercentage = 0,
        ?Carbon $effectiveDate = null,
    ): array {
        $baseUnit = $ingredient->baseUnit;
        $conversionFactor = $this->units->conversionFactor($inputUnit, $baseUnit);
        $asOf = ($effectiveDate ?? Carbon::today())->toDateString();
        $baseUnitCost = (float) ($ingredient->priceHistories()
            ->whereDate('effective_date', '<=', $asOf)
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->value('unit_cost') ?? 0);
        $unitCost = round($baseUnitCost * $conversionFactor, 6);
        $lineCost = round($quantity * (1 + ($wastePercentage / 100)) * $unitCost, 2);

        return [
            'unit_cost_snapshot' => $unitCost,
            'line_cost_snapshot' => $lineCost,
        ];
    }
}
