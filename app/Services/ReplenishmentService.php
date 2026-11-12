<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReplenishmentService
{
    public function __construct(
        private readonly InventoryReportService $reports,
        private readonly PurchaseOrderService $orders,
        private readonly PurchaseOrderItemService $orderItems,
    ) {}

    /** @return array{orders:int,lines:int,reused:int} */
    public function createDraftOrders(): array
    {
        return DB::transaction(function (): array {
            $suggestions = collect($this->reports->replenishmentSuggestions(500))
                ->filter(fn (array $suggestion): bool => $suggestion['orderable'])
                ->groupBy(fn (array $suggestion): int => (int) $suggestion['supplier']->id);

            if ($suggestions->isEmpty()) {
                throw ValidationException::withMessages([
                    'replenishment' => 'There are no reorder suggestions with an active supplier and a valid package price. Add supplier price history for low stock items first.',
                ]);
            }

            $createdOrders = 0;
            $addedLines = 0;
            $reusedOrders = 0;

            foreach ($suggestions as $supplierId => $items) {
                Supplier::query()->whereKey($supplierId)->lockForUpdate()->firstOrFail();
                $order = PurchaseOrder::query()
                    ->where('supplier_id', $supplierId)
                    ->where('status', 'draft')
                    ->where('notes', 'like', '[Auto-restock]%')
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->first();

                if ($order === null) {
                    $order = $this->orders->create([
                        'supplier_id' => $supplierId,
                        'order_date' => now()->toDateString(),
                        'expected_date' => null,
                        'tax_amount' => 0,
                        'discount_amount' => 0,
                        'notes' => '[Auto-restock] Review quantities and prices before ordering.',
                    ]);
                    $createdOrders++;
                } else {
                    $reusedOrders++;
                }

                $existingIngredientIds = $order->items()->pluck('ingredient_id')->map(fn ($id): int => (int) $id)->all();
                foreach ($items as $suggestion) {
                    if (in_array((int) $suggestion['ingredient']->id, $existingIngredientIds, true)) {
                        continue;
                    }

                    $this->orderItems->create([
                        'purchase_order_id' => $order->id,
                        'ingredient_id' => $suggestion['ingredient']->id,
                        'quantity' => round($suggestion['packages'] * $suggestion['packageQuantity'], 3),
                        'unit_id' => $suggestion['packageUnit']->id,
                        'unit_price' => round($suggestion['packagePrice'] / $suggestion['packageQuantity'], 2),
                    ]);
                    $addedLines++;
                }
            }

            return ['orders' => $createdOrders, 'lines' => $addedLines, 'reused' => $reusedOrders];
        });
    }

}
