<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use App\Services\MenuCategoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Throwable;

class MenuCategoryController extends Controller
{
    public function __construct(private readonly MenuCategoryService $menuCategories) {}

    public function index(Request $request): View|Response
    {
        $search = $request->string('search')->trim()->limit(100)->toString();
        $status = $request->query('status');
        $status = is_string($status) ? $status : null;
        $categories = $this->menuCategories->getAllMenuCategories($search, $status);

        if ($request->header('X-Live-Search') === 'true') {
            return response()->view('menu-categories.partials.results', compact('categories', 'search', 'status'));
        }

        return view('menu-categories.index', compact('categories', 'search', 'status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openMenuCategoryDrawer', 'create');
        }

        try {
            $this->menuCategories->createMenuCategory($validator->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The menu category could not be created. Please try again.');
        }

        return to_route('menu-categories.index')->with('success', 'Menu category created.');
    }

    public function update(Request $request, MenuCategory $menuCategory): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openMenuCategoryDrawer', $menuCategory->id);
        }

        try {
            $this->menuCategories->updateMenuCategory($menuCategory, $validator->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The menu category could not be updated. Please try again.');
        }

        return to_route('menu-categories.index')->with('success', 'Menu category updated.');
    }

    public function destroy(MenuCategory $menuCategory): RedirectResponse
    {
        try {
            if (! $this->menuCategories->deleteMenuCategory($menuCategory)) {
                return to_route('menu-categories.index')->with('warning', 'This category still contains menu items and cannot be deleted.');
            }
        } catch (Throwable $exception) {
            report($exception);

            return to_route('menu-categories.index')->with('error', 'The menu category could not be deleted. Please try again.');
        }

        return to_route('menu-categories.index')->with('success', 'Menu category deleted.');
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
