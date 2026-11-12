<x-drawer id="createMenuItemDrawer" title="Add menu item" width="34rem">
    <form id="createMenuItemForm" method="POST" action="{{ route('menu-items.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="create-menu-item-code" class="form-label">Code <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
            <input id="create-menu-item-code" name="code" type="text" maxlength="50" required value="{{ old('code') }}"
                class="form-control @error('code') is-invalid @enderror">
            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="create-menu-item-name" class="form-label">Name <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
            <input id="create-menu-item-name" name="name" type="text" maxlength="200" required value="{{ old('name') }}"
                class="form-control @error('name') is-invalid @enderror">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="create-menu-item-barcode" class="form-label">Barcode <span class="text-secondary">(optional)</span></label>
            <input id="create-menu-item-barcode" name="barcode" type="text" maxlength="100" value="{{ old('barcode') }}"
                class="form-control @error('barcode') is-invalid @enderror" autocomplete="off">
            @error('barcode')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Use the product barcode to add this item quickly at checkout.</div>
        </div>
        <div class="mb-3">
            <label for="create-menu-item-category" class="form-label">Category <span class="text-secondary">(optional)</span></label>
            <select id="create-menu-item-category" name="menu_category_id" class="form-select @error('menu_category_id') is-invalid @enderror">
                <option value="">No category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('menu_category_id') === (string) $category->id)>
                        {{ $category->name }}{{ $category->is_active ? '' : ' (inactive)' }}
                    </option>
                @endforeach
            </select>
            @error('menu_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="row g-3 mb-3">
            <div class="col-sm-8">
                <label for="create-menu-item-price" class="form-label">Selling price <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <input id="create-menu-item-price" name="selling_price" type="number" min="0" step="0.01" required value="{{ old('selling_price') }}"
                    class="form-control @error('selling_price') is-invalid @enderror">
                @error('selling_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-4">
                <label for="create-menu-item-currency" class="form-label">Currency <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <input id="create-menu-item-currency" name="currency" type="text" maxlength="10" required value="{{ old('currency', 'USD') }}"
                    class="form-control @error('currency') is-invalid @enderror">
                @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="mb-3">
            <label for="create-menu-item-sale-mode" class="form-label">Stock deduction</label>
            <select id="create-menu-item-sale-mode" name="sale_mode" class="form-select @error('sale_mode') is-invalid @enderror" data-sale-mode-select>
                <option value="recipe" @selected(old('sale_mode', 'recipe') === 'recipe')>Use recipe ingredients</option>
                <option value="stock_item" @selected(old('sale_mode') === 'stock_item')>Sell an inventory item directly</option>
            </select>
            @error('sale_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Choose direct stock for bottled drinks or other products sold as-is.</div>
        </div>
        <div class="sale-mode-stock-fields" data-sale-mode-stock-fields>
            <div class="mb-3">
                <label for="create-menu-item-stock-item" class="form-label">Inventory item</label>
                <select id="create-menu-item-stock-item" name="stock_ingredient_id" class="form-select @error('stock_ingredient_id') is-invalid @enderror" data-sale-stock-item>
                    <option value="">Select an inventory item</option>
                    @foreach ($stockItems as $stockItem)
                        <option value="{{ $stockItem->id }}" data-unit="{{ $stockItem->baseUnit?->abbreviation }}" @selected((string) old('stock_ingredient_id') === (string) $stockItem->id)>{{ $stockItem->code }} — {{ $stockItem->name }}{{ $stockItem->is_active ? '' : ' (inactive)' }}</option>
                    @endforeach
                </select>
                @error('stock_ingredient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="create-menu-item-stock-quantity" class="form-label">Stock used per sale</label>
                <div class="input-group">
                    <input id="create-menu-item-stock-quantity" name="stock_quantity" type="number" min="0.001" step="0.001" value="{{ old('stock_quantity', '1') }}" class="form-control @error('stock_quantity') is-invalid @enderror" data-sale-stock-quantity>
                    <span class="input-group-text" data-sale-stock-unit>base units</span>
                    @error('stock_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-text">For one bottle sold from stock tracked as bottles, enter 1.</div>
            </div>
        </div>
        <div class="mb-3">
            <label for="create-menu-item-description" class="form-label">Description <span class="text-secondary">(optional)</span></label>
            <textarea id="create-menu-item-description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <x-image-upload id="create-menu-item-image" label="Menu photo"
            hint="JPG, PNG, or WebP, up to 5 MB. A square 600 × 600 photo works best in the POS."
            preview-alt="Selected menu item preview" />
        <input type="hidden" name="is_active" value="0">
        <div class="form-check">
            <input id="create-menu-item-active" name="is_active" type="checkbox" value="1" class="form-check-input"
                @checked(old('is_active', '1') === '1' || old('is_active') === 1)>
            <label class="form-check-label" for="create-menu-item-active">Active</label>
        </div>
    </form>

    <x-slot:footer>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="submit" form="createMenuItemForm" class="btn btn-success">Save menu item</button>
        </div>
    </x-slot:footer>
</x-drawer>
