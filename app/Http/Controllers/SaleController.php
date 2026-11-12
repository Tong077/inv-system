<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function __construct(private readonly SaleService $sales) {}

    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->limit(100)->toString();

        return view('sales.index', [
            'sales' => $this->sales->getSales($search),
            'search' => $search,
        ]);
    }

    public function show(Sale $sale): View
    {
        return view('sales.show', ['sale' => $this->sales->findSale($sale)]);
    }
}
