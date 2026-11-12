<x-drawer id="createCategoryDrawer" title="Add inventory category" width="32rem">
    <form id="createCategoryForm" method="POST" action="{{ route('ingredient-categories.store') }}">
        @csrf
        <div class="mb-3">
            <label for="create-parent-id" class="form-label">Parent category <span
                    class="text-secondary">(optional)</span></label>
            <select id="create-parent-id" name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                <option value="">No parent category</option>
                @foreach ($parentOptions as $option)
                    <option value="{{ $option->id }}" @selected((string) old('parent_id') === (string) $option->id)>
                        {{ $option->name }}
                    </option>
                @endforeach
            </select>
            @error('parent_id')
            <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label for="create-name" class="form-label">
                Name <span class="text-danger" aria-hidden="true">*</span>
                <span class="visually-hidden">(required)</span>
            </label>
            <input id="create-name" name="name" type="text" maxlength="255" required value="{{ old('name') }}"
                class="form-control @error('name') is-invalid @enderror">
            @error('name')
            <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label for="create-description" class="form-label">Description <span
                    class="text-secondary">(optional)</span></label>
            <textarea id="create-description" name="description" rows="3"
                class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
            @error('description')
            <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <input type="hidden" name="is_active" value="0">
        <div class="form-check">
            <input id="create-is-active" name="is_active" type="checkbox" value="1" class="form-check-input"
                @checked(old('is_active', '1') === '1' || old('is_active') === 1)>
            <label class="form-check-label" for="create-is-active">Active</label>
        </div>
    </form>

    <x-slot:footer>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="submit" form="createCategoryForm" class="btn btn-success">Save category</button>
        </div>
    </x-slot:footer>
</x-drawer>

