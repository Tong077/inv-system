@foreach ($categories as $category)
    <x-drawer id="editCategoryDrawer-{{ $category->id }}" title="Edit inventory category" width="32rem">
        <form id="editCategoryForm-{{ $category->id }}" method="POST"
            action="{{ route('ingredient-categories.update', $category) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="edit-parent-id-{{ $category->id }}" class="form-label">Parent category <span
                        class="text-secondary">(optional)</span></label>
                <select id="edit-parent-id-{{ $category->id }}" name="parent_id"
                    class="form-select @if (session('openCategoryDrawer') == $category->id) @error('parent_id') is-invalid @enderror @endif">
                    <option value="">No parent category</option>
                    @foreach ($parentOptions as $option)
                        @if ($option->id !== $category->id)
                            <option value="{{ $option->id }}" @selected((string) old('parent_id', $category->parent_id) === (string) $option->id)>{{ $option->name }}</option>
                        @endif
                    @endforeach
                </select>
                @if (session('openCategoryDrawer') == $category->id) @error('parent_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror @endif
            </div>
            <div class="mb-3">
                <label for="edit-name-{{ $category->id }}" class="form-label">
                    Name <span class="text-danger" aria-hidden="true">*</span>
                    <span class="visually-hidden">(required)</span>
                </label>
                <input id="edit-name-{{ $category->id }}" name="name" type="text" maxlength="255" required
                    value="{{ old('name', $category->name) }}"
                    class="form-control @if (session('openCategoryDrawer') == $category->id) @error('name') is-invalid @enderror @endif">
                @if (session('openCategoryDrawer') == $category->id) @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror @endif
            </div>
            <div class="mb-3">
                <label for="edit-description-{{ $category->id }}" class="form-label">Description <span
                        class="text-secondary">(optional)</span></label>
                <textarea id="edit-description-{{ $category->id }}" name="description" rows="3"
                    class="form-control @if (session('openCategoryDrawer') == $category->id) @error('description') is-invalid @enderror @endif">{{ old('description', $category->description) }}</textarea>
                @if (session('openCategoryDrawer') == $category->id) @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror @endif
            </div>
            <input type="hidden" name="is_active" value="0">
            <div class="form-check">
                <input id="edit-is-active-{{ $category->id }}" name="is_active" type="checkbox" value="1"
                    class="form-check-input" @checked(old('is_active', $category->is_active ? '1' : '0') === '1' || old('is_active') === 1)>
                <label class="form-check-label" for="edit-is-active-{{ $category->id }}">Active</label>
            </div>
        </form>

        <x-slot:footer>
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" form="editCategoryForm-{{ $category->id }}" class="btn btn-primary">Save changes</button>
            </div>
        </x-slot:footer>
    </x-drawer>
@endforeach
