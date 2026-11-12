<?php

namespace App\Http\Controllers;

use App\Models\RecipeOtherCost;
use App\Services\RecipeOtherCostService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class RecipeOtherCostController extends Controller
{
    public function __construct(private readonly RecipeOtherCostService $costs) {}

    public function index(Request $request): View|Response
    {
        $search = $request->string('search')->trim()->limit(100)->toString();
        $recipeVersionId = $request->integer('recipe_version_id');
        $recipeVersionId = $recipeVersionId > 0 ? $recipeVersionId : null;
        $costs = $this->costs->getAll($search, $recipeVersionId);
        $recipeVersions = $this->costs->getRecipeVersions();

        $data = compact('costs', 'recipeVersions', 'search', 'recipeVersionId');

        if ($request->header('X-Live-Search') === 'true') {
            return response()->view('recipe-other-costs.partials.results', $data);
        }

        return view('recipe-other-costs.index', $data);
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules($request));

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openRecipeOtherCostDrawer', 'create');
        }

        try {
            $this->costs->create($validator->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The additional recipe cost could not be created. Please try again.');
        }

        return to_route('recipe-other-costs.index')->with('success', 'Additional recipe cost added.');
    }

    public function update(Request $request, RecipeOtherCost $recipeOtherCost): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules($request));

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openRecipeOtherCostDrawer', $recipeOtherCost->id);
        }

        try {
            $this->costs->update($recipeOtherCost, $validator->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The additional recipe cost could not be updated. Please try again.');
        }

        return to_route('recipe-other-costs.index')->with('success', 'Additional recipe cost updated.');
    }

    public function destroy(RecipeOtherCost $recipeOtherCost): RedirectResponse
    {
        try {
            $this->costs->delete($recipeOtherCost);
        } catch (Throwable $exception) {
            report($exception);

            return to_route('recipe-other-costs.index')->with('error', 'The additional recipe cost could not be deleted. Please try again.');
        }

        return to_route('recipe-other-costs.index')->with('success', 'Additional recipe cost deleted.');
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(Request $request): array
    {
        return [
            'recipe_version_id' => ['required', 'integer', Rule::exists('recipe_versions', 'id')],
            'name' => ['required', 'string', 'max:150'],
            'amount' => ['required', 'numeric', 'min:0', Rule::when($request->input('cost_type') === 'percentage', ['max:100']), 'max:9999999999.99', 'decimal:0,2'],
            'cost_type' => ['required', Rule::in(['fixed', 'percentage'])],
            'notes' => ['nullable', 'string'],
        ];
    }
}
