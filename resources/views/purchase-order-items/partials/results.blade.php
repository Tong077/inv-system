<x-table :rows="$purchaseOrderItems" caption="Purchase order lines"
    :empty-title="$search !== '' || $purchaseOrderId !== null ? 'No matching purchase order lines' : 'No purchase order lines yet'"
    :empty-description="$search !== '' || $purchaseOrderId !== null ? 'Try changing the search or purchase order filter.' : 'Add lines to a draft purchase order.'">
    <x-slot:toolbar>
        <div class="unit-toolbar">
            <button type="button" class="btn btn-sm btn-success text-nowrap" data-bs-toggle="offcanvas"
                data-bs-target="#createPurchaseOrderItemDrawer" aria-label="Add purchase order line">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
            </button>
            <x-search-filter :action="route('purchase-order-items.index')"
                search-label="Search purchase order lines"
                search-placeholder="Search by PO number, supplier, ingredient, or code"
                search-container-class="unit-search-field"
                class="unit-search-filter"
                data-live-search="true"
                data-live-target="#purchase-order-item-results"
                data-live-delay="300"
                submit-label="Filter"
                :search-value="$search"
                :filters="[
                    [
                        'name' => 'purchase_order_id',
                        'label' => 'Purchase order',
                        'placeholder' => 'All purchase orders',
                        'value' => '',
                        'options' => $purchaseOrders->mapWithKeys(fn ($order) => [
                            $order->id => $order->po_number . ' — ' . ($order->supplier?->name ?? 'Supplier'),
                        ])->all(),
                        'class' => 'unit-status-filter',
                    ],
                ]" />
        </div>
    </x-slot:toolbar>
    <x-slot:head>
        <tr>
            <th scope="col">Purchase order</th>
            <th scope="col">Ingredient</th>
            <th scope="col" class="text-end">Quantity</th>
            <th scope="col" class="text-end">Unit price</th>
            <th scope="col" class="text-end">Line total</th>
            <th scope="col" class="text-end">Actions</th>
        </tr>
    </x-slot:head>
    <x-slot:body>
        @foreach ($purchaseOrderItems as $item)
            <tr>
                <td>
                    <span class="d-block fw-semibold">{{ $item->purchaseOrder?->po_number ?? 'Deleted order' }}</span>
                    <span class="small text-secondary">{{ $item->purchaseOrder?->supplier?->name }}</span>
                </td>
                <td>
                    <span class="d-block">{{ $item->ingredient?->name ?? 'Deleted ingredient' }}</span>
                    <span class="small text-secondary">{{ $item->ingredient?->code }}</span>
                </td>
                <td class="text-end">{{ number_format((float) $item->quantity, 3) }} {{ $item->unit?->abbreviation }}</td>
                <td class="text-end">{{ number_format((float) $item->unit_price, 2) }}</td>
                <td class="text-end">{{ number_format((float) $item->total_price, 2) }}</td>
                <td class="text-end text-nowrap">
                    @if ($item->purchaseOrder?->status === 'draft')
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="offcanvas"
                            data-bs-target="#editPurchaseOrderItemDrawer-{{ $item->id }}" aria-label="Edit {{ $item->ingredient?->name }} line">
                            <i class="bi bi-pencil" aria-hidden="true"></i>
                        </button>
                        <x-confirm-delete :action="route('purchase-order-items.destroy', $item)"
                            :title="'Delete ' . ($item->ingredient?->name ?? 'this line') . ' from the order?'"
                            text="A line with a receipt cannot be deleted."
                            :button-aria-label="'Delete line for ' . ($item->ingredient?->name ?? 'ingredient')"
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
    @include('purchase-order-items.partials.edit-drawers')
</div>
