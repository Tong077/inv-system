<?php

namespace App\Services;

use App\Models\IngredientCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class IngredientCategoryService
{
    /**
     * @return Collection<int, IngredientCategory>
     */
    public function getParentOptions(?IngredientCategory $category = null): Collection
    {
        $query = IngredientCategory::query()->orderBy('name');

        if ($category !== null) {
            $excludedIds = [$category->id, ...$this->descendantIds($category)];
            $query->whereNotIn('id', $excludedIds);
        }

        return $query->get(['id', 'name']);
    }

    public function getAllIngredientCategories(?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        return IngredientCategory::query()
            ->with('parent')
            ->withCount('ingredients')
            ->when($search !== null && $search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['active', 'inactive'], true), function (Builder $query) use ($status): void {
                $query->where('is_active', $status === 'active');
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();
    }

    public function createIngredientCategory(array $data): IngredientCategory
    {
        return IngredientCategory::create($data);
    }

    public function updateIngredientCategory(IngredientCategory $category, array $data): IngredientCategory
    {
        if (isset($data['parent_id']) && in_array((int) $data['parent_id'], [$category->id, ...$this->descendantIds($category)], true)) {
            throw ValidationException::withMessages([
                'parent_id' => 'A category cannot be placed under itself or one of its child categories.',
            ]);
        }

        $category->update($data);

        return $category->refresh();
    }

    public function deleteIngredientCategory(IngredientCategory $category): bool
    {
        if ($category->ingredients()->exists() || $category->children()->exists()) {
            return false;
        }

        return (bool) $category->delete();
    }

    /** @return list<int> */
    private function descendantIds(IngredientCategory $category): array
    {
        $ids = [];
        $frontier = [$category->id];
        $visited = [$category->id => true];

        while ($frontier !== []) {
            $children = IngredientCategory::query()
                ->whereIn('parent_id', $frontier)
                ->pluck('id')
                ->all();

            $frontier = [];

            foreach ($children as $childId) {
                $childId = (int) $childId;

                if (isset($visited[$childId])) {
                    continue;
                }

                $visited[$childId] = true;
                $ids[] = $childId;
                $frontier[] = $childId;
            }
        }

        return $ids;
    }
}
