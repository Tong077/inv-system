@foreach ($purchaseOrderItems as $item)
    @if ($item->purchaseOrder?->status === 'draft')
        <x-drawer id="editPurchaseOrderItemDrawer-{{ $item->id }}" title="Edit purchase order line" width="34rem">
            <form id="editPurchaseOrderItemForm-{{ $item->id }}" method="POST" action="{{ route('purchase-order-items.update', $item) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="edit-po-item-order-{{ $item->id }}" class="form-label">Draft purchase order <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                    <select id="edit-po-item-order-{{ $item->id }}" name="purchase_order_id" required
                        @class(['form-select', 'is-invalid' => session('openPurchaseOrderItemDrawer') == $item->id && $errors->has('purchase_order_id')])>
                        <option value="">Select a draft order</option>
                        @foreach ($purchaseOrders->where('status', 'draft') as $order)
                            <option value="{{ $order->id }}" @selected((string) old('purchase_order_id', $item->purchase_order_id) === (string) $order->id)>
                                {{ $order->po_number }} — {{ $order->supplier?->name }}
                            </option>
                        @endforeach
                    </select>
                    @if (session('openPurchaseOrderItemDrawer') == $item->id) @error('purchase_order_id')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
                <div class="mb-3">
                    <label for="edit-po-item-ingredient-{{ $item->id }}" class="form-label">Ingredient <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                    <select id="edit-po-item-ingredient-{{ $item->id }}" name="ingredient_id" required
                        @class(['form-select', 'is-invalid' => session('openPurchaseOrderItemDrawer') == $item->id && $errors->has('ingredient_id')])>
                        <option value="">Select an ingredient</option>
                        @foreach ($ingredients as $ingredient)
                            <option value="{{ $ingredient->id }}" @selected((string) old('ingredient_id', $item->ingredient_id) === (string) $ingredient->id)>
                                {{ $ingredient->code }} — {{ $ingredient->name }} (base: {{ $ingredient->baseUnit?->abbreviation }})
                            </option>
                        @endforeach
                    </select>
                    @if (session('openPurchaseOrderItemDrawer') == $item->id) @error('ingredient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label for="edit-po-item-quantity-{{ $item->id }}" class="form-label">Quantity <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                        <input id="edit-po-item-quantity-{{ $item->id }}" name="quantity" type="number" min="0.001" step="0.001" required value="{{ old('quantity', $item->quantity) }}"
                            @class(['form-control', 'is-invalid' => session('openPurchaseOrderItemDrawer') == $item->id && $errors->has('quantity')])>
                        @if (session('openPurchaseOrderItemDrawer') == $item->id) @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                    </div>
                    <div class="col-sm-6">
                        <label for="edit-po-item-unit-{{ $item->id }}" class="form-label">Unit <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                        <select id="edit-po-item-unit-{{ $item->id }}" name="unit_id" required
                            @class(['form-select', 'is-invalid' => session('openPurchaseOrderItemDrawer') == $item->id && $errors->has('unit_id')])>
                            <option value="">Select a unit</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}" @selected((string) old('unit_id', $item->unit_id) === (string) $unit->id)>
                                    {{ $unit->name }} ({{ $unit->abbreviation }}, {{ $unit->dimension }}){{ $unit->is_active ? '' : ' (inactive)' }}
                                </option>
                            @endforeach
                        </select>
                        @if (session('openPurchaseOrderItemDrawer') == $item->id) @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                    </div>
                </div>
                <div class="mb-3">
                    <label for="edit-po-item-price-{{ $item->id }}" class="form-label">Unit price <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                    <input id="edit-po-item-price-{{ $item->id }}" name="unit_price" type="number" min="0" step="0.01" required value="{{ old('unit_price', $item->unit_price) }}"
                        @class(['form-control', 'is-invalid' => session('openPurchaseOrderItemDrawer') == $item->id && $errors->has('unit_price')])>
                    @if (session('openPurchaseOrderItemDrawer') == $item->id) @error('unit_price')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
                <div class="form-text">Line total is quantity × unit price.</div>
            </form>
            <x-slot:footer>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
                    <button type="submit" form="editPurchaseOrderItemForm-{{ $item->id }}" class="btn btn-primary">Save changes</button>
                </div>
            </x-slot:footer>
        </x-drawer>
    @endif
@endforeach
