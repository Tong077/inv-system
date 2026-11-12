@foreach ($contacts as $contact)
    <x-drawer id="editSupplierContactDrawer-{{ $contact->id }}" title="Edit supplier contact" width="32rem">
        <form id="editSupplierContactForm-{{ $contact->id }}" method="POST" action="{{ route('supplier-contacts.update', $contact) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="edit-contact-supplier-{{ $contact->id }}" class="form-label">Supplier <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <select id="edit-contact-supplier-{{ $contact->id }}" name="supplier_id" required
                    @class(['form-select', 'is-invalid' => session('openSupplierContactDrawer') == $contact->id && $errors->has('supplier_id')])>
                    <option value="">Select a supplier</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected((string) old('supplier_id', $contact->supplier_id) === (string) $supplier->id)>
                            {{ $supplier->code }} — {{ $supplier->name }}{{ $supplier->is_active ? '' : ' (inactive)' }}
                        </option>
                    @endforeach
                </select>
                @if (session('openSupplierContactDrawer') == $contact->id) @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="mb-3">
                <label for="edit-contact-name-{{ $contact->id }}" class="form-label">Contact name <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <input id="edit-contact-name-{{ $contact->id }}" name="name" type="text" maxlength="150" required value="{{ old('name', $contact->name) }}"
                    @class(['form-control', 'is-invalid' => session('openSupplierContactDrawer') == $contact->id && $errors->has('name')])>
                @if (session('openSupplierContactDrawer') == $contact->id) @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="mb-3">
                <label for="edit-contact-position-{{ $contact->id }}" class="form-label">Position <span class="text-secondary">(optional)</span></label>
                <input id="edit-contact-position-{{ $contact->id }}" name="position" type="text" maxlength="100" value="{{ old('position', $contact->position) }}"
                    @class(['form-control', 'is-invalid' => session('openSupplierContactDrawer') == $contact->id && $errors->has('position')])>
                @if (session('openSupplierContactDrawer') == $contact->id) @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label for="edit-contact-phone-{{ $contact->id }}" class="form-label">Phone <span class="text-secondary">(optional)</span></label>
                    <input id="edit-contact-phone-{{ $contact->id }}" name="phone" type="tel" maxlength="50" value="{{ old('phone', $contact->phone) }}"
                        @class(['form-control', 'is-invalid' => session('openSupplierContactDrawer') == $contact->id && $errors->has('phone')])>
                    @if (session('openSupplierContactDrawer') == $contact->id) @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
                <div class="col-sm-6">
                    <label for="edit-contact-email-{{ $contact->id }}" class="form-label">Email <span class="text-secondary">(optional)</span></label>
                    <input id="edit-contact-email-{{ $contact->id }}" name="email" type="email" maxlength="150" value="{{ old('email', $contact->email) }}"
                        @class(['form-control', 'is-invalid' => session('openSupplierContactDrawer') == $contact->id && $errors->has('email')])>
                    @if (session('openSupplierContactDrawer') == $contact->id) @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
            </div>
        </form>
        <x-slot:footer>
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" form="editSupplierContactForm-{{ $contact->id }}" class="btn btn-primary">Save changes</button>
            </div>
        </x-slot:footer>
    </x-drawer>
@endforeach
