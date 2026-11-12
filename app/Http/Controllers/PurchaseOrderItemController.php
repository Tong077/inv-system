<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrderItem;
use App\Services\PurchaseOrderItemService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class PurchaseOrderItemController extends Controller
{
    public function __construct(private readonly PurchaseOrderItemService $items) {}

    public function index(Request $request): View|Response
    {
        $search = $request->string('search')->trim()->limit(100)->toString();
        $purchaseOrderId = $request->integer('purchase_order_id');
        $purchaseOrderId = $purchaseOrderId > 0 ? $purchaseOrderId : null;
        $purchaseOrderItems = $this->items->getAll($search, $purchaseOrderId);
        $options = $this->items->getFormOptions();
        $data = ['purchaseOrderItems' => $purchaseOrderItems, 'search' => $search, 'purchaseOrderId' => $purchaseOrderId, ...$options];

        if ($request->header('X-Live-Search') === 'true') {
            return response()->view('purchase-order-items.partials.results', $data);
        }

        return view('purchase-order-items.index', $data);
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openPurchaseOrderItemDrawer', 'create');
        }

        try {
            $this->items->create($validator->validated());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput()->with('openPurchaseOrderItemDrawer', 'create');
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The purchase order line could not be created. Please try again.');
        }

        return to_route('purchase-order-items.index')->with('success', 'Purchase order line added.');
    }

    public function update(Request $request, PurchaseOrderItem $purchaseOrderItem): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openPurchaseOrderItemDrawer', $purchaseOrderItem->id);
        }

        try {
            $this->items->update($purchaseOrderItem, $validator->validated());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput()->with('openPurchaseOrderItemDrawer', $purchaseOrderItem->id);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The purchase order line could not be updated. Please try again.');
        }

        return to_route('purchase-order-items.index')->with('success', 'Purchase order line updated.');
    }

    public function destroy(PurchaseOrderItem $purchaseOrderItem): RedirectResponse
    {
        try {
            if (! $this->items->delete($purchaseOrderItem)) {
                return to_route('purchase-order-items.index')->with('warning', 'Lines can only be removed while the purchase order is a draft and has no receipts.');
            }
        } catch (Throwable $exception) {
            report($exception);

            return to_route('purchase-order-items.index')->with('error', 'The purchase order line could not be deleted. Please try again.');
        }

        return to_route('purchase-order-items.index')->with('success', 'Purchase order line deleted.');
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(): array
    {
        return [
            'purchase_order_id' => ['required', 'integer', Rule::exists('purchase_orders', 'id')->where('status', 'draft')],
            'ingredient_id' => ['required', 'integer', Rule::exists('ingredients', 'id')->whereNull('deleted_at')],
            'quantity' => ['required', 'numeric', 'gt:0', 'max:999999999999.999', 'decimal:0,3'],
            'unit_id' => ['required', 'integer', Rule::exists('units', 'id')],
            'unit_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
        ];
    }
}
