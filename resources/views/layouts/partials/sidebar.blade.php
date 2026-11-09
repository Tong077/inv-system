<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark" aria-label="Workspace sections">
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}" class="brand-link">
            <span class="brand-image opacity-75 text-bg-success rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 2rem; height: 2rem;" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" width="21" height="21">
                    <path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                    <path d="m4.5 7.7 7.5 4.4 7.5-4.4M12 12.1V21" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                </svg>
            </span>
            <span class="brand-text fw-light">Stockroom</span>
        </a>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2" aria-label="Workspace navigation">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                <li class="nav-header">WORKSPACE</li>
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active text-primary' : '' }}" @if (request()->routeIs('dashboard')) aria-current="page" @endif>
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m4 10 8-6 8 6v9a1 1 0 0 1-1 1h-5v-6h-4v6H5a1 1 0 0 1-1-1v-9Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        </svg>
                        <p>Overview</p>
                    </a>
                </li>

                @php
                    $navigationGroupIcons = [
                        'INVENTORY SETUP' => 'bi-box-seam-fill',
                        'PURCHASING & RECEIVING' => 'bi-cart-fill',
                        'RECIPES & MENU' => 'bi-journal-richtext',
                        'STOCK OPERATIONS' => 'bi-clipboard-data-fill',
                    ];
                    $navigationGroups = [
                        'INVENTORY SETUP' => [
                            ['label' => 'Ingredients', 'route' => 'items.index', 'pattern' => 'items.*', 'icon' => 'bi-box-seam'],
                            ['label' => 'Ingredient categories', 'route' => 'ingredient-categories.index', 'pattern' => 'ingredient-categories.*', 'icon' => 'bi-tags'],
                            ['label' => 'Units of measure', 'route' => 'units.index', 'pattern' => 'units.*', 'icon' => 'bi-rulers'],
                            ['label' => 'Stock locations', 'route' => 'stock-locations.index', 'pattern' => 'stock-locations.*', 'icon' => 'bi-geo-alt'],
                            ['label' => 'Stock on hand', 'route' => 'stock-balances.index', 'pattern' => 'stock-balances.*', 'icon' => 'bi-boxes'],
                            ['label' => 'Stock movements', 'route' => 'stock-movements.index', 'pattern' => 'stock-movements.*', 'icon' => 'bi-arrow-left-right'],
                        ],
                        'PURCHASING & RECEIVING' => [
                            ['label' => 'Suppliers', 'route' => 'suppliers.index', 'pattern' => 'suppliers.*', 'icon' => 'bi-truck'],
                            ['label' => 'Supplier contacts', 'route' => 'supplier-contacts.index', 'pattern' => 'supplier-contacts.*', 'icon' => 'bi-person-lines-fill'],
                            ['label' => 'Purchase orders', 'route' => 'purchase-orders.index', 'pattern' => 'purchase-orders.*', 'icon' => 'bi-clipboard-check'],
                            ['label' => 'Purchase order lines', 'route' => 'purchase-order-items.index', 'pattern' => 'purchase-order-items.*', 'icon' => 'bi-list-check'],
                            ['label' => 'Goods receipts', 'route' => 'goods-receipts.index', 'pattern' => 'goods-receipts.*', 'icon' => 'bi-box-arrow-in-down'],
                            ['label' => 'Receipt lines', 'route' => 'goods-receipt-items.index', 'pattern' => 'goods-receipt-items.*', 'icon' => 'bi-card-list'],
                            ['label' => 'Ingredient price history', 'route' => 'ingredient-price-histories.index', 'pattern' => 'ingredient-price-histories.*', 'icon' => 'bi-clock-history'],
                            ['label' => 'Supplier payments', 'route' => 'supplier-payments.index', 'pattern' => 'supplier-payments.*', 'icon' => 'bi-credit-card'],
                        ],
                        'RECIPES & MENU' => [
                            ['label' => 'Menu categories', 'route' => 'menu-categories.index', 'pattern' => 'menu-categories.*', 'icon' => 'bi-tags'],
                            ['label' => 'Menu items', 'route' => 'menu-items.index', 'pattern' => 'menu-items.*', 'icon' => 'bi-journal-text'],
                            ['label' => 'Recipe versions', 'route' => 'recipe-versions.index', 'pattern' => 'recipe-versions.*', 'icon' => 'bi-journal-check'],
                            ['label' => 'Recipe ingredients', 'route' => 'recipe-ingredients.index', 'pattern' => 'recipe-ingredients.*', 'icon' => 'bi-basket'],
                            ['label' => 'Recipe instructions', 'route' => 'recipe-instructions.index', 'pattern' => 'recipe-instructions.*', 'icon' => 'bi-list-ol'],
                            ['label' => 'Other recipe costs', 'route' => 'recipe-other-costs.index', 'pattern' => 'recipe-other-costs.*', 'icon' => 'bi-calculator'],
                        ],
                        'STOCK OPERATIONS' => [
                            ['label' => 'Transfers', 'route' => 'stock-transfers.index', 'pattern' => 'stock-transfers.*', 'icon' => 'bi-arrow-left-right'],
                            ['label' => 'Transfer lines', 'route' => 'stock-transfer-items.index', 'pattern' => 'stock-transfer-items.*', 'icon' => 'bi-list-ul'],
                            ['label' => 'Adjustments', 'route' => 'stock-adjustments.index', 'pattern' => 'stock-adjustments.*', 'icon' => 'bi-sliders'],
                            ['label' => 'Adjustment lines', 'route' => 'stock-adjustment-items.index', 'pattern' => 'stock-adjustment-items.*', 'icon' => 'bi-list-ul'],
                            ['label' => 'Stock counts', 'route' => 'stock-counts.index', 'pattern' => 'stock-counts.*', 'icon' => 'bi-clipboard-data'],
                            ['label' => 'Count lines', 'route' => 'stock-count-items.index', 'pattern' => 'stock-count-items.*', 'icon' => 'bi-list-ul'],
                            ['label' => 'Stock waste', 'route' => 'stock-wastes.index', 'pattern' => 'stock-wastes.*', 'icon' => 'bi-trash'],
                            ['label' => 'Waste lines', 'route' => 'stock-waste-items.index', 'pattern' => 'stock-waste-items.*', 'icon' => 'bi-list-ul'],
                        ],
                    ];
                @endphp

                @foreach ($navigationGroups as $group => $links)
                    @php
                        $groupIsActive = collect($links)->contains(fn (array $link): bool => request()->routeIs($link['pattern']));
                        $submenuId = \Illuminate\Support\Str::slug($group).'-menu';
                    @endphp
                    <li class="nav-item {{ $groupIsActive ? 'menu-open' : '' }}">
                        <a
                            href="#"
                            class="nav-link {{ $groupIsActive ? 'active text-primary' : '' }}"
                            aria-expanded="{{ $groupIsActive ? 'true' : 'false' }}"
                            aria-controls="{{ $submenuId }}"
                        >
                            <i class="nav-icon bi {{ $navigationGroupIcons[$group] }}" aria-hidden="true"></i>
                            <p>
                                {{ $group }}
                                <i class="nav-arrow bi bi-chevron-right" aria-hidden="true"></i>
                            </p>
                        </a>
                        <ul id="{{ $submenuId }}" class="nav nav-treeview" style="display: {{ $groupIsActive ? 'block' : 'none' }};">
                            @foreach ($links as $link)
                                <li class="nav-item">
                                    <a
                                        href="{{ route($link['route']) }}"
                                        class="nav-link {{ request()->routeIs($link['pattern']) ? 'active text-primary' : '' }}"
                                        @if (request()->routeIs($link['pattern'])) aria-current="page" @endif
                                    >
                                        <i class="nav-icon bi {{ $link['icon'] }}" aria-hidden="true"></i>
                                        <p>{{ $link['label'] }}</p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>
</aside>
