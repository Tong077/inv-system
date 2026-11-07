<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsModelRecords;
use App\Models\IngredientPriceHistory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class IngredientPriceHistoryController extends Controller
{
    use ListsModelRecords;

    public function index(Request $request): View
    {
        return $this->listing($request, 'ingredient-price-histories', IngredientPriceHistory::class, ['notes'], ['ingredient', 'supplier', 'packageUnit']);
    }
}
