<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsModelRecords;
use App\Models\StockCount;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StockCountController extends Controller
{
    use ListsModelRecords;

    public function index(Request $request): View
    {
        return $this->listing($request, 'stock-counts', StockCount::class, ['count_number', 'status', 'notes'], ['stockLocation']);
    }
}
