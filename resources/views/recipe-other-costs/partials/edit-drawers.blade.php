@foreach ($costs as $cost)
    <x-drawer id="editRecipeOtherCostDrawer-{{ $cost->id }}" title="Edit recipe cost" width="34rem">
        <form id="editRecipeOtherCostForm-{{ $cost->id }}" method="POST" action="{{ route('recipe-other-costs.update', $cost) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="edit-other-cost-version-{{ $cost->id }}" class="form-label">Recipe version <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <select id="edit-other-cost-version-{{ $cost->id }}" name="recipe_version_id" required
                    @class(['form-select', 'is-invalid' => session('openRecipeOtherCostDrawer') == $cost->id && $errors->has('recipe_version_id')])>
                    <option value="">Select a recipe version</option>
                    @foreach ($recipeVersions as $version)
                        <option value="{{ $version->id }}" @selected((string) old('recipe_version_id', $cost->recipe_version_id) === (string) $version->id)>
                            {{ $version->menuItem?->name ?? 'Menu item' }} · v{{ $version->version_number }}{{ $version->is_current ? ' (current)' : '' }}
                        </option>
                    @endforeach
                </select>
                @if (session('openRecipeOtherCostDrawer') == $cost->id) @error('recipe_version_id')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="mb-3">
                <label for="edit-other-cost-name-{{ $cost->id }}" class="form-label">Cost name <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                <input id="edit-other-cost-name-{{ $cost->id }}" name="name" type="text" maxlength="150" required value="{{ old('name', $cost->name) }}"
                    @class(['form-control', 'is-invalid' => session('openRecipeOtherCostDrawer') == $cost->id && $errors->has('name')])>
                @if (session('openRecipeOtherCostDrawer') == $cost->id) @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label for="edit-other-cost-type-{{ $cost->id }}" class="form-label">Cost type <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                    <select id="edit-other-cost-type-{{ $cost->id }}" name="cost_type" required
                        @class(['form-select', 'is-invalid' => session('openRecipeOtherCostDrawer') == $cost->id && $errors->has('cost_type')])>
                        <option value="fixed" @selected(old('cost_type', $cost->cost_type) === 'fixed')>Fixed amount</option>
                        <option value="percentage" @selected(old('cost_type', $cost->cost_type) === 'percentage')>Percentage</option>
                    </select>
                    @if (session('openRecipeOtherCostDrawer') == $cost->id) @error('cost_type')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
                <div class="col-sm-6">
                    <label for="edit-other-cost-amount-{{ $cost->id }}" class="form-label">Amount <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(required)</span></label>
                    <input id="edit-other-cost-amount-{{ $cost->id }}" name="amount" type="number" min="0" step="0.01" required value="{{ old('amount', $cost->amount) }}"
                        @class(['form-control', 'is-invalid' => session('openRecipeOtherCostDrawer') == $cost->id && $errors->has('amount')])>
                    @if (session('openRecipeOtherCostDrawer') == $cost->id) @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
            </div>
            <div class="mb-3">
                <label for="edit-other-cost-notes-{{ $cost->id }}" class="form-label">Notes <span class="text-secondary">(optional)</span></label>
                <textarea id="edit-other-cost-notes-{{ $cost->id }}" name="notes" rows="3"
                    @class(['form-control', 'is-invalid' => session('openRecipeOtherCostDrawer') == $cost->id && $errors->has('notes')])>{{ old('notes', $cost->notes) }}</textarea>
                @if (session('openRecipeOtherCostDrawer') == $cost->id) @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="form-text">Percentage costs are stored as a rate from 0 to 100 percent. They are not automatically applied to recipe totals yet.</div>
        </form>
        <x-slot:footer>
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" form="editRecipeOtherCostForm-{{ $cost->id }}" class="btn btn-primary">Save changes</button>
            </div>
        </x-slot:footer>
    </x-drawer>
@endforeach
