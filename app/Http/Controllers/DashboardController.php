<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\StockLocation;
use App\Models\StockWaste;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'ingredientCount' => Ingredient::count(),
            'locationCount' => StockLocation::where('is_active', true)->count(),
            'postedWasteCount' => StockWaste::where('status', 'posted')->count(),
        ]);
    }
}
