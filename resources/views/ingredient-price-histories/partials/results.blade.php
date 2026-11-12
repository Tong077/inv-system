<x-table :rows="$histories" caption="Ingredient price history"
    :empty-title="$search !== '' || $ingredientId !== null ? 'No matching price records' : 'No ingredient prices yet'"
    :empty-description="$search !== '' || $ingredientId !== null ? 'Try changing the search or ingredient filter.' : 'Record supplier package prices to support recipe costing.'">
    <x-slot:toolbar>
        <div class="unit-toolbar">
            <button type="button" class="btn btn-sm btn-success text-nowrap" data-bs-toggle="offcanvas"
                data-bs-target="#createIngredientPriceDrawer" aria-label="Add ingredient price">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
            </button>
            <x-search-filter :action="route('ingredient-price-histories.index')"
                search-label="Search ingredient prices"
                search-placeholder="Search by ingredient, supplier, code, or notes"
                search-container-class="unit-search-field"
                class="unit-search-filter"
                data-live-search="true"
                data-live-target="#ingredient-price-results"
                data-live-delay="300"
                submit-label="Filter"
                :search-value="$search"
                :filters="[
                    [
                        'name' => 'ingredient_id',
                        'label' => 'Ingredient',
                        'placeholder' => 'All ingredients',
                        'value' => '',
                        'options' => $ingredients->mapWithKeys(fn ($ingredient) => [
                            $ingredient->id => $ingredient->code . ' — ' . $ingredient->name,
                        ])->all(),
                        'class' => 'unit-status-filter',
                    ],
                ]" />
        </div>
    </x-slot:toolbar>
    <x-slot:head>
        <tr>
            <th scope="col">Ingredient</th>
            <th scope="col">Supplier</th>
            <th scope="col" class="text-end">Package</th>
            <th scope="col" class="text-end">Package price</th>
            <th scope="col" class="text-end">Cost per base unit</th>
            <th scope="col">Effective date</th>
            <th scope="col" class="text-end">Actions</th>
        </tr>
    </x-slot:head>
    <x-slot:body>
        @foreach ($histories as $history)
            <tr>
                <td>
                    <span class="d-block fw-semibold">{{ $history->ingredient?->name ?? 'Deleted ingredient' }}</span>
                    <span class="small text-secondary">{{ $history->ingredient?->code }}</span>
                </td>
                <td>{{ $history->supplier?->name ?? '—' }}</td>
                <td class="text-end">{{ number_format((float) $history->package_quantity, 3) }} {{ $history->packageUnit?->abbreviation }}</td>
                <td class="text-end">{{ number_format((float) $history->package_price, 2) }}</td>
                <td class="text-end">{{ number_format((float) $history->unit_cost, 6) }} / {{ $history->ingredient?->baseUnit?->abbreviation }}</td>
                <td>{{ $history->effective_date?->format('M j, Y') }}</td>
                <td class="text-end text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="offcanvas"
                        data-bs-target="#editIngredientPriceDrawer-{{ $history->id }}" aria-label="Edit price for {{ $history->ingredient?->name }}">
                        <i class="bi bi-pencil" aria-hidden="true"></i>
                    </button>
                    <x-confirm-delete :action="route('ingredient-price-histories.destroy', $history)"
                        :title="'Delete price record for ' . ($history->ingredient?->name ?? 'ingredient') . '?'"
                        text="This ingredient price record will be permanently removed."
                        :button-aria-label="'Delete price record for ' . ($history->ingredient?->name ?? 'ingredient')"
                        button-class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash" aria-hidden="true"></i>
                    </x-confirm-delete>
                </td>
            </tr>
        @endforeach
    </x-slot:body>
</x-table>
<div data-live-edit-drawers>
    @include('ingredient-price-histories.partials.edit-drawers')
</div>
