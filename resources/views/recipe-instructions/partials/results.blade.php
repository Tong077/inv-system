<x-table :rows="$instructions" caption="Recipe instructions"
    :empty-title="$search !== '' || $recipeVersionId !== null ? 'No matching recipe instructions' : 'No recipe instructions yet'"
    :empty-description="$search !== '' || $recipeVersionId !== null ? 'Try changing the search or recipe filter.' : 'Add preparation steps to a recipe version.'">
    <x-slot:toolbar>
        <div class="unit-toolbar">
            <button type="button" class="btn btn-sm btn-success text-nowrap" data-bs-toggle="offcanvas"
                data-bs-target="#createRecipeInstructionDrawer" aria-label="Add recipe instruction">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
            </button>
            <x-search-filter :action="route('recipe-instructions.index')"
                search-label="Search recipe instructions"
                search-placeholder="Search by menu item or instruction text"
                search-container-class="unit-search-field"
                class="unit-search-filter"
                data-live-search="true"
                data-live-target="#recipe-instruction-results"
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
            <th scope="col" class="text-end">Step</th>
            <th scope="col">Instruction</th>
            <th scope="col" class="text-end">Prep time</th>
            <th scope="col" class="text-end">Actions</th>
        </tr>
    </x-slot:head>
    <x-slot:body>
        @foreach ($instructions as $instruction)
            <tr>
                <td>
                    <span class="d-block fw-semibold">{{ $instruction->recipeVersion?->menuItem?->name ?? 'Deleted menu item' }}</span>
                    <span class="small text-secondary">v{{ $instruction->recipeVersion?->version_number }}</span>
                </td>
                <td class="text-end">{{ $instruction->step_number }}</td>
                <td>{{ $instruction->instruction }}</td>
                <td class="text-end">{{ $instruction->preparation_time_minutes === null ? '—' : $instruction->preparation_time_minutes . ' min' }}</td>
                <td class="text-end text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="offcanvas"
                        data-bs-target="#editRecipeInstructionDrawer-{{ $instruction->id }}" aria-label="Edit step {{ $instruction->step_number }}">
                        <i class="bi bi-pencil" aria-hidden="true"></i>
                    </button>
                    <x-confirm-delete :action="route('recipe-instructions.destroy', $instruction)"
                        :title="'Delete recipe step ' . $instruction->step_number . '?'"
                        text="This preparation step will be permanently removed."
                        :button-aria-label="'Delete step ' . $instruction->step_number"
                        button-class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash" aria-hidden="true"></i>
                    </x-confirm-delete>
                </td>
            </tr>
        @endforeach
    </x-slot:body>
</x-table>
<div data-live-edit-drawers>
    @include('recipe-instructions.partials.edit-drawers')
</div>
