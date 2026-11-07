<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsModelRecords;
use App\Models\MenuItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    use ListsModelRecords;

    public function index(Request $request): View
    {
        return $this->listing($request, 'menu-items', MenuItem::class, ['code', 'name', 'description'], ['category']);
    }
}
