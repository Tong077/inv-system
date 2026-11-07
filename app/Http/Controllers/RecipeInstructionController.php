<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsModelRecords;
use App\Models\RecipeInstruction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class RecipeInstructionController extends Controller
{
    use ListsModelRecords;

    public function index(Request $request): View
    {
        return $this->listing($request, 'recipe-instructions', RecipeInstruction::class, ['instruction'], ['recipeVersion.menuItem']);
    }
}
