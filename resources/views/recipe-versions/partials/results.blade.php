<x-table :rows="$recipeVersions" caption="Recipe versions"
    :empty-title="$search !== '' || in_array($current, ['current', 'previous'], true) ? 'No matching recipe versions' : 'No recipe versions yet'"
    :empty-description="$search !== '' || in_array($current, ['current', 'previous'], true) ? 'Try changing your search or current-version filter.' : 'Create a recipe version for a menu item to begin.'">
    <x-slot:toolbar>
        <div class="unit-toolbar">
            <button type="button" class="btn btn-sm btn-success text-nowrap" data-bs-toggle="offcanvas"
                data-bs-target="#createRecipeVersionDrawer" aria-label="Add recipe version">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
            </button>

            <x-search-filter :action="route('recipe-versions.index')"
                search-label="Search recipe versions"
                search-placeholder="Search by menu item, code, version, or notes"
                search-container-class="unit-search-field"
                class="unit-search-filter"
                data-live-search="true"
                data-live-target="#recipe-version-results"
                data-live-delay="300"
                submit-label="Filter"
                :search-value="$search"
                :filters="[
                    [
                        'name' => 'current',
                        'label' => 'Version status',
                        'placeholder' => 'Show all',
                        'value' => 'all',
                        'resetValue' => 'all',
                        'showPlaceholderOption' => false,
                        'options' => ['all' => 'Show all', 'current' => 'Current', 'previous' => 'Previous'],
                        'class' => 'unit-status-filter',
                    ],
                ]" />
        </div>
    </x-slot:toolbar>

    <x-slot:head>
        <tr>
            <th scope="col">Menu item</th>
            <th scope="col">Version</th>
            <th scope="col" class="text-end">Yield</th>
            <th scope="col">Effective period</th>
            <th scope="col">Status</th>
            <th scope="col" class="text-end">Recipe details</th>
            <th scope="col" class="text-end">Actions</th>
        </tr>
    </x-slot:head>

    <x-slot:body>
        @foreach ($recipeVersions as $recipeVersion)
            <tr>
                <td>
                    <span class="d-block fw-semibold">{{ $recipeVersion->menuItem?->name ?? 'Deleted menu item' }}</span>
                    @if ($recipeVersion->menuItem?->code)
                        <span class="small text-secondary">{{ $recipeVersion->menuItem->code }}</span>
                    @endif
                </td>
                <td>v{{ $recipeVersion->version_number }}</td>
                <td class="text-end">
                    {{ number_format((float) $recipeVersion->yield_quantity, 3) }}
                    {{ $recipeVersion->yieldUnit?->abbreviation ?? '' }}
                </td>
                <td>
                    {{ $recipeVersion->effective_from?->format('M j, Y') ?? '—' }}
                    <span class="text-secondary">to</span>
                    {{ $recipeVersion->effective_until?->format('M j, Y') ?? 'Ongoing' }}
                </td>
                <td>
                    <span class="badge {{ $recipeVersion->is_current ? 'text-bg-success' : 'text-bg-secondary' }}">
                        {{ $recipeVersion->is_current ? 'Current' : 'Previous' }}
                    </span>
                </td>
                <td class="text-end">
                    {{ $recipeVersion->ingredients_count }} ingredients ·
                    {{ $recipeVersion->instructions_count }} steps ·
                    {{ $recipeVersion->other_costs_count }} costs
                </td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('recipe-ingredients.index', ['recipe_version_id' => $recipeVersion->id]) }}"
                        class="btn btn-sm btn-outline-secondary" aria-label="Manage ingredients for {{ $recipeVersion->menuItem?->name }} version {{ $recipeVersion->version_number }}">
                        <i class="bi bi-list-ul" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('recipe-instructions.index', ['recipe_version_id' => $recipeVersion->id]) }}"
                        class="btn btn-sm btn-outline-secondary" aria-label="Manage instructions for {{ $recipeVersion->menuItem?->name }} version {{ $recipeVersion->version_number }}">
                        <i class="bi bi-list-ol" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('recipe-other-costs.index', ['recipe_version_id' => $recipeVersion->id]) }}"
                        class="btn btn-sm btn-outline-secondary" aria-label="Manage additional costs for {{ $recipeVersion->menuItem?->name }} version {{ $recipeVersion->version_number }}">
                        <i class="bi bi-calculator" aria-hidden="true"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="offcanvas"
                        data-bs-target="#editRecipeVersionDrawer-{{ $recipeVersion->id }}" aria-label="Edit version {{ $recipeVersion->version_number }} of {{ $recipeVersion->menuItem?->name }}">
                        <i class="bi bi-pencil" aria-hidden="true"></i>
                    </button>
                    <x-confirm-delete :action="route('recipe-versions.destroy', $recipeVersion)"
                        :title="'Delete recipe version ' . $recipeVersion->version_number . '?'"
                        text="A recipe version with ingredients, instructions, or costs cannot be deleted."
                        :button-aria-label="'Delete recipe version ' . $recipeVersion->version_number"
                        button-class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash" aria-hidden="true"></i>
                    </x-confirm-delete>
                </td>
            </tr>
        @endforeach
    </x-slot:body>
</x-table>

<div data-live-edit-drawers>
    @include('recipe-versions.partials.edit-drawers')
</div>
