<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsModelRecords;
use App\Models\RecipeVersion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class RecipeVersionController extends Controller
{
    use ListsModelRecords;

    public function index(Request $request): View
    {
        return $this->listing($request, 'recipe-versions', RecipeVersion::class, [], ['menuItem', 'yieldUnit']);
    }
}
