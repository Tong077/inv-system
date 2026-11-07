<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsModelRecords;
use App\Models\StockCountItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StockCountItemController extends Controller
{
    use ListsModelRecords;

    public function index(Request $request): View
    {
        return $this->listing($request, 'stock-count-items', StockCountItem::class, [], ['stockCount', 'ingredient']);
    }
}
