<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Services\GoodsReceiptService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class GoodsReceiptController extends Controller
{
    public function __construct(private readonly GoodsReceiptService $receipts)
    {
    }

    public function index(Request $request): View|Response
    {
        $search = $request->string('search')->trim()->limit(100)->toString();
        $status = $request->query('status');
        $status = is_string($status) ? $status : null;
        $data = [...$this->receipts->getFormOptions(), 'receiptsList' => $this->receipts->getAll($search, $status), 'search' => $search, 'status' => $status];
        return $request->header('X-Live-Search') === 'true'
            ? response()->view('goods-receipts.partials.results', $data)
            : view('goods-receipts.index', $data);
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails())
            return back()->withErrors($validator)->withInput()->with('openGoodsReceiptDrawer', 'create');
        try {
            $this->receipts->create($validator->validated());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput()->with('openGoodsReceiptDrawer', 'create');
        } catch (Throwable $exception) {
            report($exception);
            return back()->withInput()->with('error', 'The goods receipt could not be created. Please try again.');
        }
        return to_route('goods-receipts.index')->with('success', 'Goods receipt draft created.');
    }

    public function update(Request $request, GoodsReceipt $goodsReceipt): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules($goodsReceipt));
        if ($validator->fails())
            return back()->withErrors($validator)->withInput()->with('openGoodsReceiptDrawer', $goodsReceipt->id);
        try {
            $this->receipts->update($goodsReceipt, $validator->validated());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput()->with('openGoodsReceiptDrawer', $goodsReceipt->id);
        } catch (Throwable $exception) {
            report($exception);
            return back()->withInput()->with('error', 'The goods receipt could not be updated. Please try again.');
        }
        return to_route('goods-receipts.index')->with('success', 'Goods receipt updated.');
    }

    public function destroy(GoodsReceipt $goodsReceipt): RedirectResponse
    {
        try {
            if (!$this->receipts->delete($goodsReceipt))
                return to_route('goods-receipts.index')->with('warning', 'Only draft goods receipts can be deleted.');
        } catch (Throwable $exception) {
            report($exception);
            return to_route('goods-receipts.index')->with('error', 'The goods receipt could not be deleted. Please try again.');
        }
        return to_route('goods-receipts.index')->with('success', 'Goods receipt deleted.');
    }

    public function post(GoodsReceipt $goodsReceipt): RedirectResponse
    {
        try {
            $this->receipts->post($goodsReceipt);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->with('error', 'The goods receipt could not be posted. Review its lines and try again.');
        } catch (Throwable $exception) {
            report($exception);
            return back()->with('error', 'The goods receipt could not be posted. Please try again.');
        }
        return to_route('goods-receipts.index')->with('success', 'Goods receipt posted and stock updated.');
    }

    private function rules(?GoodsReceipt $receipt = null): array
    {
        $supplierRule = Rule::exists('suppliers', 'id')->whereNull('deleted_at');
        $locationRule = Rule::exists('stock_locations', 'id');
        if ($receipt === null) {
            $locationRule->where('is_active', true);
        }

        return [
            'purchase_order_id' => ['nullable', 'integer', 'exists:purchase_orders,id'],
            'supplier_id' => ['required', 'integer', $supplierRule],
            'stock_location_id' => ['required', 'integer', $locationRule],
            'received_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
