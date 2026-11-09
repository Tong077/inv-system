<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->query('status');

        $ingredients = Ingredient::query()
            ->with('baseUnit')
            ->withSum('stockBalances', 'quantity_on_hand')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['active', 'inactive'], true), function ($query) use ($status): void {
                $query->where('is_active', $status === 'active');
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('items.index', [
            'ingredients' => $ingredients,
            'records' => $ingredients,
            'search' => $search,
            'status' => $status,
        ]);
    }
}
