<x-drawer id="createUnitDrawer" title="Add unit of measure" width="34rem">
    <form id="createUnitForm" method="POST" action="{{ route('units.store') }}">
        @csrf
        <div class="mb-3">
            <label for="create-unit-name" class="form-label">
                Name <span class="text-danger" aria-hidden="true">*</span>
                <span class="visually-hidden">(required)</span>
            </label>
            <input id="create-unit-name" name="name" type="text" maxlength="100" required
                value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="create-unit-abbreviation" class="form-label">
                Abbreviation <span class="text-danger" aria-hidden="true">*</span>
                <span class="visually-hidden">(required)</span>
            </label>
            <input id="create-unit-abbreviation" name="abbreviation" type="text" maxlength="20" required
                value="{{ old('abbreviation') }}" class="form-control @error('abbreviation') is-invalid @enderror">
            @error('abbreviation') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="create-unit-dimension" class="form-label">
                Dimension <span class="text-danger" aria-hidden="true">*</span>
                <span class="visually-hidden">(required)</span>
            </label>
            <input id="create-unit-dimension" name="dimension" type="text" maxlength="30" required
                value="{{ old('dimension') }}" placeholder="For example, mass, volume, or count"
                class="form-control @error('dimension') is-invalid @enderror">
            @error('dimension') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="create-unit-factor" class="form-label">
                Conversion factor to base <span class="text-danger" aria-hidden="true">*</span>
                <span class="visually-hidden">(required)</span>
            </label>
            <input id="create-unit-factor" name="conversion_factor_to_base" type="number" min="0.000001"
                step="0.000001" required value="{{ old('conversion_factor_to_base') }}"
                class="form-control @error('conversion_factor_to_base') is-invalid @enderror">
            <div class="form-text">A base unit must use a factor of 1.</div>
            @error('conversion_factor_to_base') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <input type="hidden" name="is_base_unit" value="0">
        <div class="form-check mb-3">
            <input id="create-unit-is-base" name="is_base_unit" type="checkbox" value="1"
                class="form-check-input @error('is_base_unit') is-invalid @enderror"
                @checked(old('is_base_unit') === '1' || old('is_base_unit') === 1)>
            <label for="create-unit-is-base" class="form-check-label">This is the base unit for its dimension</label>
            @error('is_base_unit') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <input type="hidden" name="is_active" value="0">
        <div class="form-check">
            <input id="create-unit-is-active" name="is_active" type="checkbox" value="1" class="form-check-input"
                @checked(old('is_active', '1') === '1' || old('is_active') === 1)>
            <label for="create-unit-is-active" class="form-check-label">Active</label>
        </div>
    </form>

    <x-slot:footer>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="submit" form="createUnitForm" class="btn btn-success">Save unit</button>
        </div>
    </x-slot:footer>
</x-drawer>
