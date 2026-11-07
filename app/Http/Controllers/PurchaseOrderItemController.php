<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsModelRecords;
use App\Models\PurchaseOrderItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PurchaseOrderItemController extends Controller
{
    use ListsModelRecords;

    public function index(Request $request): View
    {
        return $this->listing($request, 'purchase-order-items', PurchaseOrderItem::class, [], ['purchaseOrder', 'ingredient', 'unit']);
    }
}
