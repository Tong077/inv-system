<x-drawer id="createRecipeVersionDrawer" title="Add recipe version" width="36rem">
    <form id="createRecipeVersionForm" method="POST" action="{{ route('recipe-versions.store') }}">
        @csrf
        <div class="mb-3">
            <label for="create-recipe-menu-item" class="form-label">Menu item <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
            <select id="create-recipe-menu-item" name="menu_item_id" required class="form-select @error('menu_item_id') is-invalid @enderror">
                <option value="">Select a menu item</option>
                @foreach ($menuItems as $menuItem)
                    <option value="{{ $menuItem->id }}" @selected((string) old('menu_item_id') === (string) $menuItem->id)>
                        {{ $menuItem->code }} — {{ $menuItem->name }}{{ $menuItem->is_active ? '' : ' (inactive)' }}
                    </option>
                @endforeach
            </select>
            @error('menu_item_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label class="form-label">Version number</label>
                <div class="form-control bg-light" aria-readonly="true">Assigned automatically for the selected menu item</div>
            </div>
            <div class="col-sm-6">
                <label for="create-recipe-yield" class="form-label">Yield quantity <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <input id="create-recipe-yield" name="yield_quantity" type="number" min="0.001" step="0.001" required value="{{ old('yield_quantity') }}"
                    class="form-control @error('yield_quantity') is-invalid @enderror">
                @error('yield_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="mb-3">
            <label for="create-recipe-yield-unit" class="form-label">Yield unit <span class="text-secondary">(optional)</span></label>
            <select id="create-recipe-yield-unit" name="yield_unit_id" class="form-select @error('yield_unit_id') is-invalid @enderror">
                <option value="">No unit</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}" @selected((string) old('yield_unit_id') === (string) $unit->id)>
                        {{ $unit->name }} ({{ $unit->abbreviation }}, {{ $unit->dimension }}){{ $unit->is_active ? '' : ' (inactive)' }}
                    </option>
                @endforeach
            </select>
            @error('yield_unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label for="create-recipe-effective-from" class="form-label">Effective from <span class="text-secondary">(optional)</span></label>
                <input id="create-recipe-effective-from" name="effective_from" type="date" value="{{ old('effective_from') }}"
                    class="form-control @error('effective_from') is-invalid @enderror">
                @error('effective_from')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
                <label for="create-recipe-effective-until" class="form-label">Effective until <span class="text-secondary">(optional)</span></label>
                <input id="create-recipe-effective-until" name="effective_until" type="date" value="{{ old('effective_until') }}"
                    class="form-control @error('effective_until') is-invalid @enderror">
                @error('effective_until')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="mb-3">
            <label for="create-recipe-notes" class="form-label">Notes <span class="text-secondary">(optional)</span></label>
            <textarea id="create-recipe-notes" name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <input type="hidden" name="is_current" value="0">
        <div class="form-check">
            <input id="create-recipe-current" name="is_current" type="checkbox" value="1" class="form-check-input"
                @checked(old('is_current') === '1' || old('is_current') === 1)>
            <label for="create-recipe-current" class="form-check-label">Set as current version</label>
            <div class="form-text">Setting this version as current will mark the other versions for this menu item as previous.</div>
        </div>
    </form>

    <x-slot:footer>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="submit" form="createRecipeVersionForm" class="btn btn-success">Save recipe version</button>
        </div>
    </x-slot:footer>
</x-drawer>
