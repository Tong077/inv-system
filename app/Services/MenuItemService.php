<?php

namespace App\Services;

use App\Models\MenuCategory;
use App\Models\Ingredient;
use App\Models\MenuItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Throwable;

class MenuItemService
{
    public function __construct(private readonly ImageHandleService $images) {}

    public function getAllMenuItems(?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        return MenuItem::query()
            ->with(['category', 'stockIngredient.baseUnit'])
            ->withCount('recipeVersions')
            ->when($search !== null && $search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
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

    /** @return Collection<int, MenuCategory> */
    public function getCategoryOptions(): Collection
    {
        return MenuCategory::query()
            ->orderBy('name')
            ->get(['id', 'name', 'is_active']);
    }

    /** @return Collection<int, Ingredient> */
    public function getStockItemOptions(): Collection
    {
        return Ingredient::query()
            ->with('baseUnit')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'base_unit_id', 'is_active']);
    }

    public function createMenuItem(array $data): MenuItem
    {
        if (($data['sale_mode'] ?? 'recipe') !== 'stock_item') {
            $data['stock_ingredient_id'] = null;
            $data['stock_quantity'] = null;
        }

        $image = $data['image'] ?? null;
        unset($data['image'], $data['remove_image']);

        $imagePath = $image instanceof UploadedFile
            ? $this->images->uploadImage($image, 'menu-items')
            : null;

        if ($image instanceof UploadedFile && $imagePath === null) {
            throw new RuntimeException('The menu item image could not be saved.');
        }

        try {
            return MenuItem::create([...$data, 'image_path' => $imagePath]);
        } catch (Throwable $exception) {
            $this->images->deleteImage($imagePath);
            throw $exception;
        }
    }

    public function updateMenuItem(MenuItem $menuItem, array $data): MenuItem
    {
        if (($data['sale_mode'] ?? 'recipe') !== 'stock_item') {
            $data['stock_ingredient_id'] = null;
            $data['stock_quantity'] = null;
        }

        $image = $data['image'] ?? null;
        $removeImage = (bool) ($data['remove_image'] ?? false);
        unset($data['image'], $data['remove_image']);

        $oldImagePath = $menuItem->image_path;
        $newImagePath = $image instanceof UploadedFile
            ? $this->images->uploadImage($image, 'menu-items')
            : null;

        if ($image instanceof UploadedFile && $newImagePath === null) {
            throw new RuntimeException('The replacement menu item image could not be saved.');
        }

        if ($newImagePath !== null) {
            $data['image_path'] = $newImagePath;
        } elseif ($removeImage) {
            $data['image_path'] = null;
        }

        try {
            $menuItem->update($data);
        } catch (Throwable $exception) {
            $this->images->deleteImage($newImagePath);
            throw $exception;
        }

        if ($oldImagePath !== null && ($newImagePath !== null || $removeImage)) {
            $this->images->deleteImage($oldImagePath);
        }

        return $menuItem->refresh();
    }

    public function deleteMenuItem(MenuItem $menuItem): bool
    {
        if ($menuItem->recipeVersions()->exists()) {
            return false;
        }

        return (bool) $menuItem->delete();
    }
}
