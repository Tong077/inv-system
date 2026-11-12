@foreach ($receiptsList->where('status', 'draft') as $receipt)
    @php($isOpenReceipt = (string) session('openGoodsReceiptDrawer') === (string) $receipt->id)

    <x-drawer
        id="editGoodsReceiptDrawer-{{ $receipt->id }}"
        title="Edit goods receipt draft"
        width="36rem"
    >
        <form
            id="editGoodsReceiptForm-{{ $receipt->id }}"
            method="POST"
            action="{{ route('goods-receipts.update', $receipt) }}"
        >
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="gr-number-{{ $receipt->id }}" class="form-label">
                    Receipt number
                </label>
                <input
                    id="gr-number-{{ $receipt->id }}"
                    value="{{ $receipt->receipt_number }}"
                    readonly
                    class="form-control"
                >
            </div>

            <div class="mb-3">
                <label for="gr-order-{{ $receipt->id }}" class="form-label">
                    Purchase order <span class="text-secondary">(optional)</span>
                </label>
                <select
                    id="gr-order-{{ $receipt->id }}"
                    name="purchase_order_id"
                    @class([
                        'form-select',
                        'is-invalid' => $isOpenReceipt && $errors->has('purchase_order_id'),
                    ])
                >
                    <option value="">Standalone receipt</option>
                    @foreach ($purchaseOrders as $order)
                        <option
                            value="{{ $order->id }}"
                            @selected((string) old('purchase_order_id', $receipt->purchase_order_id) === (string) $order->id)
                        >
                            {{ $order->po_number }} — {{ $order->supplier?->name }}
                        </option>
                    @endforeach
                    @if ($receipt->purchaseOrder && ! $purchaseOrders->contains('id', $receipt->purchase_order_id))
                        <option value="{{ $receipt->purchase_order_id }}" selected>
                            {{ $receipt->purchaseOrder->po_number }} — {{ $receipt->purchaseOrder->supplier?->name }}
                        </option>
                    @endif
                </select>
                @if ($isOpenReceipt && $errors->has('purchase_order_id'))
                    <div class="invalid-feedback">{{ $errors->first('purchase_order_id') }}</div>
                @endif
            </div>

            <div class="mb-3">
                <label for="gr-supplier-{{ $receipt->id }}" class="form-label">
                    Supplier <span class="text-danger">*</span>
                </label>
                <select
                    id="gr-supplier-{{ $receipt->id }}"
                    name="supplier_id"
                    required
                    @class([
                        'form-select',
                        'is-invalid' => $isOpenReceipt && $errors->has('supplier_id'),
                    ])
                >
                    <option value="">Select supplier</option>
                    @foreach ($suppliers as $supplier)
                        <option
                            value="{{ $supplier->id }}"
                            @selected((string) old('supplier_id', $receipt->supplier_id) === (string) $supplier->id)
                        >
                            {{ $supplier->code }} — {{ $supplier->name }}{{ $supplier->is_active ? '' : ' (inactive)' }}
                        </option>
                    @endforeach
                </select>
                @if ($isOpenReceipt && $errors->has('supplier_id'))
                    <div class="invalid-feedback">{{ $errors->first('supplier_id') }}</div>
                @endif
            </div>

            <div class="mb-3">
                <label for="gr-location-{{ $receipt->id }}" class="form-label">
                    Stock location <span class="text-danger">*</span>
                </label>
                <select
                    id="gr-location-{{ $receipt->id }}"
                    name="stock_location_id"
                    required
                    @class([
                        'form-select',
                        'is-invalid' => $isOpenReceipt && $errors->has('stock_location_id'),
                    ])
                >
                    <option value="">Select location</option>
                    @foreach ($locations as $location)
                        <option
                            value="{{ $location->id }}"
                            @selected((string) old('stock_location_id', $receipt->stock_location_id) === (string) $location->id)
                        >
                            {{ $location->code }} — {{ $location->name }}{{ $location->is_active ? '' : ' (inactive)' }}
                        </option>
                    @endforeach
                </select>
                @if ($isOpenReceipt && $errors->has('stock_location_id'))
                    <div class="invalid-feedback">{{ $errors->first('stock_location_id') }}</div>
                @endif
            </div>

            <div class="mb-3">
                <label for="gr-date-{{ $receipt->id }}" class="form-label">
                    Received date <span class="text-danger">*</span>
                </label>
                <input
                    id="gr-date-{{ $receipt->id }}"
                    name="received_date"
                    type="date"
                    required
                    value="{{ old('received_date', $receipt->received_date?->format('Y-m-d')) }}"
                    @class([
                        'form-control',
                        'is-invalid' => $isOpenReceipt && $errors->has('received_date'),
                    ])
                >
                @if ($isOpenReceipt && $errors->has('received_date'))
                    <div class="invalid-feedback">{{ $errors->first('received_date') }}</div>
                @endif
            </div>

            <div class="mb-3">
                <label for="gr-notes-{{ $receipt->id }}" class="form-label">
                    Notes <span class="text-secondary">(optional)</span>
                </label>
                <textarea
                    id="gr-notes-{{ $receipt->id }}"
                    name="notes"
                    rows="3"
                    @class([
                        'form-control',
                        'is-invalid' => $isOpenReceipt && $errors->has('notes'),
                    ])
                >{{ old('notes', $receipt->notes) }}</textarea>
                @if ($isOpenReceipt && $errors->has('notes'))
                    <div class="invalid-feedback">{{ $errors->first('notes') }}</div>
                @endif
            </div>

            @if ($receipt->items_count)
                <div class="alert alert-light border mb-0">
                    Supplier and purchase order are locked while this receipt has lines.
                </div>
            @endif
        </form>

        <x-slot:footer>
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button
                    type="submit"
                    form="editGoodsReceiptForm-{{ $receipt->id }}"
                    class="btn btn-primary"
                >
                    Save changes
                </button>
            </div>
        </x-slot:footer>
    </x-drawer>
@endforeach
