<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsModelRecords;
use App\Models\RecipeIngredient;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class RecipeIngredientController extends Controller
{
    use ListsModelRecords;

    public function index(Request $request): View
    {
        return $this->listing($request, 'recipe-ingredients', RecipeIngredient::class, ['notes'], ['recipeVersion.menuItem', 'ingredient', 'unit']);
    }
}
