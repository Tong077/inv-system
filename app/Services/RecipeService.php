<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\RecipeVersion;
use App\Models\Unit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RecipeService
{
    public function getAllRecipeVersions(?string $search = null, ?string $current = null): LengthAwarePaginator
    {
        return RecipeVersion::query()
            ->with(['menuItem', 'yieldUnit'])
            ->withCount(['ingredients', 'instructions', 'otherCosts'])
            ->when($search !== null && $search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('version_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('menuItem', function (Builder $menuItems) use ($search): void {
                            $menuItems->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        });
                });
            })
            ->when(in_array($current, ['current', 'previous'], true), function (Builder $query) use ($current): void {
                $query->where('is_current', $current === 'current');
            })
            ->orderByDesc('is_current')
            ->orderByDesc('effective_from')
            ->orderByDesc('version_number')
            ->paginate(15)
            ->withQueryString();
    }

    /** @return array{menuItems: Collection<int, MenuItem>, units: Collection<int, Unit>} */
    public function getFormOptions(): array
    {
        return [
            'menuItems' => MenuItem::query()->orderBy('name')->get(['id', 'code', 'name', 'is_active']),
            'units' => Unit::query()->orderBy('dimension')->orderBy('name')->get(['id', 'name', 'abbreviation', 'dimension', 'is_active']),
        ];
    }

    public function createRecipeVersion(array $data): RecipeVersion
    {
        return DB::transaction(function () use ($data): RecipeVersion {
            $menuItem = MenuItem::query()->whereKey($data['menu_item_id'])->lockForUpdate()->firstOrFail();
            $data['version_number'] = ((int) $menuItem->recipeVersions()->max('version_number')) + 1;

            if ($data['is_current']) {
                RecipeVersion::query()->where('menu_item_id', $data['menu_item_id'])->update(['is_current' => false]);
            }

            return RecipeVersion::create($data);
        });
    }

    public function updateRecipeVersion(RecipeVersion $recipeVersion, array $data): RecipeVersion
    {
        return DB::transaction(function () use ($recipeVersion, $data): RecipeVersion {
            $menuItem = MenuItem::query()->whereKey($data['menu_item_id'])->lockForUpdate()->firstOrFail();

            if ((int) $data['menu_item_id'] !== (int) $recipeVersion->menu_item_id) {
                $data['version_number'] = ((int) $menuItem->recipeVersions()->max('version_number')) + 1;
            } else {
                unset($data['version_number']);
            }

            if ($data['is_current']) {
                RecipeVersion::query()
                    ->where('menu_item_id', $data['menu_item_id'])
                    ->where('id', '!=', $recipeVersion->getKey())
                    ->update(['is_current' => false]);
            }

            $recipeVersion->update($data);

            return $recipeVersion->refresh();
        });
    }

    public function deleteRecipeVersion(RecipeVersion $recipeVersion): bool
    {
        if ($recipeVersion->ingredients()->exists()
            || $recipeVersion->instructions()->exists()
            || $recipeVersion->otherCosts()->exists()) {
            return false;
        }

        return (bool) $recipeVersion->delete();
    }
}
