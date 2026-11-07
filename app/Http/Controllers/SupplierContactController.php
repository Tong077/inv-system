<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsModelRecords;
use App\Models\SupplierContact;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SupplierContactController extends Controller
{
    use ListsModelRecords;

    public function index(Request $request): View
    {
        return $this->listing($request, 'supplier-contacts', SupplierContact::class, ['name', 'position', 'email', 'phone'], ['supplier']);
    }
}
