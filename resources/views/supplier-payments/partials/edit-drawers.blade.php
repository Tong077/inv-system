@foreach ($payments as $payment)
    <x-drawer
        id="editSupplierPaymentDrawer-{{ $payment->id }}"
        title="Edit supplier payment"
        width="36rem"
    >
        <form
            id="editSupplierPaymentForm-{{ $payment->id }}"
            method="POST"
            action="{{ route('supplier-payments.update', $payment) }}"
        >
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="pay-supplier-{{ $payment->id }}" class="form-label">
                    Supplier <span class="text-danger">*</span>
                </label>
                <select id="pay-supplier-{{ $payment->id }}" name="supplier_id" required class="form-select">
                    @foreach ($suppliers as $supplier)
                        <option
                            value="{{ $supplier->id }}"
                            @selected((string) old('supplier_id', $payment->supplier_id) === (string) $supplier->id)
                        >
                            {{ $supplier->code }} — {{ $supplier->name }}{{ $supplier->is_active ? '' : ' (inactive)' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="pay-order-{{ $payment->id }}" class="form-label">Purchase order</label>
                <select id="pay-order-{{ $payment->id }}" name="purchase_order_id" class="form-select">
                    <option value="">No purchase order</option>
                    @foreach ($orders as $order)
                        <option
                            value="{{ $order->id }}"
                            @selected((string) old('purchase_order_id', $payment->purchase_order_id) === (string) $order->id)
                        >
                            {{ $order->po_number }} — {{ $order->supplier?->name }}
                        </option>
                    @endforeach
                    @if ($payment->purchaseOrder && ! $orders->contains('id', $payment->purchase_order_id))
                        <option value="{{ $payment->purchase_order_id }}" selected>
                            {{ $payment->purchaseOrder->po_number }}
                        </option>
                    @endif
                </select>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label for="pay-date-{{ $payment->id }}" class="form-label">
                        Payment date <span class="text-danger">*</span>
                    </label>
                    <input
                        id="pay-date-{{ $payment->id }}"
                        name="payment_date"
                        type="date"
                        required
                        value="{{ old('payment_date', $payment->payment_date?->format('Y-m-d')) }}"
                        class="form-control"
                    >
                </div>
                <div class="col-sm-6">
                    <label for="pay-amount-{{ $payment->id }}" class="form-label">
                        Amount <span class="text-danger">*</span>
                    </label>
                    <input
                        id="pay-amount-{{ $payment->id }}"
                        name="amount"
                        type="number"
                        min="0.01"
                        step="0.01"
                        required
                        value="{{ old('amount', $payment->amount) }}"
                        class="form-control"
                    >
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label for="pay-method-{{ $payment->id }}" class="form-label">
                        Method <span class="text-danger">*</span>
                    </label>
                    <select id="pay-method-{{ $payment->id }}" name="payment_method" required class="form-select">
                        @foreach (['cash' => 'Cash', 'bank_transfer' => 'Bank transfer', 'card' => 'Card', 'cheque' => 'Cheque', 'other' => 'Other'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('payment_method', $payment->payment_method) === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6">
                    <label for="pay-status-{{ $payment->id }}" class="form-label">
                        Status <span class="text-danger">*</span>
                    </label>
                    <select id="pay-status-{{ $payment->id }}" name="status" required class="form-select">
                        @foreach (['pending' => 'Pending', 'paid' => 'Paid', 'cancelled' => 'Cancelled'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $payment->status) === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="pay-ref-{{ $payment->id }}" class="form-label">Reference number</label>
                <input
                    id="pay-ref-{{ $payment->id }}"
                    name="reference_number"
                    maxlength="100"
                    value="{{ old('reference_number', $payment->reference_number) }}"
                    class="form-control"
                >
            </div>
        </form>

        <x-slot:footer>
            <div class="d-flex justify-content-end gap-2">
                <button class="btn btn-light" type="button" data-bs-dismiss="offcanvas">Cancel</button>
                <button
                    class="btn btn-primary"
                    type="submit"
                    form="editSupplierPaymentForm-{{ $payment->id }}"
                >
                    Save changes
                </button>
            </div>
        </x-slot:footer>
    </x-drawer>
@endforeach
