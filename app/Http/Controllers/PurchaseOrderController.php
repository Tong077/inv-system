<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class PurchaseOrderController extends Controller
{
    public function __construct(private readonly PurchaseOrderService $orders) {}

    public function index(Request $request): View|Response
    {
        $search = $request->string('search')->trim()->limit(100)->toString();
        $status = $request->query('status');
        $status = is_string($status) ? $status : null;
        $orders = $this->orders->getAll($search, $status);
        $suppliers = $this->orders->getSuppliers();
        $data = compact('orders', 'suppliers', 'search', 'status');

        if ($request->header('X-Live-Search') === 'true') {
            return response()->view('purchase-orders.partials.results', $data);
        }

        return view('purchase-orders.index', $data);
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openPurchaseOrderDrawer', 'create');
        }

        try {
            $this->orders->create($validator->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The purchase order could not be created. Please try again.');
        }

        return to_route('purchase-orders.index')->with('success', 'Purchase order draft created.');
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules($purchaseOrder));

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openPurchaseOrderDrawer', $purchaseOrder->id);
        }

        try {
            $this->orders->update($purchaseOrder, $validator->validated());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput()->with('openPurchaseOrderDrawer', $purchaseOrder->id);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The purchase order could not be updated. Please try again.');
        }

        return to_route('purchase-orders.index')->with('success', 'Purchase order updated.');
    }

    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        try {
            if (! $this->orders->delete($purchaseOrder)) {
                return to_route('purchase-orders.index')->with('warning', 'Only draft purchase orders without receipts or payments can be deleted.');
            }
        } catch (Throwable $exception) {
            report($exception);

            return to_route('purchase-orders.index')->with('error', 'The purchase order could not be deleted. Please try again.');
        }

        return to_route('purchase-orders.index')->with('success', 'Purchase order deleted.');
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(?PurchaseOrder $order = null): array
    {
        $supplierRule = Rule::exists('suppliers', 'id')->whereNull('deleted_at');

        if ($order === null) {
            $supplierRule->where('is_active', true);
        }

        return [
            'supplier_id' => ['required', 'integer', $supplierRule],
            'order_date' => ['required', 'date'],
            'expected_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'tax_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'discount_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'status' => [$order === null ? 'prohibited' : 'required', Rule::in($order === null ? ['draft'] : ['draft', 'ordered', 'cancelled'])],
            'notes' => ['nullable', 'string'],
        ];
    }

}
