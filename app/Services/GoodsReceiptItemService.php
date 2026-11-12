<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Ingredient;
use App\Models\PurchaseOrderItem;
use App\Models\Unit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GoodsReceiptItemService
{
    public function __construct(private readonly UnitConversionService $units) {}

    public function getAll(?string $search = null, ?int $receiptId = null): LengthAwarePaginator
    {
        return GoodsReceiptItem::query()
            ->with(['goodsReceipt.purchaseOrder', 'purchaseOrderItem', 'ingredient.baseUnit', 'unit'])
            ->when($receiptId !== null, fn (Builder $query) => $query->where('goods_receipt_id', $receiptId))
            ->when($search !== null && $search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('batch_number', 'like', "%{$search}%")
                        ->orWhereHas('ingredient', fn (Builder $ingredients) => $ingredients->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                        ->orWhereHas('goodsReceipt', fn (Builder $receipts) => $receipts->where('receipt_number', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('goods_receipt_id')->orderBy('id')
            ->paginate(15)->withQueryString();
    }

    /** @return array{receipts: Collection<int, GoodsReceipt>, ingredients: Collection<int, Ingredient>, units: Collection<int, Unit>, purchaseOrderItems: Collection<int, PurchaseOrderItem>} */
    public function getFormOptions(): array
    {
        return [
            'receipts' => GoodsReceipt::query()->where('status', 'draft')->with('purchaseOrder')->orderByDesc('received_date')->get(),
            'ingredients' => Ingredient::query()->with('baseUnit')->orderBy('name')->get(['id', 'code', 'name', 'base_unit_id']),
            'units' => Unit::query()->orderBy('dimension')->orderBy('name')->get(['id', 'name', 'abbreviation', 'dimension']),
            'purchaseOrderItems' => PurchaseOrderItem::query()
                ->whereHas('purchaseOrder', fn (Builder $orders) => $orders->whereIn('status', ['ordered', 'partially_received']))
                ->with(['purchaseOrder', 'ingredient', 'unit'])->orderByDesc('id')->get(),
        ];
    }

    public function create(array $data): GoodsReceiptItem
    {
        return DB::transaction(function () use ($data): GoodsReceiptItem {
            $receipt = GoodsReceipt::query()->whereKey($data['goods_receipt_id'])->lockForUpdate()->firstOrFail();
            $this->ensureDraft($receipt);
            $data = $this->normalizeLine($receipt, $data);

            return $receipt->items()->create($data);
        });
    }

    public function update(GoodsReceiptItem $item, array $data): GoodsReceiptItem
    {
        return DB::transaction(function () use ($item, $data): GoodsReceiptItem {
            $oldReceipt = GoodsReceipt::query()->whereKey($item->goods_receipt_id)->lockForUpdate()->firstOrFail();
            $newReceipt = GoodsReceipt::query()->whereKey($data['goods_receipt_id'])->lockForUpdate()->firstOrFail();
            $this->ensureDraft($oldReceipt);
            $this->ensureDraft($newReceipt);
            $data['id'] = $item->id;
            $item->update($this->normalizeLine($newReceipt, $data));

            return $item->refresh();
        });
    }

    public function delete(GoodsReceiptItem $item): bool
    {
        return DB::transaction(function () use ($item): bool {
            $receipt = GoodsReceipt::query()->whereKey($item->goods_receipt_id)->lockForUpdate()->firstOrFail();
            return $receipt->status === 'draft' && (bool) $item->delete();
        });
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    private function normalizeLine(GoodsReceipt $receipt, array $data): array
    {
        $ingredient = Ingredient::query()->with('baseUnit')->findOrFail($data['ingredient_id']);
        $unit = Unit::query()->findOrFail($data['unit_id']);
        $this->units->conversionFactor($unit, $ingredient->baseUnit);

        if ($receipt->purchase_order_id === null) {
            if (! empty($data['purchase_order_item_id'])) {
                throw ValidationException::withMessages(['purchase_order_item_id' => 'A stand-alone receipt cannot use a purchase order line.']);
            }
            $data['purchase_order_item_id'] = null;
            $data['quantity_ordered'] = 0;
            return $data;
        }

        if (empty($data['purchase_order_item_id'])) {
            throw ValidationException::withMessages(['purchase_order_item_id' => 'Select a line from the linked purchase order.']);
        }

        $orderItem = PurchaseOrderItem::query()->with('purchaseOrder')->lockForUpdate()->findOrFail($data['purchase_order_item_id']);
        if ((int) $orderItem->purchase_order_id !== (int) $receipt->purchase_order_id
            || (int) $orderItem->ingredient_id !== (int) $ingredient->id
            || (int) $orderItem->unit_id !== (int) $unit->id) {
            throw ValidationException::withMessages(['purchase_order_item_id' => 'The selected line must match the receipt purchase order, ingredient, and unit.']);
        }

        $received = (float) $orderItem->goodsReceiptItems()
            ->whereHas('goodsReceipt', fn (Builder $query) => $query->where('status', 'posted')->orWhereKey($receipt->getKey()))
            ->when(isset($data['id']), fn (Builder $query) => $query->where('id', '!=', $data['id']))
            ->sum('quantity_received');

        if ($received + (float) $data['quantity_received'] > (float) $orderItem->quantity + 0.0005) {
            throw ValidationException::withMessages(['quantity_received' => 'Received quantity cannot exceed the remaining purchase order quantity.']);
        }

        $data['quantity_ordered'] = $orderItem->quantity;
        return $data;
    }

    private function ensureDraft(GoodsReceipt $receipt): void
    {
        if ($receipt->status !== 'draft') {
            throw ValidationException::withMessages(['goods_receipt_id' => 'Receipt lines can only be changed while the receipt is a draft.']);
        }
    }
}
