<x-drawer id="createRecipeIngredientDrawer" title="Add recipe ingredient" width="36rem">
    <form id="createRecipeIngredientForm" method="POST" action="{{ route('recipe-ingredients.store') }}">
        @csrf
        <div class="mb-3">
            <label for="create-recipe-ingredient-version" class="form-label">Recipe version <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
            <select id="create-recipe-ingredient-version" name="recipe_version_id" required class="form-select @error('recipe_version_id') is-invalid @enderror">
                <option value="">Select a recipe version</option>
                @foreach ($recipeVersions as $version)
                    <option value="{{ $version->id }}" @selected((string) old('recipe_version_id', $recipeVersionId) === (string) $version->id)>
                        {{ $version->menuItem?->name ?? 'Menu item' }} · v{{ $version->version_number }}{{ $version->is_current ? ' (current)' : '' }}
                    </option>
                @endforeach
            </select>
            @error('recipe_version_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="create-recipe-ingredient-item" class="form-label">Ingredient <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
            <select id="create-recipe-ingredient-item" name="ingredient_id" required class="form-select @error('ingredient_id') is-invalid @enderror">
                <option value="">Select an ingredient</option>
                @foreach ($ingredients as $ingredient)
                    <option value="{{ $ingredient->id }}" @selected((string) old('ingredient_id') === (string) $ingredient->id)>
                        {{ $ingredient->code }} — {{ $ingredient->name }} (base: {{ $ingredient->baseUnit?->abbreviation }})
                    </option>
                @endforeach
            </select>
            @error('ingredient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label for="create-recipe-ingredient-quantity" class="form-label">Quantity <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <input id="create-recipe-ingredient-quantity" name="quantity" type="number" min="0.001" step="0.001" required value="{{ old('quantity') }}"
                    class="form-control @error('quantity') is-invalid @enderror">
                @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
                <label for="create-recipe-ingredient-unit" class="form-label">Unit <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <select id="create-recipe-ingredient-unit" name="unit_id" required class="form-select @error('unit_id') is-invalid @enderror">
                    <option value="">Select a unit</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" @selected((string) old('unit_id') === (string) $unit->id)>
                            {{ $unit->name }} ({{ $unit->abbreviation }}, {{ $unit->dimension }}){{ $unit->is_active ? '' : ' (inactive)' }}
                        </option>
                    @endforeach
                </select>
            <div class="form-text">Choose a unit with the same dimension as the ingredient base unit. The quantity is converted automatically.</div>
                @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="mb-3">
            <label for="create-recipe-ingredient-waste" class="form-label">Waste percentage <span class="text-secondary">(optional)</span></label>
            <input id="create-recipe-ingredient-waste" name="waste_percentage" type="number" min="0" max="100" step="0.01" value="{{ old('waste_percentage') }}"
                class="form-control @error('waste_percentage') is-invalid @enderror">
            <div class="form-text">Leave blank when there is no expected waste.</div>
            @error('waste_percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="create-recipe-ingredient-notes" class="form-label">Notes <span class="text-secondary">(optional)</span></label>
            <textarea id="create-recipe-ingredient-notes" name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-text">Unit cost is calculated from the latest ingredient price recorded on or before the recipe effective date. Without price history, the snapshot is zero.</div>
    </form>
    <x-slot:footer>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="submit" form="createRecipeIngredientForm" class="btn btn-success">Add ingredient</button>
        </div>
    </x-slot:footer>
</x-drawer>
