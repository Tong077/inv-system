<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\RecipeIngredient;
use App\Models\RecipeVersion;
use App\Models\Unit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class RecipeIngredientService
{
    public function __construct(private readonly RecipeCostService $costs) {}

    public function getAllRecipeIngredients(?string $search = null, ?int $recipeVersionId = null): LengthAwarePaginator
    {
        return RecipeIngredient::query()
            ->with(['recipeVersion.menuItem', 'ingredient.baseUnit', 'unit'])
            ->when($recipeVersionId !== null, fn (Builder $query) => $query->where('recipe_version_id', $recipeVersionId))
            ->when($search !== null && $search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('notes', 'like', "%{$search}%")
                        ->orWhereHas('ingredient', function (Builder $ingredients) use ($search): void {
                            $ingredients->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        })
                        ->orWhereHas('recipeVersion.menuItem', function (Builder $menuItems) use ($search): void {
                            $menuItems->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('recipe_version_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();
    }

    /** @return array{recipeVersions: Collection<int, RecipeVersion>, ingredients: Collection<int, Ingredient>, units: Collection<int, Unit>} */
    public function getFormOptions(): array
    {
        return [
            'recipeVersions' => RecipeVersion::query()->with('menuItem')->orderByDesc('is_current')->orderByDesc('version_number')->get(),
            'ingredients' => Ingredient::query()->with('baseUnit')->orderBy('name')->get(),
            'units' => Unit::query()->orderBy('dimension')->orderBy('name')->get(),
        ];
    }

    public function createRecipeIngredient(array $data): RecipeIngredient
    {
        $data = $this->withCostSnapshot($data);
        $currentSortOrder = RecipeIngredient::query()
            ->where('recipe_version_id', $data['recipe_version_id'])
            ->max('sort_order');
        $data['sort_order'] = ($currentSortOrder ?? 0) + 1;

        return RecipeIngredient::create($data);
    }

    public function updateRecipeIngredient(RecipeIngredient $recipeIngredient, array $data): RecipeIngredient
    {
        $data['sort_order'] = $recipeIngredient->sort_order;
        $recipeIngredient->update($this->withCostSnapshot($data));

        return $recipeIngredient->refresh();
    }

    public function deleteRecipeIngredient(RecipeIngredient $recipeIngredient): bool
    {
        return (bool) $recipeIngredient->delete();
    }

    /** @param array<string, mixed> $data
     *  @return array<string, mixed>
     */
    private function withCostSnapshot(array $data): array
    {
        $ingredient = Ingredient::query()->with('baseUnit')->findOrFail($data['ingredient_id']);
        $unit = Unit::query()->findOrFail($data['unit_id']);
        $recipeVersion = RecipeVersion::query()->findOrFail($data['recipe_version_id']);
        $snapshot = $this->costs->ingredientSnapshot(
            $ingredient,
            $unit,
            (float) $data['quantity'],
            (float) ($data['waste_percentage'] ?? 0),
            $recipeVersion->effective_from,
        );

        return [...$data, 'waste_percentage' => $data['waste_percentage'] ?? 0, ...$snapshot];
    }
}
