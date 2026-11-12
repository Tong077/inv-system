<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\IngredientPriceHistory;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class IngredientPriceHistoryService
{
    public function __construct(private readonly UnitConversionService $units) {}

    public function getAll(?string $search = null, ?int $ingredientId = null): LengthAwarePaginator
    {
        return IngredientPriceHistory::query()
            ->with(['ingredient.baseUnit', 'supplier', 'packageUnit'])
            ->when($ingredientId !== null, fn (Builder $query) => $query->where('ingredient_id', $ingredientId))
            ->when($search !== null && $search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('notes', 'like', "%{$search}%")
                        ->orWhereHas('ingredient', function (Builder $ingredients) use ($search): void {
                            $ingredients->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        })
                        ->orWhereHas('supplier', function (Builder $suppliers) use ($search): void {
                            $suppliers->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
    }

    /** @return array{ingredients: Collection<int, Ingredient>, suppliers: Collection<int, Supplier>, units: Collection<int, Unit>} */
    public function getFormOptions(): array
    {
        return [
            'ingredients' => Ingredient::query()->with('baseUnit')->orderBy('name')->get(['id', 'code', 'name', 'base_unit_id']),
            'suppliers' => Supplier::query()->orderBy('name')->get(['id', 'code', 'name', 'is_active']),
            'units' => Unit::query()->orderBy('dimension')->orderBy('name')->get(['id', 'name', 'abbreviation', 'dimension', 'is_active']),
        ];
    }

    public function create(array $data): IngredientPriceHistory
    {
        return IngredientPriceHistory::create($this->withUnitCost($data));
    }

    public function update(IngredientPriceHistory $history, array $data): IngredientPriceHistory
    {
        $history->update($this->withUnitCost($data));

        return $history->refresh();
    }

    public function delete(IngredientPriceHistory $history): bool
    {
        return (bool) $history->delete();
    }

    /** @param array<string, mixed> $data
     *  @return array<string, mixed>
     */
    private function withUnitCost(array $data): array
    {
        $ingredient = Ingredient::query()->with('baseUnit')->findOrFail($data['ingredient_id']);
        $packageUnit = Unit::query()->findOrFail($data['package_unit_id']);
        $baseQuantity = $this->units->convert(
            (float) $data['package_quantity'],
            $packageUnit,
            $ingredient->baseUnit,
            6,
            'package_unit_id',
        );

        if ($baseQuantity <= 0) {
            throw ValidationException::withMessages([
                'package_quantity' => 'The package quantity must convert to a positive base-unit quantity.',
            ]);
        }

        return [...$data, 'unit_cost' => round((float) $data['package_price'] / $baseQuantity, 6)];
    }
}
