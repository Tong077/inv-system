<section class="card data-table-card ingredient-catalog-card">
    <div class="card-header data-table-toolbar">
        <div class="unit-toolbar">
            <button type="button" class="btn btn-sm btn-success text-nowrap" data-bs-toggle="offcanvas"
                data-bs-target="#createIngredientDrawer" aria-label="Add inventory item">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
            </button>

            <x-search-filter :action="route('items.index')"
                search-label="Search inventory items"
                search-placeholder="Search inventory items by code or name"
                search-container-class="unit-search-field"
                class="unit-search-filter"
                data-live-search="true"
                data-live-target="#ingredient-results"
                data-live-delay="300"
                submit-label="Filter"
                :search-value="$search"
                :filters="[
                    [
                        'name' => 'status',
                        'label' => 'Status',
                        'placeholder' => 'Show all',
                        'value' => 'all',
                        'resetValue' => 'all',
                        'showPlaceholderOption' => false,
                        'options' => ['all' => 'Show all', 'active' => 'Active', 'inactive' => 'Inactive'],
                        'class' => 'unit-status-filter',
                    ],
                ]" />
        </div>
    </div>

    <div class="card-body ingredient-catalog-body">
        @if ($ingredients->isEmpty())
            <div class="catalog-grid-empty">
                <span class="catalog-grid-empty-icon" aria-hidden="true"><i class="bi bi-box-seam"></i></span>
                <h2 class="h5 mb-1">{{ $search !== '' || in_array($status, ['active', 'inactive'], true) ? 'No matching inventory items' : 'No inventory items yet' }}</h2>
                <p class="text-secondary mb-0">{{ $search !== '' || in_array($status, ['active', 'inactive'], true) ? 'Try changing the search or status filter.' : 'Create base units before adding inventory items.' }}</p>
            </div>
        @else
            <div class="row g-3 ingredient-card-grid">
                @foreach ($ingredients as $ingredient)
                    @php
                        $quantityOnHand = (float) ($ingredient->stock_balances_sum_quantity_on_hand ?? 0);
                        $minimumStock = (float) $ingredient->minimum_stock;
                        $stockStatus = $quantityOnHand <= 0
                            ? 'out'
                            : ($quantityOnHand <= $minimumStock ? 'low' : 'in');
                        $stockStatusLabel = [
                            'out' => 'Out of stock',
                            'low' => 'Low stock',
                            'in' => 'In stock',
                        ][$stockStatus];
                        $baseUnit = $ingredient->baseUnit?->abbreviation ?? 'units';
                        $baseUnitLabel = $ingredient->baseUnit
                            ? $ingredient->baseUnit->name.' ('.$ingredient->baseUnit->abbreviation.')'
                            : 'Unit not set';
                    @endphp
                    <div class="col-6 col-md-4 col-xl-3">
                        <article class="card h-100 catalog-card">
                            <div class="catalog-card-image">
                                @if ($ingredient->image_url)
                                    <img src="{{ $ingredient->image_url }}" alt="" loading="lazy">
                                @else
                                    <span class="catalog-card-image-empty" aria-hidden="true"><i class="bi bi-image"></i></span>
                                @endif
                                <span class="catalog-card-status catalog-card-status-{{ $stockStatus }}">{{ $stockStatusLabel }}</span>
                            </div>

                            <div class="card-body d-flex flex-column">
                                <div class="catalog-card-code-row">
                                    <span class="catalog-card-code">{{ $ingredient->code }}</span>
                                    <span class="catalog-card-active-badge {{ $ingredient->is_active ? 'is-active' : 'is-inactive' }}">
                                        {{ $ingredient->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <h2 class="catalog-card-name">{{ $ingredient->name }}</h2>
                                <span class="catalog-card-category">
                                    {{ $ingredient->ingredientCategory?->name ?? 'Uncategorized' }}
                                </span>
                                <span class="catalog-card-subtitle">{{ $baseUnitLabel }}</span>

                                <div class="catalog-card-stats">
                                    <div class="catalog-card-stat">
                                        <span>Quantity</span>
                                        <strong>{{ number_format($quantityOnHand, 3) }} {{ $baseUnit }}</strong>
                                    </div>
                                    <div class="catalog-card-stat">
                                        <span>Reorder at</span>
                                        <strong>{{ number_format($minimumStock, 3) }} {{ $baseUnit }}</strong>
                                    </div>
                                </div>

                                <div class="catalog-card-actions mt-auto">
                                    <button type="button" class="btn btn-sm btn-light catalog-card-view-button"
                                        data-bs-toggle="offcanvas" data-bs-target="#editIngredientDrawer-{{ $ingredient->id }}"
                                        aria-label="View details for {{ $ingredient->name }}">
                                        View details
                                    </button>
                                    <x-confirm-delete :action="route('items.destroy', $ingredient)"
                                        :title="'Delete ' . $ingredient->name . '?'"
                                        text="This ingredient can only be deleted if it has no inventory or transaction history."
                                        :button-aria-label="'Delete ' . $ingredient->name"
                                        button-class="btn btn-sm btn-outline-danger catalog-card-delete-button">
                                        <i class="bi bi-trash" aria-hidden="true"></i>
                                    </x-confirm-delete>
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if ($ingredients->hasPages())
        <div class="card-footer data-table-footer">
            {{ $ingredients->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    @endif
</section>

<div data-live-edit-drawers>
    @include('items.partials.edit-drawers')
</div>
