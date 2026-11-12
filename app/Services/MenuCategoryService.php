<?php

namespace App\Services;

use App\Models\MenuCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class MenuCategoryService
{
    public function getAllMenuCategories(?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        return MenuCategory::query()
            ->withCount('menuItems')
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

    public function createMenuCategory(array $data): MenuCategory
    {
        return MenuCategory::create($data);
    }

    public function updateMenuCategory(MenuCategory $menuCategory, array $data): MenuCategory
    {
        $menuCategory->update($data);

        return $menuCategory->refresh();
    }

    public function deleteMenuCategory(MenuCategory $menuCategory): bool
    {
        if ($menuCategory->menuItems()->withTrashed()->exists()) {
            return false;
        }

        return (bool) $menuCategory->delete();
    }
}
