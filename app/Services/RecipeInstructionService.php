<?php

namespace App\Services;

use App\Models\RecipeInstruction;
use App\Models\RecipeVersion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class RecipeInstructionService
{
    public function getAll(?string $search = null, ?int $recipeVersionId = null): LengthAwarePaginator
    {
        return RecipeInstruction::query()
            ->with('recipeVersion.menuItem')
            ->when($recipeVersionId !== null, fn (Builder $query) => $query->where('recipe_version_id', $recipeVersionId))
            ->when($search !== null && $search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('instruction', 'like', "%{$search}%")
                        ->orWhereHas('recipeVersion.menuItem', function (Builder $menuItems) use ($search): void {
                            $menuItems->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('recipe_version_id')
            ->orderBy('step_number')
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

    public function create(array $data): RecipeInstruction
    {
        return RecipeInstruction::create($data);
    }

    public function update(RecipeInstruction $instruction, array $data): RecipeInstruction
    {
        $instruction->update($data);

        return $instruction->refresh();
    }

    public function delete(RecipeInstruction $instruction): bool
    {
        return (bool) $instruction->delete();
    }
}
