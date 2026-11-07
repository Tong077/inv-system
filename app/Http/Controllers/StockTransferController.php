<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsModelRecords;
use App\Models\StockTransfer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StockTransferController extends Controller
{
    use ListsModelRecords;

    public function index(Request $request): View
    {
        return $this->listing($request, 'stock-transfers', StockTransfer::class, ['transfer_number', 'status', 'notes'], ['fromLocation', 'toLocation']);
    }
}
