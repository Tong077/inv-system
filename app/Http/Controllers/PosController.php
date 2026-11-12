<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class PosController extends Controller
{
    public function __construct(private readonly SaleService $sales) {}

    public function index(): View
    {
        return view('pos.index', $this->sales->getPosOptions());
    }

    public function checkout(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'stock_location_id' => ['required', 'integer', Rule::exists('stock_locations', 'id')->where('is_active', true)],
            'items' => ['required', 'array'],
            'items.*' => ['nullable', 'numeric', 'min:0', 'max:999999.999', 'decimal:0,3'],
            'payment_method' => ['required', Rule::in(['cash', 'card', 'qr', 'other'])],
            'amount_tendered' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
        ]);

        try {
            $sale = $this->sales->checkout($data);
        } catch (Throwable $exception) {
            if ($exception instanceof \Illuminate\Validation\ValidationException) {
                throw $exception;
            }

            report($exception);

            return back()->withInput()->with('error', 'The sale could not be completed. Please try again.');
        }

        return to_route('sales.show', $sale)->with('success', 'Sale completed and stock updated from recipes or direct inventory items.');
    }
}
