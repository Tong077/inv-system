<x-drawer id="createSupplierDrawer" title="Add supplier" width="36rem">
    <form id="createSupplierForm" method="POST" action="{{ route('suppliers.store') }}">
        @csrf
        <div class="row g-3 mb-3">
            <div class="col-sm-5">
                <label for="create-supplier-code" class="form-label">Supplier code <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <input id="create-supplier-code" name="code" type="text" maxlength="50" required value="{{ old('code') }}"
                    class="form-control @error('code') is-invalid @enderror">
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-7">
                <label for="create-supplier-name" class="form-label">Supplier name <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <input id="create-supplier-name" name="name" type="text" maxlength="200" required value="{{ old('name') }}"
                    class="form-control @error('name') is-invalid @enderror">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label for="create-supplier-phone" class="form-label">Phone <span class="text-secondary">(optional)</span></label>
                <input id="create-supplier-phone" name="phone" type="tel" maxlength="50" value="{{ old('phone') }}"
                    class="form-control @error('phone') is-invalid @enderror">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
                <label for="create-supplier-email" class="form-label">Email <span class="text-secondary">(optional)</span></label>
                <input id="create-supplier-email" name="email" type="email" maxlength="150" value="{{ old('email') }}"
                    class="form-control @error('email') is-invalid @enderror">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="mb-3">
            <label for="create-supplier-address" class="form-label">Address <span class="text-secondary">(optional)</span></label>
            <textarea id="create-supplier-address" name="address" rows="2" class="form-control @error('address') is-invalid @enderror">{{ old('address') }}</textarea>
            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label for="create-supplier-tax" class="form-label">Tax number <span class="text-secondary">(optional)</span></label>
                <input id="create-supplier-tax" name="tax_number" type="text" maxlength="100" value="{{ old('tax_number') }}"
                    class="form-control @error('tax_number') is-invalid @enderror">
                @error('tax_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
                <label for="create-supplier-terms" class="form-label">Payment terms <span class="text-secondary">(optional)</span></label>
                <input id="create-supplier-terms" name="payment_terms" type="text" maxlength="100" value="{{ old('payment_terms') }}"
                    class="form-control @error('payment_terms') is-invalid @enderror">
                @error('payment_terms')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <input type="hidden" name="is_active" value="0">
        <div class="form-check">
            <input id="create-supplier-active" name="is_active" type="checkbox" value="1" class="form-check-input"
                @checked(old('is_active', '1') === '1' || old('is_active') === 1)>
            <label for="create-supplier-active" class="form-check-label">Active</label>
        </div>
    </form>
    <x-slot:footer>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="submit" form="createSupplierForm" class="btn btn-success">Save supplier</button>
        </div>
    </x-slot:footer>
</x-drawer>
