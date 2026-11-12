<x-drawer id="createSupplierContactDrawer" title="Add supplier contact" width="32rem">
    <form id="createSupplierContactForm" method="POST" action="{{ route('supplier-contacts.store') }}">
        @csrf
        <div class="mb-3">
            <label for="create-contact-supplier" class="form-label">Supplier <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
            <select id="create-contact-supplier" name="supplier_id" required class="form-select @error('supplier_id') is-invalid @enderror">
                <option value="">Select a supplier</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected((string) old('supplier_id') === (string) $supplier->id)>
                        {{ $supplier->code }} — {{ $supplier->name }}{{ $supplier->is_active ? '' : ' (inactive)' }}
                    </option>
                @endforeach
            </select>
            @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="create-contact-name" class="form-label">Contact name <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
            <input id="create-contact-name" name="name" type="text" maxlength="150" required value="{{ old('name') }}"
                class="form-control @error('name') is-invalid @enderror">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="create-contact-position" class="form-label">Position <span class="text-secondary">(optional)</span></label>
            <input id="create-contact-position" name="position" type="text" maxlength="100" value="{{ old('position') }}"
                class="form-control @error('position') is-invalid @enderror">
            @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label for="create-contact-phone" class="form-label">Phone <span class="text-secondary">(optional)</span></label>
                <input id="create-contact-phone" name="phone" type="tel" maxlength="50" value="{{ old('phone') }}"
                    class="form-control @error('phone') is-invalid @enderror">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
                <label for="create-contact-email" class="form-label">Email <span class="text-secondary">(optional)</span></label>
                <input id="create-contact-email" name="email" type="email" maxlength="150" value="{{ old('email') }}"
                    class="form-control @error('email') is-invalid @enderror">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </form>
    <x-slot:footer>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="submit" form="createSupplierContactForm" class="btn btn-success">Save contact</button>
        </div>
    </x-slot:footer>
</x-drawer>
