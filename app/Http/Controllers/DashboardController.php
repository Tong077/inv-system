<?php

namespace App\Http\Controllers;

use App\Services\InventoryReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly InventoryReportService $reports) {}

    public function __invoke(Request $request): View
    {
        $ingredientId = $request->integer('ingredient_id');
        return view('dashboard', $this->reports->dashboard($ingredientId > 0 ? $ingredientId : null));
    }
}
