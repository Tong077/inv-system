@foreach ($instructions as $instruction)
    <x-drawer id="editRecipeInstructionDrawer-{{ $instruction->id }}" title="Edit recipe instruction" width="36rem">
        <form id="editRecipeInstructionForm-{{ $instruction->id }}" method="POST" action="{{ route('recipe-instructions.update', $instruction) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="edit-instruction-version-{{ $instruction->id }}" class="form-label">Recipe version <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <select id="edit-instruction-version-{{ $instruction->id }}" name="recipe_version_id" required
                    @class(['form-select', 'is-invalid' => session('openRecipeInstructionDrawer') == $instruction->id && $errors->has('recipe_version_id')])>
                    <option value="">Select a recipe version</option>
                    @foreach ($recipeVersions as $version)
                        <option value="{{ $version->id }}" @selected((string) old('recipe_version_id', $instruction->recipe_version_id) === (string) $version->id)>
                            {{ $version->menuItem?->name ?? 'Menu item' }} · v{{ $version->version_number }}{{ $version->is_current ? ' (current)' : '' }}
                        </option>
                    @endforeach
                </select>
                @if (session('openRecipeInstructionDrawer') == $instruction->id) @error('recipe_version_id')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="row g-3 mb-3">
                <div class="col-sm-4">
                    <label for="edit-instruction-step-{{ $instruction->id }}" class="form-label">Step number <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                    <input id="edit-instruction-step-{{ $instruction->id }}" name="step_number" type="number" min="1" step="1" required
                        value="{{ old('step_number', $instruction->step_number) }}"
                        @class(['form-control', 'is-invalid' => session('openRecipeInstructionDrawer') == $instruction->id && $errors->has('step_number')])>
                    @if (session('openRecipeInstructionDrawer') == $instruction->id) @error('step_number')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
                <div class="col-sm-8">
                    <label for="edit-instruction-time-{{ $instruction->id }}" class="form-label">Preparation time in minutes <span class="text-secondary">(optional)</span></label>
                    <input id="edit-instruction-time-{{ $instruction->id }}" name="preparation_time_minutes" type="number" min="0" step="1"
                        value="{{ old('preparation_time_minutes', $instruction->preparation_time_minutes) }}"
                        @class(['form-control', 'is-invalid' => session('openRecipeInstructionDrawer') == $instruction->id && $errors->has('preparation_time_minutes')])>
                    @if (session('openRecipeInstructionDrawer') == $instruction->id) @error('preparation_time_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
            </div>
            <div class="mb-3">
                <label for="edit-instruction-text-{{ $instruction->id }}" class="form-label">Instruction <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <textarea id="edit-instruction-text-{{ $instruction->id }}" name="instruction" rows="5" required
                    @class(['form-control', 'is-invalid' => session('openRecipeInstructionDrawer') == $instruction->id && $errors->has('instruction')])>{{ old('instruction', $instruction->instruction) }}</textarea>
                @if (session('openRecipeInstructionDrawer') == $instruction->id) @error('instruction')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
        </form>
        <x-slot:footer>
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" form="editRecipeInstructionForm-{{ $instruction->id }}" class="btn btn-primary">Save changes</button>
            </div>
        </x-slot:footer>
    </x-drawer>
@endforeach
