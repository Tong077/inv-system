<?php

namespace App\Http\Controllers;

use App\Models\RecipeInstruction;
use App\Services\RecipeInstructionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class RecipeInstructionController extends Controller
{
    public function __construct(private readonly RecipeInstructionService $instructions) {}

    public function index(Request $request): View|Response
    {
        $search = $request->string('search')->trim()->limit(100)->toString();
        $recipeVersionId = $request->integer('recipe_version_id');
        $recipeVersionId = $recipeVersionId > 0 ? $recipeVersionId : null;
        $instructions = $this->instructions->getAll($search, $recipeVersionId);
        $recipeVersions = $this->instructions->getRecipeVersions();

        $data = compact('instructions', 'recipeVersions', 'search', 'recipeVersionId');

        if ($request->header('X-Live-Search') === 'true') {
            return response()->view('recipe-instructions.partials.results', $data);
        }

        return view('recipe-instructions.index', $data);
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules($request));

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openRecipeInstructionDrawer', 'create');
        }

        try {
            $this->instructions->create($validator->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The recipe instruction could not be created. Please try again.');
        }

        return to_route('recipe-instructions.index')->with('success', 'Recipe instruction added.');
    }

    public function update(Request $request, RecipeInstruction $recipeInstruction): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules($request, $recipeInstruction));

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openRecipeInstructionDrawer', $recipeInstruction->id);
        }

        try {
            $this->instructions->update($recipeInstruction, $validator->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The recipe instruction could not be updated. Please try again.');
        }

        return to_route('recipe-instructions.index')->with('success', 'Recipe instruction updated.');
    }

    public function destroy(RecipeInstruction $recipeInstruction): RedirectResponse
    {
        try {
            $this->instructions->delete($recipeInstruction);
        } catch (Throwable $exception) {
            report($exception);

            return to_route('recipe-instructions.index')->with('error', 'The recipe instruction could not be deleted. Please try again.');
        }

        return to_route('recipe-instructions.index')->with('success', 'Recipe instruction deleted.');
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(Request $request, ?RecipeInstruction $recipeInstruction = null): array
    {
        $stepRule = Rule::unique('recipe_instructions', 'step_number')
            ->where('recipe_version_id', $request->input('recipe_version_id'));

        if ($recipeInstruction !== null) {
            $stepRule->ignore($recipeInstruction);
        }

        return [
            'recipe_version_id' => ['required', 'integer', Rule::exists('recipe_versions', 'id')],
            'step_number' => ['required', 'integer', 'min:1', $stepRule],
            'instruction' => ['required', 'string'],
            'preparation_time_minutes' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
        ];
    }
}
