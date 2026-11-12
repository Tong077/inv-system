<?php

namespace App\Services;

use App\Models\Unit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class UnitService
{
    public function getAllUnits(?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        return Unit::query()
            ->when($search !== null && $search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('abbreviation', 'like', "%{$search}%")
                        ->orWhere('dimension', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['active', 'inactive'], true), function (Builder $query) use ($status): void {
                $query->where('is_active', $status === 'active');
            })
            ->orderBy('dimension')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();
    }

    public function createUnit(array $data): Unit
    {
        $this->validateUnitRules($data);

        return Unit::create($data);
    }

    public function updateUnit(Unit $unit, array $data): Unit
    {
        $this->validateUnitRules($data, $unit);

        $conversionFieldsChanged = $unit->dimension !== $data['dimension']
            || (float) $unit->conversion_factor_to_base !== (float) $data['conversion_factor_to_base']
            || $unit->is_base_unit !== (bool) $data['is_base_unit'];

        if ($conversionFieldsChanged && $this->isInUse($unit)) {
            throw ValidationException::withMessages([
                'dimension' => 'This unit is already used by inventory or recipe records. Its dimension and conversion settings cannot be changed.',
            ]);
        }

        $unit->update($data);

        return $unit->refresh();
    }

    public function deleteUnit(Unit $unit): bool
    {
        if ($this->isInUse($unit)) {
            return false;
        }

        return (bool) $unit->delete();
    }

    private function validateUnitRules(array $data, ?Unit $unit = null): void
    {
        if ((bool) $data['is_base_unit'] && (float) $data['conversion_factor_to_base'] !== 1.0) {
            throw ValidationException::withMessages([
                'conversion_factor_to_base' => 'A base unit must have a conversion factor of 1.',
            ]);
        }

        if (! (bool) $data['is_base_unit']) {
            return;
        }

        $baseUnitExists = Unit::query()
            ->where('dimension', $data['dimension'])
            ->where('is_base_unit', true)
            ->when($unit !== null, fn (Builder $query) => $query->where('id', '!=', $unit->getKey()))
            ->exists();

        if ($baseUnitExists) {
            throw ValidationException::withMessages([
                'is_base_unit' => 'A base unit already exists for this dimension.',
            ]);
        }
    }

    private function isInUse(Unit $unit): bool
    {
        return $unit->baseUnitIngredients()->withTrashed()->exists()
            || $unit->recipeIngredients()->exists()
            || $unit->recipeVersions()->exists()
            || $unit->purchaseOrderItems()->exists()
            || $unit->goodsReceiptItems()->exists()
            || $unit->priceHistories()->exists()
            || $unit->stockWasteItems()->exists()
            || $unit->stockTransferItems()->exists();
    }
}
