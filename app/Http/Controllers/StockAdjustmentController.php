<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsModelRecords;
use App\Models\StockAdjustment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StockAdjustmentController extends Controller
{
    use ListsModelRecords;

    public function index(Request $request): View
    {
        return $this->listing($request, 'stock-adjustments', StockAdjustment::class, ['adjustment_number', 'reason', 'status', 'notes'], ['stockLocation']);
    }
}
