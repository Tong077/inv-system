<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsModelRecords;
use App\Models\StockTransferItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StockTransferItemController extends Controller
{
    use ListsModelRecords;

    public function index(Request $request): View
    {
        return $this->listing($request, 'stock-transfer-items', StockTransferItem::class, [], ['stockTransfer', 'ingredient', 'unit']);
    }
}
