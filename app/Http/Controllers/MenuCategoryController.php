<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsModelRecords;
use App\Models\MenuCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MenuCategoryController extends Controller
{
    use ListsModelRecords;

    public function index(Request $request): View
    {
        return $this->listing($request, 'menu-categories', MenuCategory::class, ['name', 'description']);
    }
}
