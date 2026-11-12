<x-drawer id="createStockLocationDrawer" title="Add stock location" width="32rem">
    <form id="createStockLocationForm" method="POST" action="{{ route('stock-locations.store') }}">
        @csrf
        <div class="mb-3">
            <label for="create-stock-location-code" class="form-label">Location code <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
            <input id="create-stock-location-code" name="code" type="text" maxlength="50" required value="{{ old('code') }}"
                class="form-control @error('code') is-invalid @enderror">
            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="create-stock-location-name" class="form-label">Name <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
            <input id="create-stock-location-name" name="name" type="text" maxlength="150" required value="{{ old('name') }}"
                class="form-control @error('name') is-invalid @enderror">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="create-stock-location-type" class="form-label">Location type <span class="text-secondary">(optional)</span></label>
            <input id="create-stock-location-type" name="location_type" type="text" maxlength="50" value="{{ old('location_type') }}"
                class="form-control @error('location_type') is-invalid @enderror">
            @error('location_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="create-stock-location-address" class="form-label">Address <span class="text-secondary">(optional)</span></label>
            <textarea id="create-stock-location-address" name="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address') }}</textarea>
            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <input type="hidden" name="is_active" value="0">
        <div class="form-check">
            <input id="create-stock-location-active" name="is_active" type="checkbox" value="1" class="form-check-input"
                @checked(old('is_active', '1') === '1' || old('is_active') === 1)>
            <label for="create-stock-location-active" class="form-check-label">Active</label>
        </div>
    </form>
    <x-slot:footer>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="submit" form="createStockLocationForm" class="btn btn-success">Save location</button>
        </div>
    </x-slot:footer>
</x-drawer>
