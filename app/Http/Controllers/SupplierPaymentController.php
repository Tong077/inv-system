<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Services\SupplierPaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class SupplierPaymentController extends Controller
{
    public function __construct(private readonly SupplierPaymentService $payments)
    {
    }

    public function index(Request $request): View|Response
    {
        $search = $request->string('search')->trim()->limit(100)->toString();
        $status = $request->query('status');
        $status = is_string($status) ? $status : null;
        $data = ['payments' => $this->payments->getAll($search, $status), 'suppliers' => Supplier::query()->orderBy('name')->get(['id', 'code', 'name', 'is_active']), 'orders' => PurchaseOrder::query()->whereIn('status', ['ordered', 'partially_received', 'received'])->with('supplier')->orderByDesc('order_date')->get(), 'search' => $search, 'status' => $status];
        return $request->header('X-Live-Search') === 'true' ? response()->view('supplier-payments.partials.results', $data) : view('supplier-payments.index', $data);
    }

    public function store(Request $request): RedirectResponse
    {
        $v = Validator::make($request->all(), $this->rules());
        if ($v->fails())
            return back()->withErrors($v)->withInput()->with('openSupplierPaymentDrawer', 'create');
        try {
            $this->payments->create($v->validated());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('openSupplierPaymentDrawer', 'create');
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'The payment could not be created. Please try again.');
        }
        return to_route('supplier-payments.index')->with('success', 'Supplier payment recorded.');
    }

    public function update(Request $request, SupplierPayment $supplierPayment): RedirectResponse
    {
        $v = Validator::make($request->all(), $this->rules($supplierPayment));
        if ($v->fails())
            return back()->withErrors($v)->withInput()->with('openSupplierPaymentDrawer', $supplierPayment->id);
        try {
            $this->payments->update($supplierPayment, $v->validated());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('openSupplierPaymentDrawer', $supplierPayment->id);
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'The payment could not be updated. Please try again.');
        }
        return to_route('supplier-payments.index')->with('success', 'Supplier payment updated.');
    }

    public function destroy(SupplierPayment $supplierPayment): RedirectResponse
    {
        try {
            $this->payments->delete($supplierPayment);
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', 'The payment could not be deleted. Please try again.');
        }
        return to_route('supplier-payments.index')->with('success', 'Supplier payment deleted.');
    }

    private function rules(?SupplierPayment $payment = null): array
    {
        return ['supplier_id' => ['required', 'integer', Rule::exists('suppliers', 'id')->whereNull('deleted_at')], 'purchase_order_id' => ['nullable', 'integer', 'exists:purchase_orders,id'], 'payment_date' => ['required', 'date'], 'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999.99', 'decimal:0,2'], 'payment_method' => ['required', 'string', 'max:50'], 'reference_number' => ['nullable', 'string', 'max:100'], 'status' => ['required', Rule::in(['pending', 'paid', 'cancelled'])]];
    }
}
