<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Services\IngredientService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class IngredientController extends Controller
{
    public function __construct(private readonly IngredientService $ingredients) {}

    public function index(Request $request): View|Response
    {
        $search = $request->string('search')->trim()->limit(100)->toString();
        $status = $request->query('status');
        $status = is_string($status) ? $status : null;
        $ingredients = $this->ingredients->getAllIngredients($search, $status);
        $options = $this->ingredients->getFormOptions();

        if ($request->header('X-Live-Search') === 'true') {
            return response()->view('items.partials.results', [
                'ingredients' => $ingredients,
                'search' => $search,
                'status' => $status,
                ...$options,
            ]);
        }

        return view('items.index', [
            'ingredients' => $ingredients,
            'search' => $search,
            'status' => $status,
            ...$options,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->normalizeInput($request);
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openIngredientDrawer', 'create');
        }

        try {
            $this->ingredients->createIngredient($validator->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The inventory item could not be created. Please try again.');
        }

        return to_route('items.index')->with('success', 'Inventory item created.');
    }

    public function update(Request $request, Ingredient $ingredient): RedirectResponse
    {
        $this->normalizeInput($request);
        $validator = Validator::make($request->all(), $this->rules($ingredient));

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openIngredientDrawer', $ingredient->id);
        }

        try {
            $this->ingredients->updateIngredient($ingredient, $validator->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The inventory item could not be updated. Please try again.');
        }

        return to_route('items.index')->with('success', 'Inventory item updated.');
    }

    public function destroy(Ingredient $ingredient): RedirectResponse
    {
        try {
            if (! $this->ingredients->deleteIngredient($ingredient)) {
                return to_route('items.index')->with('warning', 'This inventory item has stock or transaction history and cannot be deleted.');
            }
        } catch (Throwable $exception) {
            report($exception);

            return to_route('items.index')->with('error', 'The inventory item could not be deleted. Please try again.');
        }

        return to_route('items.index')->with('success', 'Inventory item deleted.');
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(?Ingredient $ingredient = null): array
    {
        $codeRule = Rule::unique('ingredients', 'code');

        if ($ingredient !== null) {
            $codeRule->ignore($ingredient);
        }

        return [
            'code' => ['required', 'string', 'max:50', $codeRule],
            'name' => ['required', 'string', 'max:200'],
            'ingredient_category_id' => ['nullable', 'integer', Rule::exists('ingredient_categories', 'id')->whereNull('deleted_at')],
            'base_unit_id' => ['required', 'integer', Rule::exists('units', 'id')->where('is_base_unit', true)],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['sometimes', 'boolean'],
            'minimum_stock' => ['required', 'numeric', 'min:0', 'max:999999999999.999', 'decimal:0,3'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    private function normalizeInput(Request $request): void
    {
        foreach (['code', 'name'] as $field) {
            $value = $request->input($field);

            if (is_string($value)) {
                $request->merge([$field => trim($field === 'code' ? strtoupper($value) : $value)]);
            }
        }
    }
}
