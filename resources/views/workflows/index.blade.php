@extends('layouts.app')

@php
    $resource = $resource ?? 'ingredients';
    $records = $records ?? collect();
    $schemas = [
        'units' => ['title' => 'Units of measure', 'eyebrow' => 'INVENTORY SETUP', 'description' => 'Review measurement units and their conversion rules.', 'columns' => [['name' => 'name', 'label' => 'Unit'], ['name' => 'abbreviation', 'label' => 'Abbreviation'], ['name' => 'dimension', 'label' => 'Dimension'], ['name' => 'conversion_factor_to_base', 'label' => 'Base factor', 'type' => 'number'], ['name' => 'is_base_unit', 'label' => 'Base unit', 'type' => 'boolean'], ['name' => 'is_active', 'label' => 'Status', 'type' => 'boolean']]],
        'ingredients' => ['title' => 'Ingredients', 'eyebrow' => 'INVENTORY FOUNDATION', 'description' => 'Manage ingredient records and their base stock units.', 'columns' => [['name' => 'code', 'label' => 'Code'], ['name' => 'name', 'label' => 'Ingredient'], ['name' => 'baseUnit.abbreviation', 'label' => 'Base unit'], ['name' => 'minimum_stock', 'label' => 'Reorder threshold', 'type' => 'number'], ['name' => 'is_active', 'label' => 'Status', 'type' => 'boolean']]],
        'menu-categories' => ['title' => 'Menu categories', 'eyebrow' => 'RECIPE SETUP', 'description' => 'Organize menu items into categories.', 'columns' => [['name' => 'name', 'label' => 'Category'], ['name' => 'description', 'label' => 'Description'], ['name' => 'is_active', 'label' => 'Status', 'type' => 'boolean']]],
        'suppliers' => ['title' => 'Suppliers', 'eyebrow' => 'PURCHASING SETUP', 'description' => 'Manage supplier details used by purchase orders and price history.', 'columns' => [['name' => 'code', 'label' => 'Code'], ['name' => 'name', 'label' => 'Supplier'], ['name' => 'phone', 'label' => 'Phone'], ['name' => 'email', 'label' => 'Email'], ['name' => 'payment_terms', 'label' => 'Payment terms'], ['name' => 'is_active', 'label' => 'Status', 'type' => 'boolean']]],
        'supplier-contacts' => ['title' => 'Supplier contacts', 'eyebrow' => 'PURCHASING SETUP', 'description' => 'View contact people associated with suppliers.', 'columns' => [['name' => 'supplier.name', 'label' => 'Supplier'], ['name' => 'name', 'label' => 'Contact'], ['name' => 'position', 'label' => 'Position'], ['name' => 'phone', 'label' => 'Phone'], ['name' => 'email', 'label' => 'Email']]],
        'stock-locations' => ['title' => 'Stock locations', 'eyebrow' => 'INVENTORY SETUP', 'description' => 'Manage storage locations used for stock balances and transactions.', 'columns' => [['name' => 'code', 'label' => 'Code'], ['name' => 'name', 'label' => 'Location'], ['name' => 'location_type', 'label' => 'Type'], ['name' => 'address', 'label' => 'Address'], ['name' => 'is_active', 'label' => 'Status', 'type' => 'boolean']]],
        'menu-items' => ['title' => 'Menu items', 'eyebrow' => 'RECIPE SETUP', 'description' => 'Manage saleable menu items and their prices.', 'columns' => [['name' => 'code', 'label' => 'Code'], ['name' => 'name', 'label' => 'Menu item'], ['name' => 'category.name', 'label' => 'Category'], ['name' => 'selling_price', 'label' => 'Selling price', 'type' => 'money'], ['name' => 'currency', 'label' => 'Currency'], ['name' => 'is_active', 'label' => 'Status', 'type' => 'boolean']]],
        'ingredient-price-histories' => ['title' => 'Ingredient price history', 'eyebrow' => 'PURCHASING', 'description' => 'Review supplier package prices and normalized unit costs over time.', 'columns' => [['name' => 'ingredient.name', 'label' => 'Ingredient'], ['name' => 'supplier.name', 'label' => 'Supplier'], ['name' => 'package_price', 'label' => 'Package price', 'type' => 'money'], ['name' => 'package_quantity', 'label' => 'Package quantity', 'type' => 'number'], ['name' => 'packageUnit.abbreviation', 'label' => 'Unit'], ['name' => 'unit_cost', 'label' => 'Cost per base unit', 'type' => 'money'], ['name' => 'effective_date', 'label' => 'Effective date', 'type' => 'date']]],
        'purchase-orders' => ['title' => 'Purchase orders', 'eyebrow' => 'PURCHASING', 'description' => 'Track intended purchases. A purchase order does not increase available stock.', 'columns' => [['name' => 'po_number', 'label' => 'PO number'], ['name' => 'supplier.name', 'label' => 'Supplier'], ['name' => 'order_date', 'label' => 'Order date', 'type' => 'date'], ['name' => 'expected_date', 'label' => 'Expected', 'type' => 'date'], ['name' => 'total_amount', 'label' => 'Total', 'type' => 'money'], ['name' => 'status', 'label' => 'Status', 'type' => 'badge']]],
        'purchase-order-items' => ['title' => 'Purchase order lines', 'eyebrow' => 'PURCHASING', 'description' => 'Review ingredients, order quantities, and prices on purchase orders.', 'columns' => [['name' => 'purchaseOrder.po_number', 'label' => 'PO number'], ['name' => 'ingredient.name', 'label' => 'Ingredient'], ['name' => 'quantity', 'label' => 'Quantity', 'type' => 'number'], ['name' => 'unit.abbreviation', 'label' => 'Unit'], ['name' => 'unit_price', 'label' => 'Unit price', 'type' => 'money'], ['name' => 'total_price', 'label' => 'Line total', 'type' => 'money']]],
        'goods-receipts' => ['title' => 'Goods receipts', 'eyebrow' => 'RECEIVING', 'description' => 'Review received deliveries and their destination locations.', 'columns' => [['name' => 'receipt_number', 'label' => 'Receipt'], ['name' => 'supplier.name', 'label' => 'Supplier'], ['name' => 'purchaseOrder.po_number', 'label' => 'Purchase order'], ['name' => 'stockLocation.name', 'label' => 'Location'], ['name' => 'received_date', 'label' => 'Received', 'type' => 'date'], ['name' => 'status', 'label' => 'Status', 'type' => 'badge']]],
        'goods-receipt-items' => ['title' => 'Goods receipt lines', 'eyebrow' => 'RECEIVING', 'description' => 'Review received quantities, batch details, expiry dates, and actual prices.', 'columns' => [['name' => 'goodsReceipt.receipt_number', 'label' => 'Receipt'], ['name' => 'ingredient.name', 'label' => 'Ingredient'], ['name' => 'quantity_ordered', 'label' => 'Ordered', 'type' => 'number'], ['name' => 'quantity_received', 'label' => 'Received', 'type' => 'number'], ['name' => 'unit.abbreviation', 'label' => 'Unit'], ['name' => 'unit_price', 'label' => 'Unit price', 'type' => 'money'], ['name' => 'batch_number', 'label' => 'Batch'], ['name' => 'expiry_date', 'label' => 'Expiry', 'type' => 'date']]],
        'supplier-payments' => ['title' => 'Supplier payments', 'eyebrow' => 'PURCHASING', 'description' => 'Review recorded supplier payment details and status.', 'columns' => [['name' => 'supplier.name', 'label' => 'Supplier'], ['name' => 'purchaseOrder.po_number', 'label' => 'Purchase order'], ['name' => 'payment_date', 'label' => 'Payment date', 'type' => 'date'], ['name' => 'amount', 'label' => 'Amount', 'type' => 'money'], ['name' => 'payment_method', 'label' => 'Method'], ['name' => 'reference_number', 'label' => 'Reference'], ['name' => 'status', 'label' => 'Status', 'type' => 'badge']]],
        'recipe-versions' => ['title' => 'Recipe versions', 'eyebrow' => 'RECIPES & COSTING', 'description' => 'Review recipe yields, version history, and effective dates.', 'columns' => [['name' => 'menuItem.name', 'label' => 'Menu item'], ['name' => 'version_number', 'label' => 'Version'], ['name' => 'yield_quantity', 'label' => 'Yield', 'type' => 'number'], ['name' => 'yieldUnit.abbreviation', 'label' => 'Yield unit'], ['name' => 'is_current', 'label' => 'Current', 'type' => 'boolean'], ['name' => 'effective_from', 'label' => 'Effective from', 'type' => 'date'], ['name' => 'effective_until', 'label' => 'Effective until', 'type' => 'date']]],
        'recipe-ingredients' => ['title' => 'Recipe ingredients', 'eyebrow' => 'RECIPES & COSTING', 'description' => 'Review normalized recipe quantities and saved ingredient cost snapshots.', 'columns' => [['name' => 'recipeVersion.menuItem.name', 'label' => 'Menu item'], ['name' => 'ingredient.name', 'label' => 'Ingredient'], ['name' => 'quantity', 'label' => 'Recipe quantity', 'type' => 'number'], ['name' => 'unit.abbreviation', 'label' => 'Unit'], ['name' => 'waste_percentage', 'label' => 'Waste %', 'type' => 'number'], ['name' => 'unit_cost_snapshot', 'label' => 'Unit cost', 'type' => 'money'], ['name' => 'line_cost_snapshot', 'label' => 'Line cost', 'type' => 'money']]],
        'recipe-instructions' => ['title' => 'Recipe instructions', 'eyebrow' => 'RECIPES & COSTING', 'description' => 'Review preparation steps and their estimated times.', 'columns' => [['name' => 'recipeVersion.menuItem.name', 'label' => 'Menu item'], ['name' => 'step_number', 'label' => 'Step'], ['name' => 'instruction', 'label' => 'Instruction'], ['name' => 'preparation_time_minutes', 'label' => 'Minutes', 'type' => 'number']]],
        'recipe-other-costs' => ['title' => 'Other recipe costs', 'eyebrow' => 'RECIPES & COSTING', 'description' => 'Review additional fixed or percentage costs included in recipe costing.', 'columns' => [['name' => 'recipeVersion.menuItem.name', 'label' => 'Menu item'], ['name' => 'name', 'label' => 'Cost'], ['name' => 'amount', 'label' => 'Amount', 'type' => 'money'], ['name' => 'cost_type', 'label' => 'Cost type', 'type' => 'badge'], ['name' => 'notes', 'label' => 'Notes']]],
        'stock-balances' => ['title' => 'Stock on hand', 'eyebrow' => 'INVENTORY', 'description' => 'Current quantity and average unit cost by ingredient and location.', 'columns' => [['name' => 'ingredient.code', 'label' => 'Code'], ['name' => 'ingredient.name', 'label' => 'Ingredient'], ['name' => 'stockLocation.name', 'label' => 'Location'], ['name' => 'quantity_on_hand', 'label' => 'On hand', 'type' => 'number'], ['name' => 'ingredient.baseUnit.abbreviation', 'label' => 'Base unit'], ['name' => 'average_unit_cost', 'label' => 'Average unit cost', 'type' => 'money'], ['name' => 'last_movement_at', 'label' => 'Last movement', 'type' => 'datetime']]],
        'stock-movements' => ['title' => 'Stock movement ledger', 'eyebrow' => 'INVENTORY AUDIT', 'description' => 'Auditable stock changes by ingredient, location, and source reference.', 'columns' => [['name' => 'movement_date', 'label' => 'Date', 'type' => 'datetime'], ['name' => 'ingredient.name', 'label' => 'Ingredient'], ['name' => 'stockLocation.name', 'label' => 'Location'], ['name' => 'movement_type', 'label' => 'Movement', 'type' => 'badge'], ['name' => 'quantity_in', 'label' => 'In', 'type' => 'number'], ['name' => 'quantity_out', 'label' => 'Out', 'type' => 'number'], ['name' => 'balance_after', 'label' => 'Balance after', 'type' => 'number'], ['name' => 'reference_type', 'label' => 'Source']]],
        'stock-transfers' => ['title' => 'Stock transfers', 'eyebrow' => 'INVENTORY OPERATIONS', 'description' => 'Review stock movements between storage locations.', 'columns' => [['name' => 'transfer_number', 'label' => 'Transfer'], ['name' => 'fromLocation.name', 'label' => 'From'], ['name' => 'toLocation.name', 'label' => 'To'], ['name' => 'transfer_date', 'label' => 'Date', 'type' => 'date'], ['name' => 'status', 'label' => 'Status', 'type' => 'badge']]],
        'stock-transfer-items' => ['title' => 'Transfer lines', 'eyebrow' => 'INVENTORY OPERATIONS', 'description' => 'Review ingredients and quantities assigned to each transfer.', 'columns' => [['name' => 'stockTransfer.transfer_number', 'label' => 'Transfer'], ['name' => 'ingredient.name', 'label' => 'Ingredient'], ['name' => 'quantity', 'label' => 'Quantity', 'type' => 'number'], ['name' => 'unit.abbreviation', 'label' => 'Unit']]],
        'stock-adjustments' => ['title' => 'Stock adjustments', 'eyebrow' => 'INVENTORY OPERATIONS', 'description' => 'Review approved stock corrections and their reasons.', 'columns' => [['name' => 'adjustment_number', 'label' => 'Adjustment'], ['name' => 'stockLocation.name', 'label' => 'Location'], ['name' => 'adjustment_date', 'label' => 'Date', 'type' => 'date'], ['name' => 'reason', 'label' => 'Reason'], ['name' => 'status', 'label' => 'Status', 'type' => 'badge']]],
        'stock-adjustment-items' => ['title' => 'Adjustment lines', 'eyebrow' => 'INVENTORY OPERATIONS', 'description' => 'Compare previous and adjusted quantities.', 'columns' => [['name' => 'stockAdjustment.adjustment_number', 'label' => 'Adjustment'], ['name' => 'ingredient.name', 'label' => 'Ingredient'], ['name' => 'previous_quantity', 'label' => 'Previous', 'type' => 'number'], ['name' => 'adjusted_quantity', 'label' => 'Adjusted', 'type' => 'number'], ['name' => 'difference_quantity', 'label' => 'Difference', 'type' => 'number'], ['name' => 'unit_cost', 'label' => 'Unit cost', 'type' => 'money']]],
        'stock-counts' => ['title' => 'Stock counts', 'eyebrow' => 'INVENTORY OPERATIONS', 'description' => 'Track count sessions and approvals without changing stock while a count is in draft.', 'columns' => [['name' => 'count_number', 'label' => 'Count'], ['name' => 'stockLocation.name', 'label' => 'Location'], ['name' => 'count_date', 'label' => 'Count date', 'type' => 'date'], ['name' => 'status', 'label' => 'Status', 'type' => 'badge']]],
        'stock-count-items' => ['title' => 'Stock count lines', 'eyebrow' => 'INVENTORY OPERATIONS', 'description' => 'Compare expected system quantities with physical counts and variances.', 'columns' => [['name' => 'stockCount.count_number', 'label' => 'Count'], ['name' => 'ingredient.name', 'label' => 'Ingredient'], ['name' => 'system_quantity', 'label' => 'System', 'type' => 'number'], ['name' => 'counted_quantity', 'label' => 'Counted', 'type' => 'number'], ['name' => 'variance_quantity', 'label' => 'Variance', 'type' => 'number'], ['name' => 'unit_cost', 'label' => 'Unit cost', 'type' => 'money']]],
        'stock-wastes' => ['title' => 'Stock waste', 'eyebrow' => 'INVENTORY OPERATIONS', 'description' => 'Review waste records by location, date, reason, and posting status.', 'columns' => [['name' => 'waste_number', 'label' => 'Waste record'], ['name' => 'stockLocation.name', 'label' => 'Location'], ['name' => 'waste_date', 'label' => 'Date', 'type' => 'date'], ['name' => 'reason', 'label' => 'Reason'], ['name' => 'status', 'label' => 'Status', 'type' => 'badge']]],
        'stock-waste-items' => ['title' => 'Waste lines', 'eyebrow' => 'INVENTORY OPERATIONS', 'description' => 'Review wasted ingredient quantities and their recorded cost.', 'columns' => [['name' => 'stockWaste.waste_number', 'label' => 'Waste record'], ['name' => 'ingredient.name', 'label' => 'Ingredient'], ['name' => 'quantity', 'label' => 'Quantity', 'type' => 'number'], ['name' => 'unit.abbreviation', 'label' => 'Unit'], ['name' => 'unit_cost', 'label' => 'Unit cost', 'type' => 'money'], ['name' => 'total_cost', 'label' => 'Total cost', 'type' => 'money'], ['name' => 'notes', 'label' => 'Notes']]],
        'users' => ['title' => 'Users', 'eyebrow' => 'WORKSPACE', 'description' => 'Review user accounts associated with this workspace.', 'columns' => [['name' => 'name', 'label' => 'Name'], ['name' => 'email', 'label' => 'Email'], ['name' => 'email_verified_at', 'label' => 'Email verified', 'type' => 'datetime'], ['name' => 'created_at', 'label' => 'Created', 'type' => 'datetime']]],
    ];
    abort_unless(isset($schemas[$resource]), 404);
    $page = $schemas[$resource];
    $search = $search ?? request('search', '');
