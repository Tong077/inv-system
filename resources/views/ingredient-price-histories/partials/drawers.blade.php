<x-drawer id="createIngredientPriceDrawer" title="Record ingredient price" width="36rem">
    <form id="createIngredientPriceForm" method="POST" action="{{ route('ingredient-price-histories.store') }}">
        @csrf
        <div class="mb-3">
            <label for="create-price-ingredient" class="form-label">Ingredient <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
            <select id="create-price-ingredient" name="ingredient_id" required class="form-select @error('ingredient_id') is-invalid @enderror">
                <option value="">Select an ingredient</option>
                @foreach ($ingredients as $ingredient)
                    <option value="{{ $ingredient->id }}" @selected((string) old('ingredient_id', $ingredientId) === (string) $ingredient->id)>
                        {{ $ingredient->code }} — {{ $ingredient->name }} (base: {{ $ingredient->baseUnit?->abbreviation }})
                    </option>
                @endforeach
            </select>
            @error('ingredient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="create-price-supplier" class="form-label">Supplier <span class="text-secondary">(optional)</span></label>
            <select id="create-price-supplier" name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
                <option value="">No supplier</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected((string) old('supplier_id') === (string) $supplier->id)>
                        {{ $supplier->code }} — {{ $supplier->name }}{{ $supplier->is_active ? '' : ' (inactive)' }}
                    </option>
                @endforeach
            </select>
            @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label for="create-price-package-price" class="form-label">Package price <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <input id="create-price-package-price" name="package_price" type="number" min="0" step="0.01" required value="{{ old('package_price') }}"
                    class="form-control @error('package_price') is-invalid @enderror">
                @error('package_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
                <label for="create-price-package-quantity" class="form-label">Package quantity <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <input id="create-price-package-quantity" name="package_quantity" type="number" min="0.001" step="0.001" required value="{{ old('package_quantity') }}"
                    class="form-control @error('package_quantity') is-invalid @enderror">
                @error('package_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="mb-3">
            <label for="create-price-package-unit" class="form-label">Package unit <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
            <select id="create-price-package-unit" name="package_unit_id" required class="form-select @error('package_unit_id') is-invalid @enderror">
                <option value="">Select a unit</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}" @selected((string) old('package_unit_id') === (string) $unit->id)>
                        {{ $unit->name }} ({{ $unit->abbreviation }}, {{ $unit->dimension }}){{ $unit->is_active ? '' : ' (inactive)' }}
                    </option>
                @endforeach
            </select>
            <div class="form-text">The unit dimension must match the ingredient base unit.</div>
            @error('package_unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="create-price-effective-date" class="form-label">Effective date <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
            <input id="create-price-effective-date" name="effective_date" type="date" required value="{{ old('effective_date') }}"
                class="form-control @error('effective_date') is-invalid @enderror">
            @error('effective_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="create-price-notes" class="form-label">Notes <span class="text-secondary">(optional)</span></label>
            <textarea id="create-price-notes" name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-text">Cost per base unit is calculated automatically from the package price and converted package quantity.</div>
    </form>
    <x-slot:footer>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="submit" form="createIngredientPriceForm" class="btn btn-success">Save price</button>
        </div>
    </x-slot:footer>
</x-drawer>
