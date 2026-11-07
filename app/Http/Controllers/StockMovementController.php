<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsModelRecords;
use App\Models\StockMovement;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    use ListsModelRecords;

    public function index(Request $request): View
    {
        return $this->listing($request, 'stock-movements', StockMovement::class, ['movement_type', 'reference_type', 'notes'], ['ingredient', 'stockLocation']);
    }
}
