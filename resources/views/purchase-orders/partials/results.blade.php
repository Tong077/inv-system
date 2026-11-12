<x-table :rows="$orders" caption="Purchase orders"
    :empty-title="$search !== '' || filled($status) ? 'No matching purchase orders' : 'No purchase orders yet'"
    :empty-description="$search !== '' || filled($status) ? 'Try changing your search or status filter.' : 'Create a draft purchase order before receiving goods.'">
    <x-slot:toolbar>
        <div class="unit-toolbar">
            <button type="button" class="btn btn-sm btn-success text-nowrap" data-bs-toggle="offcanvas"
                data-bs-target="#createPurchaseOrderDrawer" aria-label="Create purchase order">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
            </button>
            <x-search-filter :action="route('purchase-orders.index')"
                search-label="Search purchase orders"
                search-placeholder="Search by PO number, supplier, or notes"
                search-container-class="unit-search-field"
                class="unit-search-filter"
                data-live-search="true"
                data-live-target="#purchase-order-results"
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
                        'options' => [
                            'all' => 'Show all', 'draft' => 'Draft', 'ordered' => 'Ordered',
                            'partially_received' => 'Partially received', 'received' => 'Received', 'cancelled' => 'Cancelled',
                        ],
                        'class' => 'unit-status-filter',
                    ],
                ]" />
        </div>
    </x-slot:toolbar>
    <x-slot:head>
        <tr>
            <th scope="col">PO number</th>
            <th scope="col">Supplier</th>
            <th scope="col">Order date</th>
            <th scope="col">Expected</th>
            <th scope="col" class="text-end">Lines</th>
            <th scope="col" class="text-end">Total</th>
            <th scope="col">Status</th>
            <th scope="col" class="text-end">Actions</th>
        </tr>
    </x-slot:head>
    <x-slot:body>
        @foreach ($orders as $order)
            <tr>
                <td class="fw-semibold">{{ $order->po_number }}</td>
                <td>{{ $order->supplier?->name ?? 'Deleted supplier' }}</td>
                <td>{{ $order->order_date?->format('M j, Y') }}</td>
                <td>{{ $order->expected_date?->format('M j, Y') ?? '—' }}</td>
                <td class="text-end">{{ $order->items_count }}</td>
                <td class="text-end">{{ number_format((float) $order->total_amount, 2) }}</td>
                <td><span class="badge {{ $order->status === 'received' ? 'text-bg-success' : ($order->status === 'cancelled' ? 'text-bg-secondary' : 'text-bg-primary') }} text-capitalize">{{ str_replace('_', ' ', $order->status) }}</span></td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('purchase-order-items.index', ['purchase_order_id' => $order->id]) }}"
                        class="btn btn-sm btn-outline-secondary" aria-label="Manage lines for {{ $order->po_number }}">
                        <i class="bi bi-list-ul" aria-hidden="true"></i>
                    </a>
                    @if ($order->status === 'draft')
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="offcanvas"
                            data-bs-target="#editPurchaseOrderDrawer-{{ $order->id }}" aria-label="Edit {{ $order->po_number }}">
                            <i class="bi bi-pencil" aria-hidden="true"></i>
                        </button>
                        <x-confirm-delete :action="route('purchase-orders.destroy', $order)"
                            :title="'Delete purchase order ' . $order->po_number . '?'"
                            text="Only draft purchase orders without receipts or payments can be deleted."
                            :button-aria-label="'Delete ' . $order->po_number"
                            button-class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash" aria-hidden="true"></i>
                        </x-confirm-delete>
                    @endif
                </td>
            </tr>
        @endforeach
    </x-slot:body>
</x-table>
<div data-live-edit-drawers>
    @include('purchase-orders.partials.edit-drawers')
</div>
