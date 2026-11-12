<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Unit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseOrderItemService
{
    public function __construct(
        private readonly PurchaseOrderService $orders,
        private readonly UnitConversionService $units,
    ) {}

    public function getAll(?string $search = null, ?int $purchaseOrderId = null): LengthAwarePaginator
    {
        return PurchaseOrderItem::query()
            ->with(['purchaseOrder.supplier', 'ingredient.baseUnit', 'unit'])
            ->when($purchaseOrderId !== null, fn (Builder $query) => $query->where('purchase_order_id', $purchaseOrderId))
            ->when($search !== null && $search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->whereHas('ingredient', function (Builder $ingredients) use ($search): void {
                        $ingredients->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    })->orWhereHas('purchaseOrder', function (Builder $orders) use ($search): void {
                        $orders->where('po_number', 'like', "%{$search}%")
                            ->orWhereHas('supplier', fn (Builder $suppliers) => $suppliers->where('name', 'like', "%{$search}%"));
                    });
                });
            })
            ->orderByDesc('purchase_order_id')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();
    }

    /** @return array{purchaseOrders: Collection<int, PurchaseOrder>, ingredients: Collection<int, Ingredient>, units: Collection<int, Unit>} */
    public function getFormOptions(): array
    {
        return [
            'purchaseOrders' => PurchaseOrder::query()->with('supplier')->orderByDesc('order_date')->get(),
            'ingredients' => Ingredient::query()->with('baseUnit')->orderBy('name')->get(['id', 'code', 'name', 'base_unit_id']),
            'units' => Unit::query()->orderBy('dimension')->orderBy('name')->get(['id', 'name', 'abbreviation', 'dimension', 'is_active']),
        ];
    }

    public function create(array $data): PurchaseOrderItem
    {
        return DB::transaction(function () use ($data): PurchaseOrderItem {
            $order = PurchaseOrder::query()->whereKey($data['purchase_order_id'])->lockForUpdate()->firstOrFail();
            $this->ensureDraft($order);
            $data = $this->withTotal($data);
            $item = $order->items()->create($data);
            $this->orders->recalculateTotals($order);

            return $item;
        });
    }

    public function update(PurchaseOrderItem $item, array $data): PurchaseOrderItem
    {
        return DB::transaction(function () use ($item, $data): PurchaseOrderItem {
            $oldOrder = PurchaseOrder::query()->whereKey($item->purchase_order_id)->lockForUpdate()->firstOrFail();
            $newOrder = PurchaseOrder::query()->whereKey($data['purchase_order_id'])->lockForUpdate()->firstOrFail();
            $this->ensureDraft($oldOrder);
            $this->ensureDraft($newOrder);

            $item->update($this->withTotal($data));
            $this->orders->recalculateTotals($oldOrder);

            if ($oldOrder->isNot($newOrder)) {
                $this->orders->recalculateTotals($newOrder);
            }

            return $item->refresh();
        });
    }

    public function delete(PurchaseOrderItem $item): bool
    {
        return DB::transaction(function () use ($item): bool {
            if ($item->goodsReceiptItems()->exists()) {
                return false;
            }

            $order = PurchaseOrder::query()->whereKey($item->purchase_order_id)->lockForUpdate()->firstOrFail();

            if ($order->status !== 'draft') {
                return false;
            }

            $deleted = (bool) $item->delete();
            $this->orders->recalculateTotals($order);

            return $deleted;
        });
    }

    /** @param array<string, mixed> $data
     *  @return array<string, mixed>
     */
    private function withTotal(array $data): array
    {
        $ingredient = Ingredient::query()->with('baseUnit')->findOrFail($data['ingredient_id']);
        $unit = Unit::query()->findOrFail($data['unit_id']);
        $this->units->conversionFactor($unit, $ingredient->baseUnit);
        $total = round((float) $data['quantity'] * (float) $data['unit_price'], 2);

        if ($total > 9999999999.99) {
            throw ValidationException::withMessages([
                'quantity' => 'The line total is above the maximum allowed amount.',
            ]);
        }

        return [...$data, 'total_price' => $total];
    }

    private function ensureDraft(PurchaseOrder $order): void
    {
        if ($order->status !== 'draft') {
            throw ValidationException::withMessages([
                'purchase_order_id' => 'Purchase order lines can only be changed while the order is a draft.',
            ]);
        }
    }
}
