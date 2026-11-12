<x-drawer id="createRecipeInstructionDrawer" title="Add recipe instruction" width="36rem">
    <form id="createRecipeInstructionForm" method="POST" action="{{ route('recipe-instructions.store') }}">
        @csrf
        <div class="mb-3">
            <label for="create-instruction-version" class="form-label">Recipe version <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
            <select id="create-instruction-version" name="recipe_version_id" required class="form-select @error('recipe_version_id') is-invalid @enderror">
                <option value="">Select a recipe version</option>
                @foreach ($recipeVersions as $version)
                    <option value="{{ $version->id }}" @selected((string) old('recipe_version_id', $recipeVersionId) === (string) $version->id)>
                        {{ $version->menuItem?->name ?? 'Menu item' }} · v{{ $version->version_number }}{{ $version->is_current ? ' (current)' : '' }}
                    </option>
                @endforeach
            </select>
            @error('recipe_version_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="row g-3 mb-3">
            <div class="col-sm-4">
                <label for="create-instruction-step" class="form-label">Step number <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <input id="create-instruction-step" name="step_number" type="number" min="1" step="1" required value="{{ old('step_number') }}"
                    class="form-control @error('step_number') is-invalid @enderror">
                @error('step_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-8">
                <label for="create-instruction-time" class="form-label">Preparation time in minutes <span class="text-secondary">(optional)</span></label>
                <input id="create-instruction-time" name="preparation_time_minutes" type="number" min="0" step="1" value="{{ old('preparation_time_minutes') }}"
                    class="form-control @error('preparation_time_minutes') is-invalid @enderror">
                @error('preparation_time_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="mb-3">
            <label for="create-instruction-text" class="form-label">Instruction <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
            <textarea id="create-instruction-text" name="instruction" rows="5" required class="form-control @error('instruction') is-invalid @enderror">{{ old('instruction') }}</textarea>
            @error('instruction')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </form>
    <x-slot:footer>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="submit" form="createRecipeInstructionForm" class="btn btn-success">Save step</button>
        </div>
    </x-slot:footer>
</x-drawer>
