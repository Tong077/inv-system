@foreach ($orders->where('status', 'draft') as $order)
    <x-drawer id="editPurchaseOrderDrawer-{{ $order->id }}" title="Edit purchase order draft" width="36rem">
        <form id="editPurchaseOrderForm-{{ $order->id }}" method="POST" action="{{ route('purchase-orders.update', $order) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="edit-po-number-{{ $order->id }}" class="form-label">PO number</label>
                <input id="edit-po-number-{{ $order->id }}" type="text" value="{{ $order->po_number }}" readonly
                    class="form-control">
            </div>
            <div class="mb-3">
                <label for="edit-po-supplier-{{ $order->id }}" class="form-label">Supplier <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <select id="edit-po-supplier-{{ $order->id }}" name="supplier_id" required
                    @class(['form-select', 'is-invalid' => session('openPurchaseOrderDrawer') == $order->id && $errors->has('supplier_id')])>
                    <option value="">Select a supplier</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected((string) old('supplier_id', $order->supplier_id) === (string) $supplier->id)>{{ $supplier->code }} — {{ $supplier->name }}{{ $supplier->is_active ? '' : ' (inactive)' }}</option>
                    @endforeach
                </select>
                @if (session('openPurchaseOrderDrawer') == $order->id) @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label for="edit-po-order-date-{{ $order->id }}" class="form-label">Order date <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                    <input id="edit-po-order-date-{{ $order->id }}" name="order_date" type="date" required value="{{ old('order_date', $order->order_date?->format('Y-m-d')) }}"
                        @class(['form-control', 'is-invalid' => session('openPurchaseOrderDrawer') == $order->id && $errors->has('order_date')])>
                    @if (session('openPurchaseOrderDrawer') == $order->id) @error('order_date')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
                <div class="col-sm-6">
                    <label for="edit-po-expected-date-{{ $order->id }}" class="form-label">Expected date <span class="text-secondary">(optional)</span></label>
                    <input id="edit-po-expected-date-{{ $order->id }}" name="expected_date" type="date" value="{{ old('expected_date', $order->expected_date?->format('Y-m-d')) }}"
                        @class(['form-control', 'is-invalid' => session('openPurchaseOrderDrawer') == $order->id && $errors->has('expected_date')])>
                    @if (session('openPurchaseOrderDrawer') == $order->id) @error('expected_date')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label for="edit-po-tax-{{ $order->id }}" class="form-label">Tax amount <span class="text-secondary">(optional)</span></label>
                    <input id="edit-po-tax-{{ $order->id }}" name="tax_amount" type="number" min="0" step="0.01" value="{{ old('tax_amount', $order->tax_amount) }}"
                        @class(['form-control', 'is-invalid' => session('openPurchaseOrderDrawer') == $order->id && $errors->has('tax_amount')])>
                    @if (session('openPurchaseOrderDrawer') == $order->id) @error('tax_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
                <div class="col-sm-6">
                    <label for="edit-po-discount-{{ $order->id }}" class="form-label">Discount amount <span class="text-secondary">(optional)</span></label>
                    <input id="edit-po-discount-{{ $order->id }}" name="discount_amount" type="number" min="0" step="0.01" value="{{ old('discount_amount', $order->discount_amount) }}"
                        @class(['form-control', 'is-invalid' => session('openPurchaseOrderDrawer') == $order->id && $errors->has('discount_amount')])>
                    @if (session('openPurchaseOrderDrawer') == $order->id) @error('discount_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
            </div>
            <div class="mb-3">
                <label for="edit-po-status-{{ $order->id }}" class="form-label">Status <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <select id="edit-po-status-{{ $order->id }}" name="status" required
                    @class(['form-select', 'is-invalid' => session('openPurchaseOrderDrawer') == $order->id && $errors->has('status')])>
                    <option value="draft" @selected(old('status', $order->status) === 'draft')>Draft</option>
                    <option value="ordered" @selected(old('status', $order->status) === 'ordered')>Ordered</option>
                    <option value="cancelled" @selected(old('status', $order->status) === 'cancelled')>Cancelled</option>
                </select>
                @if (session('openPurchaseOrderDrawer') == $order->id) @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                <div class="form-text">An order must have at least one line before it can be marked ordered.</div>
            </div>
            <div class="mb-3">
                <label for="edit-po-notes-{{ $order->id }}" class="form-label">Notes <span class="text-secondary">(optional)</span></label>
                <textarea id="edit-po-notes-{{ $order->id }}" name="notes" rows="3"
                    @class(['form-control', 'is-invalid' => session('openPurchaseOrderDrawer') == $order->id && $errors->has('notes')])>{{ old('notes', $order->notes) }}</textarea>
                @if (session('openPurchaseOrderDrawer') == $order->id) @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
        </form>
        <x-slot:footer>
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" form="editPurchaseOrderForm-{{ $order->id }}" class="btn btn-primary">Save changes</button>
            </div>
        </x-slot:footer>
    </x-drawer>
@endforeach
