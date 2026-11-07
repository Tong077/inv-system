<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsModelRecords;
use App\Models\SupplierPayment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SupplierPaymentController extends Controller
{
    use ListsModelRecords;

    public function index(Request $request): View
    {
        return $this->listing($request, 'supplier-payments', SupplierPayment::class, ['payment_method', 'reference_number', 'status'], ['supplier', 'purchaseOrder']);
    }
}
