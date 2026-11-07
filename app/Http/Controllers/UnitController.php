<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsModelRecords;
use App\Models\Unit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    use ListsModelRecords;

    public function index(Request $request): View
    {
        return $this->listing($request, 'units', Unit::class, ['name', 'abbreviation', 'dimension']);
    }
}
