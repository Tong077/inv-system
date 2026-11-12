@foreach ($menuItems as $menuItem)
    <x-drawer id="editMenuItemDrawer-{{ $menuItem->id }}" title="Edit menu item" width="34rem">
        <form id="editMenuItemForm-{{ $menuItem->id }}" method="POST" action="{{ route('menu-items.update', $menuItem) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="edit-menu-item-code-{{ $menuItem->id }}" class="form-label">Code <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <input id="edit-menu-item-code-{{ $menuItem->id }}" name="code" type="text" maxlength="50" required
                    value="{{ old('code', $menuItem->code) }}"
                    @class(['form-control', 'is-invalid' => session('openMenuItemDrawer') == $menuItem->id && $errors->has('code')])>
                @if (session('openMenuItemDrawer') == $menuItem->id) @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="mb-3">
                <label for="edit-menu-item-name-{{ $menuItem->id }}" class="form-label">Name <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <input id="edit-menu-item-name-{{ $menuItem->id }}" name="name" type="text" maxlength="200" required
                    value="{{ old('name', $menuItem->name) }}"
                    @class(['form-control', 'is-invalid' => session('openMenuItemDrawer') == $menuItem->id && $errors->has('name')])>
                @if (session('openMenuItemDrawer') == $menuItem->id) @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="mb-3">
                <label for="edit-menu-item-barcode-{{ $menuItem->id }}" class="form-label">Barcode <span class="text-secondary">(optional)</span></label>
                <input id="edit-menu-item-barcode-{{ $menuItem->id }}" name="barcode" type="text" maxlength="100"
                    value="{{ old('barcode', $menuItem->barcode) }}" autocomplete="off"
                    @class(['form-control', 'is-invalid' => session('openMenuItemDrawer') == $menuItem->id && $errors->has('barcode')])>
                @if (session('openMenuItemDrawer') == $menuItem->id) @error('barcode')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                <div class="form-text">Use the product barcode to add this item quickly at checkout.</div>
            </div>
            <div class="mb-3">
                <label for="edit-menu-item-category-{{ $menuItem->id }}" class="form-label">Category <span class="text-secondary">(optional)</span></label>
                <select id="edit-menu-item-category-{{ $menuItem->id }}" name="menu_category_id"
                    @class(['form-select', 'is-invalid' => session('openMenuItemDrawer') == $menuItem->id && $errors->has('menu_category_id')])>
                    <option value="">No category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('menu_category_id', $menuItem->menu_category_id) === (string) $category->id)>
                            {{ $category->name }}{{ $category->is_active ? '' : ' (inactive)' }}
                        </option>
                    @endforeach
                </select>
                @if (session('openMenuItemDrawer') == $menuItem->id) @error('menu_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="row g-3 mb-3">
                <div class="col-sm-8">
                    <label for="edit-menu-item-price-{{ $menuItem->id }}" class="form-label">Selling price <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                    <input id="edit-menu-item-price-{{ $menuItem->id }}" name="selling_price" type="number" min="0" step="0.01" required
                        value="{{ old('selling_price', $menuItem->selling_price) }}"
                        @class(['form-control', 'is-invalid' => session('openMenuItemDrawer') == $menuItem->id && $errors->has('selling_price')])>
                    @if (session('openMenuItemDrawer') == $menuItem->id) @error('selling_price')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
                <div class="col-sm-4">
                    <label for="edit-menu-item-currency-{{ $menuItem->id }}" class="form-label">Currency <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                    <input id="edit-menu-item-currency-{{ $menuItem->id }}" name="currency" type="text" maxlength="10" required
                        value="{{ old('currency', $menuItem->currency) }}"
                        @class(['form-control', 'is-invalid' => session('openMenuItemDrawer') == $menuItem->id && $errors->has('currency')])>
                    @if (session('openMenuItemDrawer') == $menuItem->id) @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
            </div>
            <div class="mb-3">
                <label for="edit-menu-item-sale-mode-{{ $menuItem->id }}" class="form-label">Stock deduction</label>
                <select id="edit-menu-item-sale-mode-{{ $menuItem->id }}" name="sale_mode"
                    @class(['form-select', 'is-invalid' => session('openMenuItemDrawer') == $menuItem->id && $errors->has('sale_mode')]) data-sale-mode-select>
                    <option value="recipe" @selected(old('sale_mode', $menuItem->sale_mode ?? 'recipe') === 'recipe')>Use recipe ingredients</option>
                    <option value="stock_item" @selected(old('sale_mode', $menuItem->sale_mode ?? 'recipe') === 'stock_item')>Sell an inventory item directly</option>
                </select>
                @if (session('openMenuItemDrawer') == $menuItem->id) @error('sale_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                <div class="form-text">Choose direct stock for bottled drinks or other products sold as-is.</div>
            </div>
            <div class="sale-mode-stock-fields" data-sale-mode-stock-fields>
                <div class="mb-3">
                    <label for="edit-menu-item-stock-item-{{ $menuItem->id }}" class="form-label">Inventory item</label>
                    <select id="edit-menu-item-stock-item-{{ $menuItem->id }}" name="stock_ingredient_id"
                        @class(['form-select', 'is-invalid' => session('openMenuItemDrawer') == $menuItem->id && $errors->has('stock_ingredient_id')]) data-sale-stock-item>
                        <option value="">Select an inventory item</option>
                        @foreach ($stockItems as $stockItem)
                            <option value="{{ $stockItem->id }}" data-unit="{{ $stockItem->baseUnit?->abbreviation }}" @selected((string) old('stock_ingredient_id', $menuItem->stock_ingredient_id) === (string) $stockItem->id)>{{ $stockItem->code }} — {{ $stockItem->name }}{{ $stockItem->is_active ? '' : ' (inactive)' }}</option>
                        @endforeach
                    </select>
                    @if (session('openMenuItemDrawer') == $menuItem->id) @error('stock_ingredient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
                <div class="mb-3">
                    <label for="edit-menu-item-stock-quantity-{{ $menuItem->id }}" class="form-label">Stock used per sale</label>
                    <div class="input-group">
                        <input id="edit-menu-item-stock-quantity-{{ $menuItem->id }}" name="stock_quantity" type="number" min="0.001" step="0.001"
                            value="{{ old('stock_quantity', $menuItem->stock_quantity ?? '1') }}"
                            @class(['form-control', 'is-invalid' => session('openMenuItemDrawer') == $menuItem->id && $errors->has('stock_quantity')]) data-sale-stock-quantity>
                        <span class="input-group-text" data-sale-stock-unit>{{ $menuItem->stockIngredient?->baseUnit?->abbreviation ?? 'base units' }}</span>
                        @if (session('openMenuItemDrawer') == $menuItem->id) @error('stock_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                    </div>
                    <div class="form-text">For one bottle sold from stock tracked as bottles, enter 1.</div>
                </div>
            </div>
            <div class="mb-3">
                <label for="edit-menu-item-description-{{ $menuItem->id }}" class="form-label">Description <span class="text-secondary">(optional)</span></label>
                <textarea id="edit-menu-item-description-{{ $menuItem->id }}" name="description" rows="3"
                    @class(['form-control', 'is-invalid' => session('openMenuItemDrawer') == $menuItem->id && $errors->has('description')])>{{ old('description', $menuItem->description) }}</textarea>
                @if (session('openMenuItemDrawer') == $menuItem->id) @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <x-image-upload id="edit-menu-item-image-{{ $menuItem->id }}" label="Menu photo"
                hint="JPG, PNG, or WebP, up to 5 MB. A square 600 × 600 photo works best in the POS."
                :current-url="$menuItem->image_url" :current-alt="'Current photo for '.$menuItem->name"
                preview-alt="Selected menu item preview" removable remove-label="Remove current photo"
                :remove-checked="session('openMenuItemDrawer') == $menuItem->id && (old('remove_image') === '1' || old('remove_image') === 1)" />
            <input type="hidden" name="is_active" value="0">
            <div class="form-check">
                <input id="edit-menu-item-active-{{ $menuItem->id }}" name="is_active" type="checkbox" value="1" class="form-check-input"
                    @checked(old('is_active', $menuItem->is_active ? '1' : '0') === '1' || old('is_active') === 1)>
                <label class="form-check-label" for="edit-menu-item-active-{{ $menuItem->id }}">Active</label>
            </div>
        </form>

        <x-slot:footer>
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" form="editMenuItemForm-{{ $menuItem->id }}" class="btn btn-primary">Save changes</button>
            </div>
        </x-slot:footer>
    </x-drawer>
@endforeach
