<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsModelRecords;
use App\Models\StockBalance;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StockBalanceController extends Controller
{
    use ListsModelRecords;

    public function index(Request $request): View
    {
        return $this->listing($request, 'stock-balances', StockBalance::class, [], ['ingredient.baseUnit', 'stockLocation']);
    }
}
