@foreach ($locations as $location)
    <x-drawer id="editStockLocationDrawer-{{ $location->id }}" title="Edit stock location" width="32rem">
        <form id="editStockLocationForm-{{ $location->id }}" method="POST" action="{{ route('stock-locations.update', $location) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="edit-stock-location-code-{{ $location->id }}" class="form-label">Location code <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <input id="edit-stock-location-code-{{ $location->id }}" name="code" type="text" maxlength="50" required value="{{ old('code', $location->code) }}"
                    @class(['form-control', 'is-invalid' => session('openStockLocationDrawer') == $location->id && $errors->has('code')])>
                @if (session('openStockLocationDrawer') == $location->id) @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="mb-3">
                <label for="edit-stock-location-name-{{ $location->id }}" class="form-label">Name <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <input id="edit-stock-location-name-{{ $location->id }}" name="name" type="text" maxlength="150" required value="{{ old('name', $location->name) }}"
                    @class(['form-control', 'is-invalid' => session('openStockLocationDrawer') == $location->id && $errors->has('name')])>
                @if (session('openStockLocationDrawer') == $location->id) @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="mb-3">
                <label for="edit-stock-location-type-{{ $location->id }}" class="form-label">Location type <span class="text-secondary">(optional)</span></label>
                <input id="edit-stock-location-type-{{ $location->id }}" name="location_type" type="text" maxlength="50" value="{{ old('location_type', $location->location_type) }}"
                    @class(['form-control', 'is-invalid' => session('openStockLocationDrawer') == $location->id && $errors->has('location_type')])>
                @if (session('openStockLocationDrawer') == $location->id) @error('location_type')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="mb-3">
                <label for="edit-stock-location-address-{{ $location->id }}" class="form-label">Address <span class="text-secondary">(optional)</span></label>
                <textarea id="edit-stock-location-address-{{ $location->id }}" name="address" rows="3"
                    @class(['form-control', 'is-invalid' => session('openStockLocationDrawer') == $location->id && $errors->has('address')])>{{ old('address', $location->address) }}</textarea>
                @if (session('openStockLocationDrawer') == $location->id) @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <input type="hidden" name="is_active" value="0">
            <div class="form-check">
                <input id="edit-stock-location-active-{{ $location->id }}" name="is_active" type="checkbox" value="1" class="form-check-input"
                    @checked(old('is_active', $location->is_active ? '1' : '0') === '1' || old('is_active') === 1)>
                <label for="edit-stock-location-active-{{ $location->id }}" class="form-check-label">Active</label>
            </div>
        </form>
        <x-slot:footer>
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" form="editStockLocationForm-{{ $location->id }}" class="btn btn-primary">Save changes</button>
            </div>
        </x-slot:footer>
    </x-drawer>
@endforeach
