<x-drawer id="createRecipeOtherCostDrawer" title="Add recipe cost" width="34rem">
    <form id="createRecipeOtherCostForm" method="POST" action="{{ route('recipe-other-costs.store') }}">
        @csrf
        <div class="mb-3">
            <label for="create-other-cost-version" class="form-label">Recipe version <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
            <select id="create-other-cost-version" name="recipe_version_id" required class="form-select @error('recipe_version_id') is-invalid @enderror">
                <option value="">Select a recipe version</option>
                @foreach ($recipeVersions as $version)
                    <option value="{{ $version->id }}" @selected((string) old('recipe_version_id', $recipeVersionId) === (string) $version->id)>
                        {{ $version->menuItem?->name ?? 'Menu item' }} · v{{ $version->version_number }}{{ $version->is_current ? ' (current)' : '' }}
                    </option>
                @endforeach
            </select>
            @error('recipe_version_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="create-other-cost-name" class="form-label">Cost name <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
            <input id="create-other-cost-name" name="name" type="text" maxlength="150" required value="{{ old('name') }}"
                class="form-control @error('name') is-invalid @enderror">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label for="create-other-cost-type" class="form-label">Cost type <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <select id="create-other-cost-type" name="cost_type" required class="form-select @error('cost_type') is-invalid @enderror">
                    <option value="fixed" @selected(old('cost_type', 'fixed') === 'fixed')>Fixed amount</option>
                    <option value="percentage" @selected(old('cost_type') === 'percentage')>Percentage</option>
                </select>
                @error('cost_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
                <label for="create-other-cost-amount" class="form-label">Amount <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <input id="create-other-cost-amount" name="amount" type="number" min="0" step="0.01" required value="{{ old('amount') }}"
                    class="form-control @error('amount') is-invalid @enderror">
                @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="mb-3">
            <label for="create-other-cost-notes" class="form-label">Notes <span class="text-secondary">(optional)</span></label>
            <textarea id="create-other-cost-notes" name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-text">Percentage costs are stored as a rate from 0 to 100 percent. They are not automatically applied to recipe totals yet.</div>
    </form>
    <x-slot:footer>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="submit" form="createRecipeOtherCostForm" class="btn btn-success">Save cost</button>
        </div>
    </x-slot:footer>
</x-drawer>
