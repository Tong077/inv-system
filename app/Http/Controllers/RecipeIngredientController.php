<?php

namespace App\Http\Controllers;

use App\Models\RecipeIngredient;
use App\Services\RecipeIngredientService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class RecipeIngredientController extends Controller
{
    public function __construct(private readonly RecipeIngredientService $recipeIngredients) {}

    public function index(Request $request): View|Response
    {
        $search = $request->string('search')->trim()->limit(100)->toString();
        $recipeVersionId = $request->integer('recipe_version_id');
        $recipeVersionId = $recipeVersionId > 0 ? $recipeVersionId : null;
        $ingredients = $this->recipeIngredients->getAllRecipeIngredients($search, $recipeVersionId);
        $options = $this->recipeIngredients->getFormOptions();

        if ($request->header('X-Live-Search') === 'true') {
            return response()->view('recipe-ingredients.partials.results', [
                'recipeIngredients' => $ingredients,
                'search' => $search,
                'recipeVersionId' => $recipeVersionId,
                ...$options,
            ]);
        }

        return view('recipe-ingredients.index', [
            'recipeIngredients' => $ingredients,
            'search' => $search,
            'recipeVersionId' => $recipeVersionId,
            ...$options,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openRecipeIngredientDrawer', 'create');
        }

        try {
            $this->recipeIngredients->createRecipeIngredient($validator->validated());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput()->with('openRecipeIngredientDrawer', 'create');
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The recipe ingredient could not be created. Please try again.');
        }

        return to_route('recipe-ingredients.index')->with('success', 'Recipe ingredient added.');
    }

    public function update(Request $request, RecipeIngredient $recipeIngredient): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openRecipeIngredientDrawer', $recipeIngredient->id);
        }

        try {
            $this->recipeIngredients->updateRecipeIngredient($recipeIngredient, $validator->validated());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput()->with('openRecipeIngredientDrawer', $recipeIngredient->id);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The recipe ingredient could not be updated. Please try again.');
        }

        return to_route('recipe-ingredients.index')->with('success', 'Recipe ingredient updated.');
    }

    public function destroy(RecipeIngredient $recipeIngredient): RedirectResponse
    {
        try {
            $this->recipeIngredients->deleteRecipeIngredient($recipeIngredient);
        } catch (Throwable $exception) {
            report($exception);

            return to_route('recipe-ingredients.index')->with('error', 'The recipe ingredient could not be deleted. Please try again.');
        }

        return to_route('recipe-ingredients.index')->with('success', 'Recipe ingredient deleted.');
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(): array
    {
        return [
            'recipe_version_id' => ['required', 'integer', Rule::exists('recipe_versions', 'id')],
            'ingredient_id' => ['required', 'integer', Rule::exists('ingredients', 'id')->whereNull('deleted_at')],
            'quantity' => ['required', 'numeric', 'gt:0', 'max:999999999999.999', 'decimal:0,3'],
            'unit_id' => ['required', 'integer', Rule::exists('units', 'id')],
            'waste_percentage' => ['nullable', 'numeric', 'min:0', 'max:100', 'decimal:0,2'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
