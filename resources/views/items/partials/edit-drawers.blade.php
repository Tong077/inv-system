@foreach ($ingredients as $ingredient)
    <x-drawer id="editIngredientDrawer-{{ $ingredient->id }}" title="Edit inventory item" width="36rem">
        <form id="editIngredientForm-{{ $ingredient->id }}" method="POST"
            action="{{ route('items.update', $ingredient) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="edit-ingredient-code-{{ $ingredient->id }}" class="form-label">
                    Code <span class="text-danger" aria-hidden="true">*</span>
                    <span class="visually-hidden">(required)</span>
                </label>
                <input id="edit-ingredient-code-{{ $ingredient->id }}" name="code" type="text" maxlength="50" required
                    value="{{ old('code', $ingredient->code) }}"
                    class="form-control @if (session('openIngredientDrawer') == $ingredient->id) @error('code') is-invalid @enderror @endif">
                @if (session('openIngredientDrawer') == $ingredient->id) @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror @endif
            </div>

            <div class="mb-3">
                <label for="edit-ingredient-name-{{ $ingredient->id }}" class="form-label">
                    Inventory item name <span class="text-danger" aria-hidden="true">*</span>
                    <span class="visually-hidden">(required)</span>
                </label>
                <input id="edit-ingredient-name-{{ $ingredient->id }}" name="name" type="text" maxlength="200" required
                    value="{{ old('name', $ingredient->name) }}"
                    class="form-control @if (session('openIngredientDrawer') == $ingredient->id) @error('name') is-invalid @enderror @endif">
                @if (session('openIngredientDrawer') == $ingredient->id) @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror @endif
            </div>

            <div class="mb-3">
                <label for="edit-ingredient-category-{{ $ingredient->id }}" class="form-label">Category <span class="text-secondary">(optional)</span></label>
                <select id="edit-ingredient-category-{{ $ingredient->id }}" name="ingredient_category_id"
                    class="form-select @if (session('openIngredientDrawer') == $ingredient->id) @error('ingredient_category_id') is-invalid @enderror @endif">
                    <option value="">No category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('ingredient_category_id', $ingredient->ingredient_category_id) === (string) $category->id)>
                            {{ $category->name }}{{ $category->is_active ? '' : ' (inactive)' }}
                        </option>
                    @endforeach
                </select>
                @if (session('openIngredientDrawer') == $ingredient->id) @error('ingredient_category_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror @endif
            </div>

            <div class="mb-3">
                <label for="edit-ingredient-base-unit-{{ $ingredient->id }}" class="form-label">
                    Base unit <span class="text-danger" aria-hidden="true">*</span>
                    <span class="visually-hidden">(required)</span>
                </label>
                <select id="edit-ingredient-base-unit-{{ $ingredient->id }}" name="base_unit_id" required
                    class="form-select @if (session('openIngredientDrawer') == $ingredient->id) @error('base_unit_id') is-invalid @enderror @endif">
                    <option value="">Select a base unit</option>
                    @foreach ($baseUnits as $unit)
                        <option value="{{ $unit->id }}" @selected((string) old('base_unit_id', $ingredient->base_unit_id) === (string) $unit->id)>
                            {{ $unit->name }} ({{ $unit->abbreviation }}, {{ $unit->dimension }}){{ $unit->is_active ? '' : ' — inactive' }}
                        </option>
                    @endforeach
                </select>
                @if (session('openIngredientDrawer') == $ingredient->id) @error('base_unit_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror @endif
            </div>

            <div class="mb-3">
                <label for="edit-ingredient-minimum-stock-{{ $ingredient->id }}" class="form-label">
                    Minimum stock <span class="text-danger" aria-hidden="true">*</span>
                    <span class="visually-hidden">(required)</span>
                </label>
                <input id="edit-ingredient-minimum-stock-{{ $ingredient->id }}" name="minimum_stock" type="number"
                    min="0" max="999999999999.999" step="0.001" required
                    value="{{ old('minimum_stock', $ingredient->minimum_stock) }}"
                    class="form-control @if (session('openIngredientDrawer') == $ingredient->id) @error('minimum_stock') is-invalid @enderror @endif">
                @if (session('openIngredientDrawer') == $ingredient->id) @error('minimum_stock')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror @endif
            </div>

            <div class="mb-3">
                <label for="edit-ingredient-description-{{ $ingredient->id }}" class="form-label">Description <span class="text-secondary">(optional)</span></label>
                <textarea id="edit-ingredient-description-{{ $ingredient->id }}" name="description" rows="3"
                    class="form-control @if (session('openIngredientDrawer') == $ingredient->id) @error('description') is-invalid @enderror @endif">{{ old('description', $ingredient->description) }}</textarea>
                @if (session('openIngredientDrawer') == $ingredient->id) @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror @endif
            </div>

            <x-image-upload id="edit-ingredient-image-{{ $ingredient->id }}" label="Inventory item photo"
                hint="JPG, PNG, or WebP, up to 5 MB. A square 600 × 600 photo works well."
                :current-url="$ingredient->image_url" :current-alt="'Current photo for '.$ingredient->name"
                preview-alt="Selected ingredient preview" removable remove-label="Remove current photo"
                :remove-checked="session('openIngredientDrawer') == $ingredient->id && (old('remove_image') === '1' || old('remove_image') === 1)" />

            <input type="hidden" name="is_active" value="0">
            <div class="form-check">
                <input id="edit-ingredient-is-active-{{ $ingredient->id }}" name="is_active" type="checkbox" value="1"
                    class="form-check-input"
                    @checked(old('is_active', $ingredient->is_active ? '1' : '0') === '1' || old('is_active') === 1)>
                <label for="edit-ingredient-is-active-{{ $ingredient->id }}" class="form-check-label">Active</label>
            </div>
        </form>

        <x-slot:footer>
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" form="editIngredientForm-{{ $ingredient->id }}" class="btn btn-primary">Save changes</button>
            </div>
        </x-slot:footer>
    </x-drawer>
@endforeach