@endphp

@section('title', $page['title'].' | Stockroom')
@section('breadcrumb', $page['title'])
@section('eyebrow', $page['eyebrow'])
@section('heading', $page['title'])
@section('description', $page['description'])

@section('content')
    <x-table :rows="$records" :caption="$page['title']">
        <x-slot:toolbar>
            <x-search-filter
                :action="$searchUrl ?? request()->url()"
                search-placeholder="Search records"
                submit-label="Filter"
                :search-value="$search"
                :filters="$filters ?? []"
            />
        </x-slot:toolbar>

        <x-slot:head>
            <tr>
                @foreach ($page['columns'] as $column)
                    <th scope="col">{{ $column['label'] }}</th>
                @endforeach
            </tr>
        </x-slot:head>

        <x-slot:body>
            @foreach ($records as $record)
                <tr>
                    @foreach ($page['columns'] as $column)
                        @php($value = data_get($record, $column['name']))
                        <td>
                            @if (($column['type'] ?? '') === 'boolean')
                                <span class="badge {{ $value ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $value ? 'Yes' : 'No' }}</span>
                            @elseif (($column['type'] ?? '') === 'badge' && filled($value))
                                <span class="badge text-bg-light border text-capitalize">{{ str_replace('_', ' ', $value) }}</span>
                            @elseif (($column['type'] ?? '') === 'number' && is_numeric($value))
                                {{ number_format((float) $value, 3) }}
                            @elseif (($column['type'] ?? '') === 'money' && is_numeric($value))
                                {{ number_format((float) $value, 2) }}
                            @elseif (($column['type'] ?? '') === 'date' && $value)
                                {{ \Illuminate\Support\Carbon::parse($value)->format('M j, Y') }}
                            @elseif (($column['type'] ?? '') === 'datetime' && $value)
                                {{ \Illuminate\Support\Carbon::parse($value)->format('M j, Y H:i') }}
                            @else
                                {{ filled($value) ? $value : '—' }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </x-slot:body>

        <x-slot:empty>
            <div class="data-table-empty text-center">
                <h2 class="h5">{{ $emptyTitle ?? 'No records yet' }}</h2>
                <p class="text-secondary mb-0">{{ $emptyDescription ?? 'Records will appear here when they are available.' }}</p>
            </div>
        </x-slot:empty>
    </x-table>
@endsection
