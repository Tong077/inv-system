<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsModelRecords;
use App\Models\StockWasteItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StockWasteItemController extends Controller
{
    use ListsModelRecords;

    public function index(Request $request): View
    {
        return $this->listing($request, 'stock-waste-items', StockWasteItem::class, ['notes'], ['stockWaste', 'ingredient', 'unit']);
    }
}
