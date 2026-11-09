<?php

namespace App\Services;

use App\Models\IngredientCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class IngredientCategoryService
{
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

    public function getIngredientCategoryById(int $id): ?IngredientCategory
    {
        return IngredientCategory::find($id);
    }

    public function createIngredientCategory(array $data): IngredientCategory
    {
        return IngredientCategory::create($data);
    }

    public function updateIngredientCategory(int $id, array $data): ?IngredientCategory
    {
        $category = IngredientCategory::find($id);

        if (! $category) {
            return null;
        }

        $category->update($data);

        return $category->refresh();
    }

    public function deleteIngredientCategory(int $id): bool
    {
        $category = IngredientCategory::find($id);

        if (! $category
            || $category->ingredients()->exists()
            || $category->children()->exists()) {
            return false;
        }

        return (bool) $category->delete();
    }
}
