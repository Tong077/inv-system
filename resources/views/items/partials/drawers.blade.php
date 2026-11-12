<x-drawer id="createIngredientDrawer" title="Add inventory item" width="36rem">
    @if ($baseUnits->isEmpty())
        <div class="alert alert-warning" role="alert">
            Create a base unit before adding an inventory item. <a href="{{ route('units.index') }}">Open units</a>.
        </div>
    @endif

    <form id="createIngredientForm" method="POST" action="{{ route('items.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="create-ingredient-code" class="form-label">
                Code <span class="text-danger" aria-hidden="true">*</span>
                <span class="visually-hidden">(required)</span>
            </label>
            <input id="create-ingredient-code" name="code" type="text" maxlength="50" required
                value="{{ old('code') }}" class="form-control @error('code') is-invalid @enderror">
            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="create-ingredient-name" class="form-label">
                Inventory item name <span class="text-danger" aria-hidden="true">*</span>
                <span class="visually-hidden">(required)</span>
            </label>
            <input id="create-ingredient-name" name="name" type="text" maxlength="200" required
                value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="create-ingredient-category" class="form-label">Category <span class="text-secondary">(optional)</span></label>
            <select id="create-ingredient-category" name="ingredient_category_id"
                class="form-select @error('ingredient_category_id') is-invalid @enderror">
                <option value="">No category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('ingredient_category_id') === (string) $category->id)>
                        {{ $category->name }}{{ $category->is_active ? '' : ' (inactive)' }}
                    </option>
                @endforeach
            </select>
            @error('ingredient_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="create-ingredient-base-unit" class="form-label">
                Base unit <span class="text-danger" aria-hidden="true">*</span>
                <span class="visually-hidden">(required)</span>
            </label>
            <select id="create-ingredient-base-unit" name="base_unit_id" required
                class="form-select @error('base_unit_id') is-invalid @enderror">
                <option value="">Select a base unit</option>
                @foreach ($baseUnits as $unit)
                    <option value="{{ $unit->id }}" @selected((string) old('base_unit_id') === (string) $unit->id)>
                        {{ $unit->name }} ({{ $unit->abbreviation }}, {{ $unit->dimension }}){{ $unit->is_active ? '' : ' — inactive' }}
                    </option>
                @endforeach
            </select>
            @error('base_unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="create-ingredient-minimum-stock" class="form-label">
                Minimum stock <span class="text-danger" aria-hidden="true">*</span>
                <span class="visually-hidden">(required)</span>
            </label>
            <input id="create-ingredient-minimum-stock" name="minimum_stock" type="number" min="0"
                max="999999999999.999" step="0.001" required
                value="{{ old('minimum_stock') }}" class="form-control @error('minimum_stock') is-invalid @enderror">
            @error('minimum_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="create-ingredient-description" class="form-label">Description <span class="text-secondary">(optional)</span></label>
            <textarea id="create-ingredient-description" name="description" rows="3"
                class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <x-image-upload id="create-ingredient-image" label="Inventory item photo"
            hint="JPG, PNG, or WebP, up to 5 MB. A square 600 × 600 photo works well."
            preview-alt="Selected ingredient preview" />

        <input type="hidden" name="is_active" value="0">
        <div class="form-check">
            <input id="create-ingredient-is-active" name="is_active" type="checkbox" value="1" class="form-check-input"
                @checked(old('is_active', '1') === '1' || old('is_active') === 1)>
            <label for="create-ingredient-is-active" class="form-check-label">Active</label>
        </div>
    </form>

    <x-slot:footer>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="submit" form="createIngredientForm" class="btn btn-success">Save inventory item</button>
        </div>
    </x-slot:footer>
</x-drawer>
