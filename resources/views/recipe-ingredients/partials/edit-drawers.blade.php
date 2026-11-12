@foreach ($recipeIngredients as $recipeIngredient)
    <x-drawer id="editRecipeIngredientDrawer-{{ $recipeIngredient->id }}" title="Edit recipe ingredient" width="36rem">
        <form id="editRecipeIngredientForm-{{ $recipeIngredient->id }}" method="POST"
            action="{{ route('recipe-ingredients.update', $recipeIngredient) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="edit-recipe-ingredient-version-{{ $recipeIngredient->id }}" class="form-label">Recipe version <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <select id="edit-recipe-ingredient-version-{{ $recipeIngredient->id }}" name="recipe_version_id" required
                    @class(['form-select', 'is-invalid' => session('openRecipeIngredientDrawer') == $recipeIngredient->id && $errors->has('recipe_version_id')])>
                    <option value="">Select a recipe version</option>
                    @foreach ($recipeVersions as $version)
                        <option value="{{ $version->id }}" @selected((string) old('recipe_version_id', $recipeIngredient->recipe_version_id) === (string) $version->id)>
                            {{ $version->menuItem?->name ?? 'Menu item' }} · v{{ $version->version_number }}{{ $version->is_current ? ' (current)' : '' }}
                        </option>
                    @endforeach
                </select>
                @if (session('openRecipeIngredientDrawer') == $recipeIngredient->id) @error('recipe_version_id')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="mb-3">
                <label for="edit-recipe-ingredient-item-{{ $recipeIngredient->id }}" class="form-label">Ingredient <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <select id="edit-recipe-ingredient-item-{{ $recipeIngredient->id }}" name="ingredient_id" required
                    @class(['form-select', 'is-invalid' => session('openRecipeIngredientDrawer') == $recipeIngredient->id && $errors->has('ingredient_id')])>
                    <option value="">Select an ingredient</option>
                    @foreach ($ingredients as $ingredient)
                        <option value="{{ $ingredient->id }}" @selected((string) old('ingredient_id', $recipeIngredient->ingredient_id) === (string) $ingredient->id)>
                            {{ $ingredient->code }} — {{ $ingredient->name }} (base: {{ $ingredient->baseUnit?->abbreviation }})
                        </option>
                    @endforeach
                </select>
                @if (session('openRecipeIngredientDrawer') == $recipeIngredient->id) @error('ingredient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label for="edit-recipe-ingredient-quantity-{{ $recipeIngredient->id }}" class="form-label">Quantity <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                    <input id="edit-recipe-ingredient-quantity-{{ $recipeIngredient->id }}" name="quantity" type="number" min="0.001" step="0.001" required
                        value="{{ old('quantity', $recipeIngredient->quantity) }}"
                        @class(['form-control', 'is-invalid' => session('openRecipeIngredientDrawer') == $recipeIngredient->id && $errors->has('quantity')])>
                    @if (session('openRecipeIngredientDrawer') == $recipeIngredient->id) @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
                <div class="col-sm-6">
                    <label for="edit-recipe-ingredient-unit-{{ $recipeIngredient->id }}" class="form-label">Unit <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                    <select id="edit-recipe-ingredient-unit-{{ $recipeIngredient->id }}" name="unit_id" required
                        @class(['form-select', 'is-invalid' => session('openRecipeIngredientDrawer') == $recipeIngredient->id && $errors->has('unit_id')])>
                        <option value="">Select a unit</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected((string) old('unit_id', $recipeIngredient->unit_id) === (string) $unit->id)>
                                {{ $unit->name }} ({{ $unit->abbreviation }}, {{ $unit->dimension }}){{ $unit->is_active ? '' : ' (inactive)' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Choose a unit with the same dimension as the ingredient base unit. The quantity is converted automatically.</div>
                    @if (session('openRecipeIngredientDrawer') == $recipeIngredient->id) @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
            </div>
            <div class="mb-3">
                <label for="edit-recipe-ingredient-waste-{{ $recipeIngredient->id }}" class="form-label">Waste percentage <span class="text-secondary">(optional)</span></label>
                <input id="edit-recipe-ingredient-waste-{{ $recipeIngredient->id }}" name="waste_percentage" type="number" min="0" max="100" step="0.01"
                    value="{{ old('waste_percentage', $recipeIngredient->waste_percentage) }}"
                    @class(['form-control', 'is-invalid' => session('openRecipeIngredientDrawer') == $recipeIngredient->id && $errors->has('waste_percentage')])>
                <div class="form-text">Leave blank when there is no expected waste.</div>
                @if (session('openRecipeIngredientDrawer') == $recipeIngredient->id) @error('waste_percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="mb-3">
                <label for="edit-recipe-ingredient-notes-{{ $recipeIngredient->id }}" class="form-label">Notes <span class="text-secondary">(optional)</span></label>
                <textarea id="edit-recipe-ingredient-notes-{{ $recipeIngredient->id }}" name="notes" rows="3"
                    @class(['form-control', 'is-invalid' => session('openRecipeIngredientDrawer') == $recipeIngredient->id && $errors->has('notes')])>{{ old('notes', $recipeIngredient->notes) }}</textarea>
                @if (session('openRecipeIngredientDrawer') == $recipeIngredient->id) @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="form-text">Unit cost is recalculated from the latest ingredient price recorded on or before the recipe effective date. Without price history, the snapshot is zero.</div>
        </form>
        <x-slot:footer>
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" form="editRecipeIngredientForm-{{ $recipeIngredient->id }}" class="btn btn-primary">Save changes</button>
            </div>
        </x-slot:footer>
    </x-drawer>
@endforeach
