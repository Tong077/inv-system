@foreach ($suppliers as $supplier)
    <x-drawer id="editSupplierDrawer-{{ $supplier->id }}" title="Edit supplier" width="36rem">
        <form id="editSupplierForm-{{ $supplier->id }}" method="POST" action="{{ route('suppliers.update', $supplier) }}">
            @csrf
            @method('PUT')
            <div class="row g-3 mb-3">
                <div class="col-sm-5">
                    <label for="edit-supplier-code-{{ $supplier->id }}" class="form-label">Supplier code <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                    <input id="edit-supplier-code-{{ $supplier->id }}" name="code" type="text" maxlength="50" required value="{{ old('code', $supplier->code) }}"
                        @class(['form-control', 'is-invalid' => session('openSupplierDrawer') == $supplier->id && $errors->has('code')])>
                    @if (session('openSupplierDrawer') == $supplier->id) @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
                <div class="col-sm-7">
                    <label for="edit-supplier-name-{{ $supplier->id }}" class="form-label">Supplier name <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                    <input id="edit-supplier-name-{{ $supplier->id }}" name="name" type="text" maxlength="200" required value="{{ old('name', $supplier->name) }}"
                        @class(['form-control', 'is-invalid' => session('openSupplierDrawer') == $supplier->id && $errors->has('name')])>
                    @if (session('openSupplierDrawer') == $supplier->id) @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label for="edit-supplier-phone-{{ $supplier->id }}" class="form-label">Phone <span class="text-secondary">(optional)</span></label>
                    <input id="edit-supplier-phone-{{ $supplier->id }}" name="phone" type="tel" maxlength="50" value="{{ old('phone', $supplier->phone) }}"
                        @class(['form-control', 'is-invalid' => session('openSupplierDrawer') == $supplier->id && $errors->has('phone')])>
                    @if (session('openSupplierDrawer') == $supplier->id) @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
                <div class="col-sm-6">
                    <label for="edit-supplier-email-{{ $supplier->id }}" class="form-label">Email <span class="text-secondary">(optional)</span></label>
                    <input id="edit-supplier-email-{{ $supplier->id }}" name="email" type="email" maxlength="150" value="{{ old('email', $supplier->email) }}"
                        @class(['form-control', 'is-invalid' => session('openSupplierDrawer') == $supplier->id && $errors->has('email')])>
                    @if (session('openSupplierDrawer') == $supplier->id) @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
            </div>
            <div class="mb-3">
                <label for="edit-supplier-address-{{ $supplier->id }}" class="form-label">Address <span class="text-secondary">(optional)</span></label>
                <textarea id="edit-supplier-address-{{ $supplier->id }}" name="address" rows="2"
                    @class(['form-control', 'is-invalid' => session('openSupplierDrawer') == $supplier->id && $errors->has('address')])>{{ old('address', $supplier->address) }}</textarea>
                @if (session('openSupplierDrawer') == $supplier->id) @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label for="edit-supplier-tax-{{ $supplier->id }}" class="form-label">Tax number <span class="text-secondary">(optional)</span></label>
                    <input id="edit-supplier-tax-{{ $supplier->id }}" name="tax_number" type="text" maxlength="100" value="{{ old('tax_number', $supplier->tax_number) }}"
                        @class(['form-control', 'is-invalid' => session('openSupplierDrawer') == $supplier->id && $errors->has('tax_number')])>
                    @if (session('openSupplierDrawer') == $supplier->id) @error('tax_number')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
                <div class="col-sm-6">
                    <label for="edit-supplier-terms-{{ $supplier->id }}" class="form-label">Payment terms <span class="text-secondary">(optional)</span></label>
                    <input id="edit-supplier-terms-{{ $supplier->id }}" name="payment_terms" type="text" maxlength="100" value="{{ old('payment_terms', $supplier->payment_terms) }}"
                        @class(['form-control', 'is-invalid' => session('openSupplierDrawer') == $supplier->id && $errors->has('payment_terms')])>
                    @if (session('openSupplierDrawer') == $supplier->id) @error('payment_terms')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
            </div>
            <input type="hidden" name="is_active" value="0">
            <div class="form-check">
                <input id="edit-supplier-active-{{ $supplier->id }}" name="is_active" type="checkbox" value="1" class="form-check-input"
                    @checked(old('is_active', $supplier->is_active ? '1' : '0') === '1' || old('is_active') === 1)>
                <label for="edit-supplier-active-{{ $supplier->id }}" class="form-check-label">Active</label>
            </div>
        </form>
        <x-slot:footer>
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" form="editSupplierForm-{{ $supplier->id }}" class="btn btn-primary">Save changes</button>
            </div>
        </x-slot:footer>
    </x-drawer>
@endforeach
