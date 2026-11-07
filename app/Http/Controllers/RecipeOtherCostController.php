<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsModelRecords;
use App\Models\RecipeOtherCost;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class RecipeOtherCostController extends Controller
{
    use ListsModelRecords;

    public function index(Request $request): View
    {
        return $this->listing($request, 'recipe-other-costs', RecipeOtherCost::class, ['name', 'notes', 'cost_type'], ['recipeVersion.menuItem']);
    }
}
