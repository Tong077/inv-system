@foreach ($histories as $history)
    <x-drawer id="editIngredientPriceDrawer-{{ $history->id }}" title="Edit ingredient price" width="36rem">
        <form id="editIngredientPriceForm-{{ $history->id }}" method="POST" action="{{ route('ingredient-price-histories.update', $history) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="edit-price-ingredient-{{ $history->id }}" class="form-label">Ingredient <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <select id="edit-price-ingredient-{{ $history->id }}" name="ingredient_id" required
                    @class(['form-select', 'is-invalid' => session('openIngredientPriceDrawer') == $history->id && $errors->has('ingredient_id')])>
                    <option value="">Select an ingredient</option>
                    @foreach ($ingredients as $ingredient)
                        <option value="{{ $ingredient->id }}" @selected((string) old('ingredient_id', $history->ingredient_id) === (string) $ingredient->id)>
                            {{ $ingredient->code }} — {{ $ingredient->name }} (base: {{ $ingredient->baseUnit?->abbreviation }})
                        </option>
                    @endforeach
                </select>
                @if (session('openIngredientPriceDrawer') == $history->id) @error('ingredient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="mb-3">
                <label for="edit-price-supplier-{{ $history->id }}" class="form-label">Supplier <span class="text-secondary">(optional)</span></label>
                <select id="edit-price-supplier-{{ $history->id }}" name="supplier_id"
                    @class(['form-select', 'is-invalid' => session('openIngredientPriceDrawer') == $history->id && $errors->has('supplier_id')])>
                    <option value="">No supplier</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected((string) old('supplier_id', $history->supplier_id) === (string) $supplier->id)>
                            {{ $supplier->code }} — {{ $supplier->name }}{{ $supplier->is_active ? '' : ' (inactive)' }}
                        </option>
                    @endforeach
                </select>
                @if (session('openIngredientPriceDrawer') == $history->id) @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label for="edit-price-package-price-{{ $history->id }}" class="form-label">Package price <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                    <input id="edit-price-package-price-{{ $history->id }}" name="package_price" type="number" min="0" step="0.01" required
                        value="{{ old('package_price', $history->package_price) }}"
                        @class(['form-control', 'is-invalid' => session('openIngredientPriceDrawer') == $history->id && $errors->has('package_price')])>
                    @if (session('openIngredientPriceDrawer') == $history->id) @error('package_price')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
                <div class="col-sm-6">
                    <label for="edit-price-package-quantity-{{ $history->id }}" class="form-label">Package quantity <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                    <input id="edit-price-package-quantity-{{ $history->id }}" name="package_quantity" type="number" min="0.001" step="0.001" required
                        value="{{ old('package_quantity', $history->package_quantity) }}"
                        @class(['form-control', 'is-invalid' => session('openIngredientPriceDrawer') == $history->id && $errors->has('package_quantity')])>
                    @if (session('openIngredientPriceDrawer') == $history->id) @error('package_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
            </div>
            <div class="mb-3">
                <label for="edit-price-package-unit-{{ $history->id }}" class="form-label">Package unit <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <select id="edit-price-package-unit-{{ $history->id }}" name="package_unit_id" required
                    @class(['form-select', 'is-invalid' => session('openIngredientPriceDrawer') == $history->id && $errors->has('package_unit_id')])>
                    <option value="">Select a unit</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" @selected((string) old('package_unit_id', $history->package_unit_id) === (string) $unit->id)>
                            {{ $unit->name }} ({{ $unit->abbreviation }}, {{ $unit->dimension }}){{ $unit->is_active ? '' : ' (inactive)' }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">The unit dimension must match the ingredient base unit.</div>
                @if (session('openIngredientPriceDrawer') == $history->id) @error('package_unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="mb-3">
                <label for="edit-price-effective-date-{{ $history->id }}" class="form-label">Effective date <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <input id="edit-price-effective-date-{{ $history->id }}" name="effective_date" type="date" required
                    value="{{ old('effective_date', $history->effective_date?->format('Y-m-d')) }}"
                    @class(['form-control', 'is-invalid' => session('openIngredientPriceDrawer') == $history->id && $errors->has('effective_date')])>
                @if (session('openIngredientPriceDrawer') == $history->id) @error('effective_date')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="mb-3">
                <label for="edit-price-notes-{{ $history->id }}" class="form-label">Notes <span class="text-secondary">(optional)</span></label>
                <textarea id="edit-price-notes-{{ $history->id }}" name="notes" rows="3"
                    @class(['form-control', 'is-invalid' => session('openIngredientPriceDrawer') == $history->id && $errors->has('notes')])>{{ old('notes', $history->notes) }}</textarea>
                @if (session('openIngredientPriceDrawer') == $history->id) @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="form-text">Cost per base unit is recalculated automatically from the package price and converted package quantity.</div>
        </form>
        <x-slot:footer>
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" form="editIngredientPriceForm-{{ $history->id }}" class="btn btn-primary">Save changes</button>
            </div>
        </x-slot:footer>
    </x-drawer>
@endforeach
