@foreach ($units as $unit)
    <x-drawer id="editUnitDrawer-{{ $unit->id }}" title="Edit unit of measure" width="34rem">
        <form id="editUnitForm-{{ $unit->id }}" method="POST" action="{{ route('units.update', $unit) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="edit-unit-name-{{ $unit->id }}" class="form-label">
                    Name <span class="text-danger" aria-hidden="true">*</span>
                    <span class="visually-hidden">(required)</span>
                </label>
                <input id="edit-unit-name-{{ $unit->id }}" name="name" type="text" maxlength="100" required
                    value="{{ old('name', $unit->name) }}"
                    class="form-control @if (session('openUnitDrawer') == $unit->id) @error('name') is-invalid @enderror @endif">
                @if (session('openUnitDrawer') == $unit->id) @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror @endif
            </div>

            <div class="mb-3">
                <label for="edit-unit-abbreviation-{{ $unit->id }}" class="form-label">
                    Abbreviation <span class="text-danger" aria-hidden="true">*</span>
                    <span class="visually-hidden">(required)</span>
                </label>
                <input id="edit-unit-abbreviation-{{ $unit->id }}" name="abbreviation" type="text" maxlength="20" required
                    value="{{ old('abbreviation', $unit->abbreviation) }}"
                    class="form-control @if (session('openUnitDrawer') == $unit->id) @error('abbreviation') is-invalid @enderror @endif">
                @if (session('openUnitDrawer') == $unit->id) @error('abbreviation')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror @endif
            </div>

            <div class="mb-3">
                <label for="edit-unit-dimension-{{ $unit->id }}" class="form-label">
                    Dimension <span class="text-danger" aria-hidden="true">*</span>
                    <span class="visually-hidden">(required)</span>
                </label>
                <input id="edit-unit-dimension-{{ $unit->id }}" name="dimension" type="text" maxlength="30" required
                    value="{{ old('dimension', $unit->dimension) }}"
                    class="form-control @if (session('openUnitDrawer') == $unit->id) @error('dimension') is-invalid @enderror @endif">
                @if (session('openUnitDrawer') == $unit->id) @error('dimension')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror @endif
            </div>

            <div class="mb-3">
                <label for="edit-unit-factor-{{ $unit->id }}" class="form-label">
                    Conversion factor to base <span class="text-danger" aria-hidden="true">*</span>
                    <span class="visually-hidden">(required)</span>
                </label>
                <input id="edit-unit-factor-{{ $unit->id }}" name="conversion_factor_to_base" type="number"
                    min="0.000001" step="0.000001" required
                    value="{{ old('conversion_factor_to_base', $unit->conversion_factor_to_base) }}"
                    class="form-control @if (session('openUnitDrawer') == $unit->id) @error('conversion_factor_to_base') is-invalid @enderror @endif">
                <div class="form-text">A base unit must use a factor of 1.</div>
                @if (session('openUnitDrawer') == $unit->id) @error('conversion_factor_to_base')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror @endif
            </div>

            <input type="hidden" name="is_base_unit" value="0">
            <div class="form-check mb-3">
                <input id="edit-unit-is-base-{{ $unit->id }}" name="is_base_unit" type="checkbox" value="1"
                    class="form-check-input @if (session('openUnitDrawer') == $unit->id) @error('is_base_unit') is-invalid @enderror @endif"
                    @checked(old('is_base_unit', $unit->is_base_unit ? '1' : '0') === '1' || old('is_base_unit') === 1)>
                <label for="edit-unit-is-base-{{ $unit->id }}" class="form-check-label">This is the base unit for its dimension</label>
                @if (session('openUnitDrawer') == $unit->id) @error('is_base_unit')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror @endif
            </div>

            <input type="hidden" name="is_active" value="0">
            <div class="form-check">
                <input id="edit-unit-is-active-{{ $unit->id }}" name="is_active" type="checkbox" value="1"
                    class="form-check-input"
                    @checked(old('is_active', $unit->is_active ? '1' : '0') === '1' || old('is_active') === 1)>
                <label for="edit-unit-is-active-{{ $unit->id }}" class="form-check-label">Active</label>
            </div>
        </form>

        <x-slot:footer>
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" form="editUnitForm-{{ $unit->id }}" class="btn btn-primary">Save changes</button>
            </div>
        </x-slot:footer>
    </x-drawer>
@endforeach
