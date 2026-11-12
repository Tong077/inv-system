<?php

namespace App\Http\Controllers;

use App\Models\IngredientCategory;
use App\Services\IngredientCategoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class IngredientCategoryController extends Controller
{
    public function __construct(private readonly IngredientCategoryService $ingredientCategories) {}

    public function index(Request $request): View|Response
    {
        $search = $request->string('search')->trim()->limit(100)->toString();
        $status = $request->query('status');
        $status = is_string($status) ? $status : null;
        $categories = $this->ingredientCategories->getAllIngredientCategories($search, $status);
        $parentOptions = $this->ingredientCategories->getParentOptions();

        if ($request->header('X-Live-Search') === 'true') {
            return response()->view('ingredient-categories.partials.results', compact('categories', 'search', 'status', 'parentOptions'));
        }

        return view('ingredient-categories.index', compact('categories', 'search', 'status', 'parentOptions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openCategoryDrawer', 'create');
        }

        try {
            $this->ingredientCategories->createIngredientCategory($validator->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The category could not be created. Please try again.');
        }

        return to_route('ingredient-categories.index')->with('success', 'Ingredient category created.');
    }

    public function update(Request $request, IngredientCategory $ingredientCategory): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openCategoryDrawer', $ingredientCategory->id);
        }

        try {
            $this->ingredientCategories->updateIngredientCategory($ingredientCategory, $validator->validated());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput()->with('openCategoryDrawer', $ingredientCategory->id);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The category could not be updated. Please try again.');
        }

        return to_route('ingredient-categories.index')->with('success', 'Ingredient category updated.');
    }

    public function destroy(IngredientCategory $ingredientCategory): RedirectResponse
    {
        try {
            if (! $this->ingredientCategories->deleteIngredientCategory($ingredientCategory)) {
                return to_route('ingredient-categories.index')->with('warning', 'This category still has ingredients or child categories, so it cannot be deleted.');
            }
        } catch (Throwable $exception) {
            report($exception);

            return to_route('ingredient-categories.index')->with('error', 'The category could not be deleted. Please try again.');
        }

        return to_route('ingredient-categories.index')->with('success', 'Ingredient category deleted.');
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'integer', Rule::exists('ingredient_categories', 'id')->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
