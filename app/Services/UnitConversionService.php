<?php

namespace App\Services;

use App\Models\Unit;
use Illuminate\Validation\ValidationException;

class UnitConversionService
{
    public function conversionFactor(Unit $from, Unit $to, string $errorField = 'unit_id'): float
    {
        if ($from->dimension !== $to->dimension) {
            throw ValidationException::withMessages([
                $errorField => 'Choose a unit with the same measurement dimension as the ingredient base unit.',
            ]);
        }

        $fromFactor = (float) $from->conversion_factor_to_base;
        $toFactor = (float) $to->conversion_factor_to_base;

        if ($fromFactor <= 0 || $toFactor <= 0) {
            throw ValidationException::withMessages([
                $errorField => 'The selected unit has an invalid conversion factor.',
            ]);
        }

        return $fromFactor / $toFactor;
    }

    public function convert(float $quantity, Unit $from, Unit $to, int $precision = 3, string $errorField = 'unit_id'): float
    {
        return round($quantity * $this->conversionFactor($from, $to, $errorField), $precision);
    }
}
