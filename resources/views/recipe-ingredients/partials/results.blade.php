<x-table :rows="$recipeIngredients" caption="Recipe ingredients"
    :empty-title="$search !== '' || $recipeVersionId !== null ? 'No matching recipe ingredients' : 'No recipe ingredients yet'"
    :empty-description="$search !== '' || $recipeVersionId !== null ? 'Try changing the search or recipe filter.' : 'Add ingredients to a recipe version to calculate its cost.'">
    <x-slot:toolbar>
        <div class="unit-toolbar">
            <button type="button" class="btn btn-sm btn-success text-nowrap" data-bs-toggle="offcanvas"
                data-bs-target="#createRecipeIngredientDrawer" aria-label="Add recipe ingredient">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
            </button>

            <x-search-filter :action="route('recipe-ingredients.index')"
                search-label="Search recipe ingredients"
                search-placeholder="Search by menu item, ingredient, code, or notes"
                search-container-class="unit-search-field"
                class="unit-search-filter"
                data-live-search="true"
                data-live-target="#recipe-ingredient-results"
                data-live-delay="300"
                submit-label="Filter"
                :search-value="$search"
                :filters="[
                    [
                        'name' => 'recipe_version_id',
                        'label' => 'Recipe version',
                        'placeholder' => 'All recipe versions',
                        'value' => '',
                        'options' => $recipeVersions->mapWithKeys(fn ($version) => [
                            $version->id => ($version->menuItem?->name ?? 'Menu item') . ' · v' . $version->version_number,
                        ])->all(),
                        'class' => 'unit-status-filter',
                    ],
                ]" />
        </div>
    </x-slot:toolbar>

    <x-slot:head>
        <tr>
            <th scope="col">Recipe</th>
            <th scope="col">Ingredient</th>
            <th scope="col" class="text-end">Quantity</th>
            <th scope="col" class="text-end">Waste</th>
            <th scope="col" class="text-end">Unit cost</th>
            <th scope="col" class="text-end">Line cost</th>
            <th scope="col" class="text-end">Actions</th>
        </tr>
    </x-slot:head>

    <x-slot:body>
        @foreach ($recipeIngredients as $recipeIngredient)
            <tr>
                <td>
                    <span class="d-block fw-semibold">{{ $recipeIngredient->recipeVersion?->menuItem?->name ?? 'Deleted menu item' }}</span>
                    <span class="small text-secondary">v{{ $recipeIngredient->recipeVersion?->version_number }}</span>
                </td>
                <td>
                    <span class="d-block">{{ $recipeIngredient->ingredient?->name ?? 'Deleted ingredient' }}</span>
                    <span class="small text-secondary">{{ $recipeIngredient->ingredient?->code }}</span>
                </td>
                <td class="text-end">{{ number_format((float) $recipeIngredient->quantity, 3) }} {{ $recipeIngredient->unit?->abbreviation }}</td>
                <td class="text-end">{{ number_format((float) $recipeIngredient->waste_percentage, 2) }}%</td>
                <td class="text-end">{{ number_format((float) $recipeIngredient->unit_cost_snapshot, 6) }}</td>
                <td class="text-end">{{ number_format((float) $recipeIngredient->line_cost_snapshot, 2) }}</td>
                <td class="text-end text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="offcanvas"
                        data-bs-target="#editRecipeIngredientDrawer-{{ $recipeIngredient->id }}" aria-label="Edit {{ $recipeIngredient->ingredient?->name }} in {{ $recipeIngredient->recipeVersion?->menuItem?->name }}">
                        <i class="bi bi-pencil" aria-hidden="true"></i>
                    </button>
                    <x-confirm-delete :action="route('recipe-ingredients.destroy', $recipeIngredient)"
                        :title="'Remove ' . ($recipeIngredient->ingredient?->name ?? 'this ingredient') . ' from recipe?'"
                        text="This recipe ingredient row will be permanently removed."
                        button-label="Remove"
                        :button-aria-label="'Remove ' . ($recipeIngredient->ingredient?->name ?? 'recipe ingredient')"
                        button-class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash" aria-hidden="true"></i>
                    </x-confirm-delete>
                </td>
            </tr>
        @endforeach
    </x-slot:body>
</x-table>

<div data-live-edit-drawers>
    @include('recipe-ingredients.partials.edit-drawers')
</div>
