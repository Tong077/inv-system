<?php

namespace App\Http\Controllers;

use App\Services\IngredientCategoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class IngredientCategoryController extends Controller
{
    public function __construct(private readonly IngredientCategoryService $ingredientCategories) {}

    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->limit(100)->toString();
        $status = $request->query('status');
        $status = is_string($status) ? $status : null;
        $categories = $this->ingredientCategories->getAllIngredientCategories($search, $status);

        return view('ingredient-categories.index', compact('categories', 'search', 'status'));
    }
}
