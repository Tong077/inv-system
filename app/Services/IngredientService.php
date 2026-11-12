<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\IngredientCategory;
use App\Models\Unit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Throwable;

class IngredientService
{
    public function __construct(private readonly ImageHandleService $images) {}

    public function getAllIngredients(?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        return Ingredient::query()
            ->with(['baseUnit', 'ingredientCategory'])
            ->withSum('stockBalances', 'quantity_on_hand')
            ->when($search !== null && $search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
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

    /**
     * @return array{categories: \Illuminate\Database\Eloquent\Collection, baseUnits: \Illuminate\Database\Eloquent\Collection}
     */
    public function getFormOptions(): array
    {
        return [
            'categories' => IngredientCategory::query()->orderBy('name')->get(['id', 'name', 'is_active']),
            'baseUnits' => Unit::query()
                ->where('is_base_unit', true)
                ->orderBy('dimension')
                ->orderBy('name')
                ->get(['id', 'name', 'abbreviation', 'dimension', 'is_active']),
        ];
    }

    public function createIngredient(array $data): Ingredient
    {
        $image = $data['image'] ?? null;
        unset($data['image'], $data['remove_image']);

        $imagePath = $image instanceof UploadedFile
            ? $this->images->uploadImage($image, 'ingredients')
            : null;

        if ($image instanceof UploadedFile && $imagePath === null) {
            throw new RuntimeException('The ingredient image could not be saved.');
        }

        try {
            return Ingredient::create([...$data, 'image_path' => $imagePath]);
        } catch (Throwable $exception) {
            $this->images->deleteImage($imagePath);
            throw $exception;
        }
    }

    public function updateIngredient(Ingredient $ingredient, array $data): Ingredient
    {
        $image = $data['image'] ?? null;
        $removeImage = (bool) ($data['remove_image'] ?? false);
        unset($data['image'], $data['remove_image']);

        $oldImagePath = $ingredient->image_path;
        $newImagePath = $image instanceof UploadedFile
            ? $this->images->uploadImage($image, 'ingredients')
            : null;

        if ($image instanceof UploadedFile && $newImagePath === null) {
            throw new RuntimeException('The replacement ingredient image could not be saved.');
        }

        if ($newImagePath !== null) {
            $data['image_path'] = $newImagePath;
        } elseif ($removeImage) {
            $data['image_path'] = null;
        }

        try {
            $ingredient->update($data);
        } catch (Throwable $exception) {
            $this->images->deleteImage($newImagePath);
            throw $exception;
        }

        if ($oldImagePath !== null && ($newImagePath !== null || $removeImage)) {
            $this->images->deleteImage($oldImagePath);
        }

        return $ingredient->refresh();
    }

    public function deleteIngredient(Ingredient $ingredient): bool
    {
        if ($ingredient->priceHistories()->exists()
            || $ingredient->recipeIngredients()->exists()
            || $ingredient->stockBalances()->exists()
            || $ingredient->stockMovements()->exists()
            || $ingredient->purchaseOrderItems()->exists()
            || $ingredient->goodsReceiptItems()->exists()) {
            return false;
        }

        return (bool) $ingredient->delete();
    }
}
