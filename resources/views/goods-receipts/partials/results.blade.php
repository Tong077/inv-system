<x-table :rows="$receiptsList" caption="Goods receipts"
    :empty-title="$search !== '' || filled($status) ? 'No matching goods receipts' : 'No goods receipts yet'"
    :empty-description="$search !== '' || filled($status) ? 'Try changing your search or status filter.' : 'Create a receipt to record a delivery.'">
    <x-slot:toolbar>
        <div class="unit-toolbar">
            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="offcanvas" data-bs-target="#createGoodsReceiptDrawer" aria-label="Create goods receipt"><i class="bi bi-plus-lg" aria-hidden="true"></i></button>
            <x-search-filter :action="route('goods-receipts.index')" search-label="Search goods receipts" search-placeholder="Search receipt, PO, supplier, or notes" search-container-class="unit-search-field" class="unit-search-filter" data-live-search="true" data-live-target="#goods-receipt-results" data-live-delay="300" submit-label="Filter" :search-value="$search" :filters="[['name'=>'status','label'=>'Status','placeholder'=>'Show all','value'=>'all','resetValue'=>'all','showPlaceholderOption'=>false,'options'=>['all'=>'Show all','draft'=>'Draft','posted'=>'Posted'],'class'=>'unit-status-filter']]" />
        </div>
    </x-slot:toolbar>
    <x-slot:head><tr><th>Receipt</th><th>Supplier</th><th>Purchase order</th><th>Location</th><th>Received date</th><th>Status</th><th class="text-end">Lines</th><th class="text-end">Actions</th></tr></x-slot:head>
    <x-slot:body>
        @foreach ($receiptsList as $receipt)
            <tr>
                <td class="fw-semibold">{{ $receipt->receipt_number }}</td><td>{{ $receipt->supplier?->name ?? 'Deleted supplier' }}</td><td>{{ $receipt->purchaseOrder?->po_number ?? 'Standalone' }}</td><td>{{ $receipt->stockLocation?->name ?? 'Deleted location' }}</td><td>{{ $receipt->received_date?->format('M j, Y') }}</td>
                <td><span class="badge {{ $receipt->status === 'posted' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ ucfirst($receipt->status) }}</span></td><td class="text-end">{{ $receipt->items_count }}</td>
                <td class="text-end text-nowrap">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('goods-receipt-items.index', ['goods_receipt_id'=>$receipt->id]) }}" aria-label="Manage lines for {{ $receipt->receipt_number }}"><i class="bi bi-list-ul" aria-hidden="true"></i></a>
                    @if ($receipt->status === 'draft')
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="offcanvas" data-bs-target="#editGoodsReceiptDrawer-{{ $receipt->id }}" aria-label="Edit {{ $receipt->receipt_number }}"><i class="bi bi-pencil" aria-hidden="true"></i></button>
                        <form method="POST" action="{{ route('goods-receipts.post', $receipt) }}" class="d-inline" data-swal-confirm data-swal-title="Post {{ $receipt->receipt_number }}?" data-swal-text="Posting adds the received quantities to stock and cannot be undone." data-swal-confirm-text="Post receipt" data-swal-cancel-text="Cancel">@csrf<button class="btn btn-sm btn-outline-success" aria-label="Post {{ $receipt->receipt_number }}"><i class="bi bi-box-arrow-in-down" aria-hidden="true"></i></button></form>
                        <x-confirm-delete :action="route('goods-receipts.destroy',$receipt)" :title="'Delete receipt '.$receipt->receipt_number.'?'" text="This deletes the draft receipt and its lines." :button-aria-label="'Delete '.$receipt->receipt_number"><i class="bi bi-trash" aria-hidden="true"></i></x-confirm-delete>
                    @endif
                </td>
            </tr>
        @endforeach
    </x-slot:body>
</x-table>
<div data-live-edit-drawers>@include('goods-receipts.partials.edit-drawers')</div>
