<x-table :rows="$costs" caption="Other recipe costs"
    :empty-title="$search !== '' || $recipeVersionId !== null ? 'No matching additional costs' : 'No additional recipe costs yet'"
    :empty-description="$search !== '' || $recipeVersionId !== null ? 'Try changing the search or recipe filter.' : 'Add packaging, labor, or another cost to a recipe version.'">
    <x-slot:toolbar>
        <div class="unit-toolbar">
            <button type="button" class="btn btn-sm btn-success text-nowrap" data-bs-toggle="offcanvas"
                data-bs-target="#createRecipeOtherCostDrawer" aria-label="Add recipe cost">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
            </button>
            <x-search-filter :action="route('recipe-other-costs.index')"
                search-label="Search recipe costs"
                search-placeholder="Search by menu item, cost name, type, or notes"
                search-container-class="unit-search-field"
                class="unit-search-filter"
                data-live-search="true"
                data-live-target="#recipe-other-cost-results"
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
            <th scope="col">Cost</th>
            <th scope="col">Type</th>
            <th scope="col" class="text-end">Amount</th>
            <th scope="col">Notes</th>
            <th scope="col" class="text-end">Actions</th>
        </tr>
    </x-slot:head>
    <x-slot:body>
        @foreach ($costs as $cost)
            <tr>
                <td>
                    <span class="d-block fw-semibold">{{ $cost->recipeVersion?->menuItem?->name ?? 'Deleted menu item' }}</span>
                    <span class="small text-secondary">v{{ $cost->recipeVersion?->version_number }}</span>
                </td>
                <td>{{ $cost->name }}</td>
                <td><span class="badge text-bg-light border text-capitalize">{{ $cost->cost_type }}</span></td>
                <td class="text-end">{{ number_format((float) $cost->amount, 2) }}{{ $cost->cost_type === 'percentage' ? '%' : '' }}</td>
                <td>{{ $cost->notes ?: '—' }}</td>
                <td class="text-end text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="offcanvas"
                        data-bs-target="#editRecipeOtherCostDrawer-{{ $cost->id }}" aria-label="Edit {{ $cost->name }}">
                        <i class="bi bi-pencil" aria-hidden="true"></i>
                    </button>
                    <x-confirm-delete :action="route('recipe-other-costs.destroy', $cost)"
                        :title="'Delete ' . $cost->name . '?'"
                        text="This additional recipe cost will be permanently removed."
                        :button-aria-label="'Delete ' . $cost->name"
                        button-class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash" aria-hidden="true"></i>
                    </x-confirm-delete>
                </td>
            </tr>
        @endforeach
    </x-slot:body>
</x-table>
<div data-live-edit-drawers>
    @include('recipe-other-costs.partials.edit-drawers')
</div>
