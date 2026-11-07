<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsModelRecords;
use App\Models\PurchaseOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    use ListsModelRecords;

    public function index(Request $request): View
    {
        return $this->listing($request, 'purchase-orders', PurchaseOrder::class, ['po_number', 'status', 'notes'], ['supplier']);
    }
}
