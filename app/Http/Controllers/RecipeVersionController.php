<?php

namespace App\Http\Controllers;

use App\Models\RecipeVersion;
use App\Services\RecipeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class RecipeVersionController extends Controller
{
    public function __construct(private readonly RecipeService $recipes) {}

    public function index(Request $request): View|Response
    {
        $search = $request->string('search')->trim()->limit(100)->toString();
        $current = $request->query('current');
        $current = is_string($current) ? $current : null;
        $recipeVersions = $this->recipes->getAllRecipeVersions($search, $current);
        $options = $this->recipes->getFormOptions();

        if ($request->header('X-Live-Search') === 'true') {
            return response()->view('recipe-versions.partials.results', [
                'recipeVersions' => $recipeVersions,
                'search' => $search,
                'current' => $current,
                ...$options,
            ]);
        }

        return view('recipe-versions.index', [
            'recipeVersions' => $recipeVersions,
            'search' => $search,
            'current' => $current,
            ...$options,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openRecipeVersionDrawer', 'create');
        }

        try {
            $this->recipes->createRecipeVersion($validator->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The recipe version could not be created. Please try again.');
        }

        return to_route('recipe-versions.index')->with('success', 'Recipe version created.');
    }

    public function update(Request $request, RecipeVersion $recipeVersion): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openRecipeVersionDrawer', $recipeVersion->id);
        }

        try {
            $this->recipes->updateRecipeVersion($recipeVersion, $validator->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The recipe version could not be updated. Please try again.');
        }

        return to_route('recipe-versions.index')->with('success', 'Recipe version updated.');
    }

    public function destroy(RecipeVersion $recipeVersion): RedirectResponse
    {
        try {
            if (! $this->recipes->deleteRecipeVersion($recipeVersion)) {
                return to_route('recipe-versions.index')->with('warning', 'This recipe version has ingredients, instructions, or additional costs and cannot be deleted.');
            }
        } catch (Throwable $exception) {
            report($exception);

            return to_route('recipe-versions.index')->with('error', 'The recipe version could not be deleted. Please try again.');
        }

        return to_route('recipe-versions.index')->with('success', 'Recipe version deleted.');
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(): array
    {
        return [
            'menu_item_id' => ['required', 'integer', Rule::exists('menu_items', 'id')->whereNull('deleted_at')],
            'yield_quantity' => ['required', 'numeric', 'gt:0', 'max:999999999999.999', 'decimal:0,3'],
            'yield_unit_id' => ['nullable', 'integer', Rule::exists('units', 'id')],
            'is_current' => ['required', 'boolean'],
            'effective_from' => ['nullable', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
