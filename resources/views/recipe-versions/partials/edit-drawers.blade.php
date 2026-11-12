@foreach ($recipeVersions as $recipeVersion)
    <x-drawer id="editRecipeVersionDrawer-{{ $recipeVersion->id }}" title="Edit recipe version" width="36rem">
        <form id="editRecipeVersionForm-{{ $recipeVersion->id }}" method="POST"
            action="{{ route('recipe-versions.update', $recipeVersion) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="edit-recipe-menu-item-{{ $recipeVersion->id }}" class="form-label">Menu item <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <select id="edit-recipe-menu-item-{{ $recipeVersion->id }}" name="menu_item_id" required
                    @class(['form-select', 'is-invalid' => session('openRecipeVersionDrawer') == $recipeVersion->id && $errors->has('menu_item_id')])>
                    <option value="">Select a menu item</option>
                    @foreach ($menuItems as $menuItem)
                        <option value="{{ $menuItem->id }}" @selected((string) old('menu_item_id', $recipeVersion->menu_item_id) === (string) $menuItem->id)>
                            {{ $menuItem->code }} — {{ $menuItem->name }}{{ $menuItem->is_active ? '' : ' (inactive)' }}
                        </option>
                    @endforeach
                </select>
                @if (session('openRecipeVersionDrawer') == $recipeVersion->id) @error('menu_item_id')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label for="edit-recipe-version-{{ $recipeVersion->id }}" class="form-label">Version number</label>
                    <input id="edit-recipe-version-{{ $recipeVersion->id }}" type="number"
                        value="{{ $recipeVersion->version_number }}" readonly class="form-control">
                </div>
                <div class="col-sm-6">
                    <label for="edit-recipe-yield-{{ $recipeVersion->id }}" class="form-label">Yield quantity <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                    <input id="edit-recipe-yield-{{ $recipeVersion->id }}" name="yield_quantity" type="number" min="0.001" step="0.001" required
                        value="{{ old('yield_quantity', $recipeVersion->yield_quantity) }}"
                        @class(['form-control', 'is-invalid' => session('openRecipeVersionDrawer') == $recipeVersion->id && $errors->has('yield_quantity')])>
                    @if (session('openRecipeVersionDrawer') == $recipeVersion->id) @error('yield_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
            </div>
            <div class="mb-3">
                <label for="edit-recipe-yield-unit-{{ $recipeVersion->id }}" class="form-label">Yield unit <span class="text-secondary">(optional)</span></label>
                <select id="edit-recipe-yield-unit-{{ $recipeVersion->id }}" name="yield_unit_id"
                    @class(['form-select', 'is-invalid' => session('openRecipeVersionDrawer') == $recipeVersion->id && $errors->has('yield_unit_id')])>
                    <option value="">No unit</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" @selected((string) old('yield_unit_id', $recipeVersion->yield_unit_id) === (string) $unit->id)>
                            {{ $unit->name }} ({{ $unit->abbreviation }}, {{ $unit->dimension }}){{ $unit->is_active ? '' : ' (inactive)' }}
                        </option>
                    @endforeach
                </select>
                @if (session('openRecipeVersionDrawer') == $recipeVersion->id) @error('yield_unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label for="edit-recipe-effective-from-{{ $recipeVersion->id }}" class="form-label">Effective from <span class="text-secondary">(optional)</span></label>
                    <input id="edit-recipe-effective-from-{{ $recipeVersion->id }}" name="effective_from" type="date"
                        value="{{ old('effective_from', $recipeVersion->effective_from?->format('Y-m-d')) }}"
                        @class(['form-control', 'is-invalid' => session('openRecipeVersionDrawer') == $recipeVersion->id && $errors->has('effective_from')])>
                    @if (session('openRecipeVersionDrawer') == $recipeVersion->id) @error('effective_from')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
                <div class="col-sm-6">
                    <label for="edit-recipe-effective-until-{{ $recipeVersion->id }}" class="form-label">Effective until <span class="text-secondary">(optional)</span></label>
                    <input id="edit-recipe-effective-until-{{ $recipeVersion->id }}" name="effective_until" type="date"
                        value="{{ old('effective_until', $recipeVersion->effective_until?->format('Y-m-d')) }}"
                        @class(['form-control', 'is-invalid' => session('openRecipeVersionDrawer') == $recipeVersion->id && $errors->has('effective_until')])>
                    @if (session('openRecipeVersionDrawer') == $recipeVersion->id) @error('effective_until')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
            </div>
            <div class="mb-3">
                <label for="edit-recipe-notes-{{ $recipeVersion->id }}" class="form-label">Notes <span class="text-secondary">(optional)</span></label>
                <textarea id="edit-recipe-notes-{{ $recipeVersion->id }}" name="notes" rows="3"
                    @class(['form-control', 'is-invalid' => session('openRecipeVersionDrawer') == $recipeVersion->id && $errors->has('notes')])>{{ old('notes', $recipeVersion->notes) }}</textarea>
                @if (session('openRecipeVersionDrawer') == $recipeVersion->id) @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <input type="hidden" name="is_current" value="0">
            <div class="form-check">
                <input id="edit-recipe-current-{{ $recipeVersion->id }}" name="is_current" type="checkbox" value="1" class="form-check-input"
                    @checked(old('is_current', $recipeVersion->is_current ? '1' : '0') === '1' || old('is_current') === 1)>
                <label for="edit-recipe-current-{{ $recipeVersion->id }}" class="form-check-label">Set as current version</label>
                <div class="form-text">Setting this version as current will mark the other versions for this menu item as previous.</div>
            </div>
        </form>

        <x-slot:footer>
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" form="editRecipeVersionForm-{{ $recipeVersion->id }}" class="btn btn-primary">Save changes</button>
            </div>
        </x-slot:footer>
    </x-drawer>
@endforeach
