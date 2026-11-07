<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsModelRecords;
use App\Models\GoodsReceipt;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class GoodsReceiptController extends Controller
{
    use ListsModelRecords;

    public function index(Request $request): View
    {
        return $this->listing($request, 'goods-receipts', GoodsReceipt::class, ['receipt_number', 'status', 'notes'], ['supplier', 'purchaseOrder', 'stockLocation']);
    }
}
