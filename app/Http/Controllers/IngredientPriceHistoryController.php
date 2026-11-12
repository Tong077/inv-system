<?php

namespace App\Http\Controllers;

use App\Models\IngredientPriceHistory;
use App\Services\IngredientPriceHistoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class IngredientPriceHistoryController extends Controller
{
    public function __construct(private readonly IngredientPriceHistoryService $prices) {}

    public function index(Request $request): View|Response
    {
        $search = $request->string('search')->trim()->limit(100)->toString();
        $ingredientId = $request->integer('ingredient_id');
        $ingredientId = $ingredientId > 0 ? $ingredientId : null;
        $histories = $this->prices->getAll($search, $ingredientId);
        $options = $this->prices->getFormOptions();
        $data = ['histories' => $histories, 'search' => $search, 'ingredientId' => $ingredientId, ...$options];

        if ($request->header('X-Live-Search') === 'true') {
            return response()->view('ingredient-price-histories.partials.results', $data);
        }

        return view('ingredient-price-histories.index', $data);
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openIngredientPriceDrawer', 'create');
        }

        try {
            $this->prices->create($validator->validated());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput()->with('openIngredientPriceDrawer', 'create');
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The ingredient price could not be created. Please try again.');
        }

        return to_route('ingredient-price-histories.index')->with('success', 'Ingredient price recorded.');
    }

    public function update(Request $request, IngredientPriceHistory $ingredientPriceHistory): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openIngredientPriceDrawer', $ingredientPriceHistory->id);
        }

        try {
            $this->prices->update($ingredientPriceHistory, $validator->validated());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput()->with('openIngredientPriceDrawer', $ingredientPriceHistory->id);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The ingredient price could not be updated. Please try again.');
        }

        return to_route('ingredient-price-histories.index')->with('success', 'Ingredient price updated.');
    }

    public function destroy(IngredientPriceHistory $ingredientPriceHistory): RedirectResponse
    {
        try {
            $this->prices->delete($ingredientPriceHistory);
        } catch (Throwable $exception) {
            report($exception);

            return to_route('ingredient-price-histories.index')->with('error', 'The ingredient price could not be deleted. Please try again.');
        }

        return to_route('ingredient-price-histories.index')->with('success', 'Ingredient price deleted.');
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(): array
    {
        return [
            'ingredient_id' => ['required', 'integer', Rule::exists('ingredients', 'id')->whereNull('deleted_at')],
            'supplier_id' => ['nullable', 'integer', Rule::exists('suppliers', 'id')->whereNull('deleted_at')],
            'package_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'package_quantity' => ['required', 'numeric', 'gt:0', 'max:999999999999.999', 'decimal:0,3'],
            'package_unit_id' => ['required', 'integer', Rule::exists('units', 'id')],
            'effective_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
