<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsModelRecords;
use App\Models\StockAdjustmentItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StockAdjustmentItemController extends Controller
{
    use ListsModelRecords;

    public function index(Request $request): View
    {
        return $this->listing($request, 'stock-adjustment-items', StockAdjustmentItem::class, [], ['stockAdjustment', 'ingredient']);
    }
}
