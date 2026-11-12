<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Services\GoodsReceiptItemService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class GoodsReceiptItemController extends Controller
{
    public function __construct(private readonly GoodsReceiptItemService $items)
    {
    }

    public function index(Request $request): View|Response
    {
        $search = $request->string('search')->trim()->limit(100)->toString();
        $receiptId = $request->integer('goods_receipt_id') ?: null;
        $data = [...$this->items->getFormOptions(), 'items' => $this->items->getAll($search, $receiptId), 'search' => $search, 'receiptId' => $receiptId];
        return $request->header('X-Live-Search') === 'true'
            ? response()->view('goods-receipt-items.partials.results', $data)
            : view('goods-receipt-items.index', $data);
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails())
            return back()->withErrors($validator)->withInput()->with('openGoodsReceiptItemDrawer', 'create');
        try {
            $this->items->create($validator->validated());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput()->with('openGoodsReceiptItemDrawer', 'create');
        } catch (Throwable $exception) {
            report($exception);
            return back()->withInput()->with('error', 'The receipt line could not be added. Please try again.');
        }
        return to_route('goods-receipt-items.index', $request->only('goods_receipt_id'))->with('success', 'Receipt line added.');
    }

    public function update(Request $request, GoodsReceiptItem $goodsReceiptItem): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules($goodsReceiptItem));
        if ($validator->fails())
            return back()->withErrors($validator)->withInput()->with('openGoodsReceiptItemDrawer', $goodsReceiptItem->id);
        try {
            $this->items->update($goodsReceiptItem, $validator->validated());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput()->with('openGoodsReceiptItemDrawer', $goodsReceiptItem->id);
        } catch (Throwable $exception) {
            report($exception);
            return back()->withInput()->with('error', 'The receipt line could not be updated. Please try again.');
        }
        return to_route('goods-receipt-items.index', $request->only('goods_receipt_id'))->with('success', 'Receipt line updated.');
    }

    public function destroy(Request $request, GoodsReceiptItem $goodsReceiptItem): RedirectResponse
    {
        try {
            if (!$this->items->delete($goodsReceiptItem))
                return back()->with('warning', 'Only lines on draft receipts can be deleted.');
        } catch (Throwable $exception) {
            report($exception);
            return back()->with('error', 'The receipt line could not be deleted. Please try again.');
        }
        return to_route('goods-receipt-items.index', $request->only('goods_receipt_id'))->with('success', 'Receipt line deleted.');
    }

    private function rules(?GoodsReceiptItem $item = null): array
    {
        return [
            'goods_receipt_id' => ['required', 'integer', Rule::exists('goods_receipts', 'id')->where('status', 'draft')],
            'purchase_order_item_id' => ['nullable', 'integer', 'exists:purchase_order_items,id'],
            'ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'quantity_received' => ['required', 'numeric', 'gt:0', 'max:999999999999.999', 'decimal:0,3'],
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'unit_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'expiry_date' => ['nullable', 'date'],
            'batch_number' => ['nullable', 'string', 'max:100'],
        ];
    }
}
