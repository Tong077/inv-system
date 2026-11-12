@foreach ($categories as $category)
    <x-drawer id="editMenuCategoryDrawer-{{ $category->id }}" title="Edit menu category" width="32rem">
        <form id="editMenuCategoryForm-{{ $category->id }}" method="POST"
            action="{{ route('menu-categories.update', $category) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="edit-menu-category-name-{{ $category->id }}" class="form-label">
                    Name <span class="text-danger" aria-hidden="true">*</span>
                    <span class="visually-hidden">(required)</span>
                </label>
                <input id="edit-menu-category-name-{{ $category->id }}" name="name" type="text" maxlength="150" required
                    value="{{ old('name', $category->name) }}"
                    @class(['form-control', 'is-invalid' => session('openMenuCategoryDrawer') == $category->id && $errors->has('name')])>
                @if (session('openMenuCategoryDrawer') == $category->id)
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @endif
            </div>
            <div class="mb-3">
                <label for="edit-menu-category-description-{{ $category->id }}" class="form-label">Description <span class="text-secondary">(optional)</span></label>
                <textarea id="edit-menu-category-description-{{ $category->id }}" name="description" rows="3"
                    @class(['form-control', 'is-invalid' => session('openMenuCategoryDrawer') == $category->id && $errors->has('description')])>{{ old('description', $category->description) }}</textarea>
                @if (session('openMenuCategoryDrawer') == $category->id)
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @endif
            </div>
            <input type="hidden" name="is_active" value="0">
            <div class="form-check">
                <input id="edit-menu-category-active-{{ $category->id }}" name="is_active" type="checkbox" value="1"
                    class="form-check-input" @checked(old('is_active', $category->is_active ? '1' : '0') === '1' || old('is_active') === 1)>
                <label class="form-check-label" for="edit-menu-category-active-{{ $category->id }}">Active</label>
            </div>
        </form>

        <x-slot:footer>
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" form="editMenuCategoryForm-{{ $category->id }}" class="btn btn-primary">Save changes</button>
            </div>
        </x-slot:footer>
    </x-drawer>
@endforeach
