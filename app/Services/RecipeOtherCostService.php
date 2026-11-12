<?php

namespace App\Services;

use App\Models\RecipeOtherCost;
use App\Models\RecipeVersion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class RecipeOtherCostService
{
    public function getAll(?string $search = null, ?int $recipeVersionId = null): LengthAwarePaginator
    {
        return RecipeOtherCost::query()
            ->with('recipeVersion.menuItem')
            ->when($recipeVersionId !== null, fn (Builder $query) => $query->where('recipe_version_id', $recipeVersionId))
            ->when($search !== null && $search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhere('cost_type', 'like', "%{$search}%")
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

    /** @return Collection<int, RecipeVersion> */
    public function getRecipeVersions(): Collection
    {
        return RecipeVersion::query()
            ->with('menuItem')
            ->orderByDesc('is_current')
            ->orderByDesc('version_number')
            ->get();
    }

    public function create(array $data): RecipeOtherCost
    {
        $data['sort_order'] = ((int) RecipeOtherCost::query()
            ->where('recipe_version_id', $data['recipe_version_id'])
            ->max('sort_order')) + 1;

        return RecipeOtherCost::create($data);
    }

    public function update(RecipeOtherCost $cost, array $data): RecipeOtherCost
    {
        $data['sort_order'] = $cost->sort_order;
        $cost->update($data);

        return $cost->refresh();
    }

    public function delete(RecipeOtherCost $cost): bool
    {
        return (bool) $cost->delete();
    }
}
