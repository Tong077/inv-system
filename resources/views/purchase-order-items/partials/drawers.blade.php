<x-drawer id="createPurchaseOrderItemDrawer" title="Add purchase order line" width="34rem">
    <form id="createPurchaseOrderItemForm" method="POST" action="{{ route('purchase-order-items.store') }}">
        @csrf
        <div class="mb-3">
            <label for="create-po-item-order" class="form-label">Draft purchase order <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
            <select id="create-po-item-order" name="purchase_order_id" required class="form-select @error('purchase_order_id') is-invalid @enderror">
                <option value="">Select a draft order</option>
                @foreach ($purchaseOrders->where('status', 'draft') as $order)
                    <option value="{{ $order->id }}" @selected((string) old('purchase_order_id', $purchaseOrderId) === (string) $order->id)>
                        {{ $order->po_number }} — {{ $order->supplier?->name }}
                    </option>
                @endforeach
            </select>
            @error('purchase_order_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="create-po-item-ingredient" class="form-label">Ingredient <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
            <select id="create-po-item-ingredient" name="ingredient_id" required class="form-select @error('ingredient_id') is-invalid @enderror">
                <option value="">Select an ingredient</option>
                @foreach ($ingredients as $ingredient)
                    <option value="{{ $ingredient->id }}" @selected((string) old('ingredient_id') === (string) $ingredient->id)>
                        {{ $ingredient->code }} — {{ $ingredient->name }} (base: {{ $ingredient->baseUnit?->abbreviation }})
                    </option>
                @endforeach
            </select>
            @error('ingredient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label for="create-po-item-quantity" class="form-label">Quantity <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <input id="create-po-item-quantity" name="quantity" type="number" min="0.001" step="0.001" required value="{{ old('quantity') }}"
                    class="form-control @error('quantity') is-invalid @enderror">
                @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
                <label for="create-po-item-unit" class="form-label">Unit <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <select id="create-po-item-unit" name="unit_id" required class="form-select @error('unit_id') is-invalid @enderror">
                    <option value="">Select a unit</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" @selected((string) old('unit_id') === (string) $unit->id)>
                            {{ $unit->name }} ({{ $unit->abbreviation }}, {{ $unit->dimension }}){{ $unit->is_active ? '' : ' (inactive)' }}
                        </option>
                    @endforeach
                </select>
                @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="mb-3">
            <label for="create-po-item-price" class="form-label">Unit price <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
            <input id="create-po-item-price" name="unit_price" type="number" min="0" step="0.01" required value="{{ old('unit_price') }}"
                class="form-control @error('unit_price') is-invalid @enderror">
            @error('unit_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-text">Line total is quantity × unit price. The same unit dimension as the ingredient base unit is required.</div>
    </form>
    <x-slot:footer>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="submit" form="createPurchaseOrderItemForm" class="btn btn-success">Add line</button>
        </div>
    </x-slot:footer>
</x-drawer>
