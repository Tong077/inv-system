<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Services\MenuItemService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class MenuItemController extends Controller
{
    public function __construct(private readonly MenuItemService $menuItems) {}

    public function index(Request $request): View|Response
    {
        $search = $request->string('search')->trim()->limit(100)->toString();
        $status = $request->query('status');
        $status = is_string($status) ? $status : null;
        $menuItems = $this->menuItems->getAllMenuItems($search, $status);
        $categories = $this->menuItems->getCategoryOptions();
        $stockItems = $this->menuItems->getStockItemOptions();

        if ($request->header('X-Live-Search') === 'true') {
            return response()->view('menu-items.partials.results', compact('menuItems', 'categories', 'stockItems', 'search', 'status'));
        }

        return view('menu-items.index', compact('menuItems', 'categories', 'stockItems', 'search', 'status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->normalizeInput($request);
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openMenuItemDrawer', 'create');
        }

        try {
            $this->menuItems->createMenuItem($validator->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The menu item could not be created. Please try again.');
        }

        return to_route('menu-items.index')->with('success', 'Menu item created.');
    }

    public function update(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $this->normalizeInput($request);
        $validator = Validator::make($request->all(), $this->rules($menuItem));

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openMenuItemDrawer', $menuItem->id);
        }

        try {
            $this->menuItems->updateMenuItem($menuItem, $validator->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The menu item could not be updated. Please try again.');
        }

        return to_route('menu-items.index')->with('success', 'Menu item updated.');
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        try {
            if (! $this->menuItems->deleteMenuItem($menuItem)) {
                return to_route('menu-items.index')->with('warning', 'This menu item has recipe versions and cannot be deleted.');
            }
        } catch (Throwable $exception) {
            report($exception);

            return to_route('menu-items.index')->with('error', 'The menu item could not be deleted. Please try again.');
        }

        return to_route('menu-items.index')->with('success', 'Menu item deleted.');
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(?MenuItem $menuItem = null): array
    {
        $saleMode = request()->input('sale_mode', 'recipe');
        $codeRule = Rule::unique('menu_items', 'code');
        $barcodeRule = Rule::unique('menu_items', 'barcode');

        if ($menuItem !== null) {
            $codeRule->ignore($menuItem);
            $barcodeRule->ignore($menuItem);
        }

        return [
            'menu_category_id' => ['nullable', 'integer', Rule::exists('menu_categories', 'id')->whereNull('deleted_at')],
            'code' => ['required', 'string', 'max:50', $codeRule],
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['sometimes', 'boolean'],
            'barcode' => ['nullable', 'string', 'max:100', $barcodeRule],
            'selling_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'sale_mode' => ['required', Rule::in(['recipe', 'stock_item'])],
            'stock_ingredient_id' => [Rule::requiredIf($saleMode === 'stock_item'), 'nullable', 'integer', Rule::exists('ingredients', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'stock_quantity' => [Rule::requiredIf($saleMode === 'stock_item'), 'nullable', 'numeric', 'gt:0', 'max:999999999999.999', 'decimal:0,3'],
            'currency' => ['required', 'string', 'max:10'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    private function normalizeInput(Request $request): void
    {
        if (! $request->filled('sale_mode')) {
            $request->merge(['sale_mode' => 'recipe']);
        }

        foreach (['code', 'name', 'currency', 'barcode'] as $field) {
            $value = $request->input($field);

            if (is_string($value)) {
                $value = trim($value);
                $request->merge([$field => $field === 'code' || $field === 'currency' ? strtoupper($value) : $value]);
            }
        }
    }
}
