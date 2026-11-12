<x-drawer id="createMenuCategoryDrawer" title="Add menu category" width="32rem">
    <form id="createMenuCategoryForm" method="POST" action="{{ route('menu-categories.store') }}">
        @csrf
        <div class="mb-3">
            <label for="create-menu-category-name" class="form-label">
                Name <span class="text-danger" aria-hidden="true">*</span>
                <span class="visually-hidden">(required)</span>
            </label>
            <input id="create-menu-category-name" name="name" type="text" maxlength="150" required
                value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="create-menu-category-description" class="form-label">Description <span class="text-secondary">(optional)</span></label>
            <textarea id="create-menu-category-description" name="description" rows="3"
                class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <input type="hidden" name="is_active" value="0">
        <div class="form-check">
            <input id="create-menu-category-active" name="is_active" type="checkbox" value="1"
                class="form-check-input" @checked(old('is_active', '1') === '1' || old('is_active') === 1)>
            <label class="form-check-label" for="create-menu-category-active">Active</label>
        </div>
    </form>

    <x-slot:footer>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="submit" form="createMenuCategoryForm" class="btn btn-success">Save category</button>
        </div>
    </x-slot:footer>
</x-drawer>
