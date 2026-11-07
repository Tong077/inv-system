<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsModelRecords;
use App\Models\StockLocation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StockLocationController extends Controller
{
    use ListsModelRecords;

    public function index(Request $request): View
    {
        return $this->listing($request, 'stock-locations', StockLocation::class, ['code', 'name', 'location_type'], [], [
            'title' => 'No stock locations yet',
            'description' => 'Your storage locations will appear here when they are set up.',
        ]);
    }
}
