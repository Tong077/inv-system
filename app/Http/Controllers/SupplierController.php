<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsModelRecords;
use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use ListsModelRecords;

    public function index(Request $request): View
    {
        return $this->listing($request, 'suppliers', Supplier::class, ['code', 'name', 'email']);
    }
}
