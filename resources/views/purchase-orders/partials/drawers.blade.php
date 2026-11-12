<x-drawer id="createPurchaseOrderDrawer" title="Create purchase order draft" width="36rem">
    <form id="createPurchaseOrderForm" method="POST" action="{{ route('purchase-orders.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">PO number</label>
            <div class="form-control bg-light" aria-readonly="true">Generated automatically when saved (PO-YYYYMMDD-00001)</div>
        </div>
        <div class="mb-3">
            <label for="create-po-supplier" class="form-label">Supplier <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
            <select id="create-po-supplier" name="supplier_id" required class="form-select @error('supplier_id') is-invalid @enderror">
                <option value="">Select a supplier</option>
                @foreach ($suppliers->where('is_active', true) as $supplier)
                    <option value="{{ $supplier->id }}" @selected((string) old('supplier_id') === (string) $supplier->id)>{{ $supplier->code }} — {{ $supplier->name }}</option>
                @endforeach
            </select>
            @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label for="create-po-order-date" class="form-label">Order date <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <input id="create-po-order-date" name="order_date" type="date" required value="{{ old('order_date') }}"
                    class="form-control @error('order_date') is-invalid @enderror">
                @error('order_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
                <label for="create-po-expected-date" class="form-label">Expected date <span class="text-secondary">(optional)</span></label>
                <input id="create-po-expected-date" name="expected_date" type="date" value="{{ old('expected_date') }}"
                    class="form-control @error('expected_date') is-invalid @enderror">
                @error('expected_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label for="create-po-tax" class="form-label">Tax amount <span class="text-secondary">(optional)</span></label>
                <input id="create-po-tax" name="tax_amount" type="number" min="0" step="0.01" value="{{ old('tax_amount') }}"
                    class="form-control @error('tax_amount') is-invalid @enderror">
                @error('tax_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
                <label for="create-po-discount" class="form-label">Discount amount <span class="text-secondary">(optional)</span></label>
                <input id="create-po-discount" name="discount_amount" type="number" min="0" step="0.01" value="{{ old('discount_amount') }}"
                    class="form-control @error('discount_amount') is-invalid @enderror">
                @error('discount_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="mb-3">
            <label for="create-po-notes" class="form-label">Notes <span class="text-secondary">(optional)</span></label>
            <textarea id="create-po-notes" name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="alert alert-light border mb-0" role="note">This saves a draft only. Add lines and mark the order as ordered; stock changes only when goods are received.</div>
    </form>
    <x-slot:footer>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="submit" form="createPurchaseOrderForm" class="btn btn-success">Save draft</button>
        </div>
    </x-slot:footer>
</x-drawer>
