<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsModelRecords;
use App\Models\StockWaste;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StockWasteController extends Controller
{
    use ListsModelRecords;

    public function index(Request $request): View
    {
        return $this->listing($request, 'stock-wastes', StockWaste::class, ['waste_number', 'reason', 'status'], ['stockLocation'], [
            'title' => 'No waste records yet',
            'description' => 'Stock waste records will appear here when they are created.',
        ]);
    }
}
