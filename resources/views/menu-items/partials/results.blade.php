<section class="card data-table-card menu-item-catalog-card">
    <div class="card-header data-table-toolbar">
        <div class="unit-toolbar">
            <button type="button" class="btn btn-sm btn-success text-nowrap" data-bs-toggle="offcanvas"
                data-bs-target="#createMenuItemDrawer" aria-label="Add menu item">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
            </button>

            <x-search-filter :action="route('menu-items.index')"
                search-label="Search menu items"
                search-placeholder="Search by code, name, or description"
                search-container-class="unit-search-field"
                class="unit-search-filter"
                data-live-search="true"
                data-live-target="#menu-item-results"
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

    <div class="card-body menu-item-catalog-body">
        @if ($menuItems->isEmpty())
            <div class="catalog-grid-empty">
                <span class="catalog-grid-empty-icon" aria-hidden="true"><i class="bi bi-journal-richtext"></i></span>
                <h2 class="h5 mb-1">{{ $search !== '' || in_array($status, ['active', 'inactive'], true) ? 'No matching menu items' : 'No menu items yet' }}</h2>
                <p class="text-secondary mb-0">{{ $search !== '' || in_array($status, ['active', 'inactive'], true) ? 'Try changing your search or status filter.' : 'Create a menu item and choose recipe-based or direct stock deduction.' }}</p>
            </div>
        @else
            <div class="row g-3 menu-item-card-grid">
                @foreach ($menuItems as $menuItem)
                    <div class="col-6 col-md-4 col-xl-3">
                        <article class="card h-100 catalog-card">
                            <div class="catalog-card-image">
                                @if ($menuItem->image_url)
                                    <img src="{{ $menuItem->image_url }}" alt="" loading="lazy">
                                @else
                                    <span class="catalog-card-image-empty" aria-hidden="true"><i class="bi bi-image"></i></span>
                                @endif
                                <span class="catalog-card-status {{ $menuItem->is_active ? 'catalog-card-status-active' : 'catalog-card-status-inactive' }}">
                                    {{ $menuItem->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            <div class="card-body d-flex flex-column">
                                <div class="catalog-card-code-row">
                                    <span class="catalog-card-code">{{ $menuItem->code }}</span>
                                </div>
                                <h2 class="catalog-card-name">{{ $menuItem->name }}</h2>
                                <span class="catalog-card-category">
                                    {{ $menuItem->category?->name ?? 'Uncategorized' }}
                                </span>
                                <span class="catalog-card-subtitle">
                                    {{ $menuItem->barcode ? 'Barcode: '.$menuItem->barcode : 'Barcode not assigned' }}
                                </span>
                                <span class="catalog-card-subtitle mt-1">
                                    @if ($menuItem->sale_mode === 'stock_item')
                                        Direct stock: {{ $menuItem->stockIngredient?->name ?? 'Inventory item missing' }}
                                    @else
                                        Deduct from recipe ingredients
                                    @endif
                                </span>

                                <div class="catalog-card-stats">
                                    <div class="catalog-card-stat">
                                        <span>Selling price</span>
                                        <strong>{{ $menuItem->currency }} {{ number_format((float) $menuItem->selling_price, 2) }}</strong>
                                    </div>
                                    <div class="catalog-card-stat">
                                        <span>{{ $menuItem->sale_mode === 'stock_item' ? 'Stock per sale' : 'Recipe versions' }}</span>
                                        <strong>{{ $menuItem->sale_mode === 'stock_item' ? number_format((float) $menuItem->stock_quantity, 3).' '.($menuItem->stockIngredient?->baseUnit?->abbreviation ?? '') : $menuItem->recipe_versions_count }}</strong>
                                    </div>
                                </div>

                                <div class="catalog-card-actions mt-auto">
                                    <button type="button" class="btn btn-sm btn-light catalog-card-view-button"
                                        data-bs-toggle="offcanvas" data-bs-target="#editMenuItemDrawer-{{ $menuItem->id }}"
                                        aria-label="View details for {{ $menuItem->name }}">
                                        View details
                                    </button>
                                    <x-confirm-delete :action="route('menu-items.destroy', $menuItem)"
                                        :title="'Delete ' . $menuItem->name . '?'"
                                        text="This menu item cannot be deleted while it has recipe versions."
                                        :button-aria-label="'Delete ' . $menuItem->name"
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

    @if ($menuItems->hasPages())
        <div class="card-footer data-table-footer">
            {{ $menuItems->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    @endif
</section>

<div data-live-edit-drawers>
    @include('menu-items.partials.edit-drawers')
</div>
