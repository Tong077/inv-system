<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Ingredient;
use App\Models\IngredientPriceHistory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockCount;
use App\Models\StockWaste;
use App\Models\StockAdjustment;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function __construct(private readonly UnitConversionService $units) {}

    public function postGoodsReceipt(GoodsReceipt $goodsReceipt): GoodsReceipt
    {
        return DB::transaction(function () use ($goodsReceipt): GoodsReceipt {
            $receipt = GoodsReceipt::query()
                ->with('items')
                ->whereKey($goodsReceipt->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($receipt->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Only draft goods receipts can be posted.',
                ]);
            }

            if ($receipt->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'Add at least one received item before posting this receipt.',
                ]);
            }

            if ($receipt->purchase_order_id !== null) {
                $order = PurchaseOrder::query()->whereKey($receipt->purchase_order_id)->lockForUpdate()->firstOrFail();
                if (! in_array($order->status, ['ordered', 'partially_received'], true)) {
                    throw ValidationException::withMessages(['purchase_order_id' => 'The linked purchase order is no longer open for receiving.']);
                }
            }

            foreach ($receipt->items->pluck('ingredient_id')->unique()->sort() as $ingredientId) {
                Ingredient::query()->whereKey($ingredientId)->lockForUpdate()->firstOrFail();
            }

            foreach ($receipt->items->sortBy('id') as $item) {
                $ingredient = Ingredient::query()->with('baseUnit')->findOrFail($item->ingredient_id);
                $unit = Unit::query()->findOrFail($item->unit_id);
                $conversionFactor = $this->units->conversionFactor($unit, $ingredient->baseUnit);
                $receivedBaseQuantity = round((float) $item->quantity_received * $conversionFactor, 3);
                $unitCostBase = round((float) $item->unit_price / $conversionFactor, 6);

                if ($receivedBaseQuantity <= 0) {
                    throw ValidationException::withMessages([
                        'quantity_received' => 'The received quantity must convert to a positive base-unit quantity.',
                    ]);
                }

                $this->assertPurchaseOrderQuantity($item, $receipt);
                $this->applyReceiptToBalance($receipt, $ingredient, $receivedBaseQuantity, $unitCostBase);
                $this->recordMovement($receipt, $ingredient, $receivedBaseQuantity, $unitCostBase);
                $this->recordPriceHistory($receipt, $item, $unitCostBase);
            }

            $receipt->status = 'posted';
            $receipt->received_by = auth()->id();
            $receipt->save();

            $this->refreshPurchaseOrderStatuses($receipt);

            return $receipt->refresh();
        });
    }

    public function postStockTransfer(StockTransfer $stockTransfer): StockTransfer
    {
        return DB::transaction(function () use ($stockTransfer): StockTransfer {
            $transfer = StockTransfer::query()->with('items')->whereKey($stockTransfer->id)->lockForUpdate()->firstOrFail();
            if ($transfer->status !== 'draft') throw ValidationException::withMessages(['status' => 'Only draft transfers can be posted.']);
            if ((int) $transfer->from_location_id === (int) $transfer->to_location_id) throw ValidationException::withMessages(['to_location_id' => 'Choose a different destination location.']);
            if ($transfer->items->isEmpty()) throw ValidationException::withMessages(['items' => 'Add at least one transfer line before posting.']);

            foreach ($transfer->items->pluck('ingredient_id')->unique()->sort() as $id) Ingredient::query()->whereKey($id)->lockForUpdate()->firstOrFail();
            foreach ($transfer->items as $item) {
                $ingredient = Ingredient::query()->with('baseUnit')->findOrFail($item->ingredient_id);
                $unit = Unit::query()->findOrFail($item->unit_id);
                $quantity = round((float) $item->quantity * $this->units->conversionFactor($unit, $ingredient->baseUnit), 3);
                if ($quantity <= 0) throw ValidationException::withMessages(['quantity' => 'The transfer quantity must convert to a positive base-unit quantity.']);
                $source = StockBalance::query()->where('ingredient_id', $ingredient->id)->where('stock_location_id', $transfer->from_location_id)->lockForUpdate()->first();
                if ($source === null || (float) $source->quantity_on_hand + 0.0005 < $quantity) throw ValidationException::withMessages(['quantity' => 'The source location does not have enough stock for this transfer.']);
                $cost = (float) $source->average_unit_cost;
                $source->update(['quantity_on_hand' => round((float) $source->quantity_on_hand - $quantity, 3), 'last_movement_at' => $transfer->transfer_date->copy()->startOfDay()]);
                $destination = StockBalance::query()->where('ingredient_id', $ingredient->id)->where('stock_location_id', $transfer->to_location_id)->lockForUpdate()->first();
                if ($destination === null) {
                    $destination = StockBalance::create(['ingredient_id' => $ingredient->id, 'stock_location_id' => $transfer->to_location_id, 'quantity_on_hand' => 0, 'average_unit_cost' => $cost]);
                }
                $oldQty = (float) $destination->quantity_on_hand;
                $newQty = round($oldQty + $quantity, 3);
                $avg = $newQty > 0 ? round((($oldQty * (float) $destination->average_unit_cost) + ($quantity * $cost)) / $newQty, 6) : $cost;
                $destination->update(['quantity_on_hand' => $newQty, 'average_unit_cost' => $avg, 'last_movement_at' => $transfer->transfer_date->copy()->startOfDay()]);
                foreach ([[$transfer->from_location_id, 0, $quantity, $source->quantity_on_hand],[$transfer->to_location_id, $quantity, 0, $destination->quantity_on_hand]] as [$locationId,$in,$out,$balance]) {
                    StockMovement::create(['ingredient_id'=>$ingredient->id,'stock_location_id'=>$locationId,'movement_type'=>'transfer','reference_type'=>StockTransfer::class,'reference_id'=>$transfer->id,'quantity_in'=>$in,'quantity_out'=>$out,'unit_cost'=>$cost,'balance_after'=>$balance,'movement_date'=>$transfer->transfer_date->copy()->startOfDay(),'notes'=>$transfer->transfer_number,'created_by'=>auth()->id()]);
                }
            }
            $transfer->update(['status'=>'posted']);
            return $transfer->refresh();
        });
    }

    public function postStockWaste(StockWaste $stockWaste): StockWaste
    {
        return DB::transaction(function () use ($stockWaste): StockWaste {
            $waste=StockWaste::query()->with('items')->whereKey($stockWaste->id)->lockForUpdate()->firstOrFail();
            if($waste->status!=='draft')throw ValidationException::withMessages(['status'=>'Only draft waste records can be posted.']);
            if($waste->items->isEmpty())throw ValidationException::withMessages(['items'=>'Add at least one waste line before posting.']);
            foreach($waste->items->pluck('ingredient_id')->unique()->sort() as $id)Ingredient::query()->whereKey($id)->lockForUpdate()->firstOrFail();
            foreach($waste->items as $item){$ingredient=Ingredient::query()->with('baseUnit')->findOrFail($item->ingredient_id);$unit=Unit::query()->findOrFail($item->unit_id);$factor=$this->units->conversionFactor($unit,$ingredient->baseUnit);$qty=round((float)$item->quantity*$factor,3);$balance=StockBalance::query()->where('ingredient_id',$item->ingredient_id)->where('stock_location_id',$waste->stock_location_id)->lockForUpdate()->first();if($qty<=0||$balance===null||(float)$balance->quantity_on_hand+0.0005<$qty)throw ValidationException::withMessages(['quantity'=>'There is not enough stock to record this waste.']);$cost=(float)$balance->average_unit_cost;$after=round((float)$balance->quantity_on_hand-$qty,3);$balance->update(['quantity_on_hand'=>$after,'last_movement_at'=>$waste->waste_date->copy()->startOfDay()]);$unitCost=$cost*$factor;$item->update(['unit_cost'=>round($unitCost,6),'total_cost'=>round((float)$item->quantity*$unitCost,2)]);StockMovement::create(['ingredient_id'=>$item->ingredient_id,'stock_location_id'=>$waste->stock_location_id,'movement_type'=>'waste','reference_type'=>StockWaste::class,'reference_id'=>$waste->id,'quantity_in'=>0,'quantity_out'=>$qty,'unit_cost'=>$cost,'balance_after'=>$after,'movement_date'=>$waste->waste_date->copy()->startOfDay(),'notes'=>$waste->waste_number,'created_by'=>auth()->id()]);}
            $waste->update(['status'=>'posted','approved_by'=>auth()->id()]);return $waste->refresh();
        });
    }

    public function postStockAdjustment(StockAdjustment $stockAdjustment): StockAdjustment
    {
        return DB::transaction(function () use ($stockAdjustment): StockAdjustment {
            $adjustment=StockAdjustment::query()->with('items')->whereKey($stockAdjustment->id)->lockForUpdate()->firstOrFail();
            if($adjustment->status!=='draft')throw ValidationException::withMessages(['status'=>'Only draft adjustments can be posted.']);
            if($adjustment->items->isEmpty())throw ValidationException::withMessages(['items'=>'Add at least one adjusted ingredient before posting.']);
            foreach($adjustment->items->pluck('ingredient_id')->unique()->sort() as $id)Ingredient::query()->whereKey($id)->lockForUpdate()->firstOrFail();
            foreach($adjustment->items as $item){$balance=StockBalance::query()->where('ingredient_id',$item->ingredient_id)->where('stock_location_id',$adjustment->stock_location_id)->lockForUpdate()->first();$previous=(float)($balance?->quantity_on_hand??0);$diff=round((float)$item->adjusted_quantity-$previous,3);if($balance===null)$balance=StockBalance::create(['ingredient_id'=>$item->ingredient_id,'stock_location_id'=>$adjustment->stock_location_id,'quantity_on_hand'=>0,'average_unit_cost'=>0]);$cost=(float)($balance->average_unit_cost?:$item->unit_cost);$balance->update(['quantity_on_hand'=>(float)$item->adjusted_quantity,'last_movement_at'=>$adjustment->adjustment_date->copy()->startOfDay()]);$item->update(['previous_quantity'=>$previous,'difference_quantity'=>$diff,'unit_cost'=>$cost]);if(abs($diff)>0.0005)StockMovement::create(['ingredient_id'=>$item->ingredient_id,'stock_location_id'=>$adjustment->stock_location_id,'movement_type'=>'adjustment','reference_type'=>StockAdjustment::class,'reference_id'=>$adjustment->id,'quantity_in'=>max($diff,0),'quantity_out'=>max(-$diff,0),'unit_cost'=>$cost,'balance_after'=>$item->adjusted_quantity,'movement_date'=>$adjustment->adjustment_date->copy()->startOfDay(),'notes'=>$adjustment->adjustment_number.' — '.$adjustment->reason,'created_by'=>auth()->id()]);}
            $adjustment->update(['status'=>'posted','approved_by'=>auth()->id()]);return $adjustment->refresh();
        });
    }

    public function postStockCount(StockCount $stockCount): StockCount
    {
        return DB::transaction(function () use ($stockCount): StockCount {
            $count = StockCount::query()->with('items')->whereKey($stockCount->id)->lockForUpdate()->firstOrFail();
            if ($count->status !== 'draft') throw ValidationException::withMessages(['status' => 'Only draft stock counts can be posted.']);
            if ($count->items->isEmpty()) throw ValidationException::withMessages(['items' => 'Add at least one counted ingredient before posting.']);
            foreach ($count->items->pluck('ingredient_id')->unique()->sort() as $id) Ingredient::query()->whereKey($id)->lockForUpdate()->firstOrFail();
            foreach ($count->items as $item) {
                $balance = StockBalance::query()->where('ingredient_id',$item->ingredient_id)->where('stock_location_id',$count->stock_location_id)->lockForUpdate()->first();
                $system = (float) ($balance?->quantity_on_hand ?? 0);
                $variance = round((float)$item->counted_quantity - $system, 3);
                if ($balance === null) $balance = StockBalance::create(['ingredient_id'=>$item->ingredient_id,'stock_location_id'=>$count->stock_location_id,'quantity_on_hand'=>0,'average_unit_cost'=>0]);
                $cost = (float)($balance->average_unit_cost ?: $item->unit_cost);
                $balance->update(['quantity_on_hand'=>(float)$item->counted_quantity,'last_movement_at'=>$count->count_date->copy()->startOfDay()]);
                $item->update(['system_quantity'=>$system,'variance_quantity'=>$variance,'unit_cost'=>$cost]);
                if (abs($variance) > 0.0005) StockMovement::create(['ingredient_id'=>$item->ingredient_id,'stock_location_id'=>$count->stock_location_id,'movement_type'=>'stock_count','reference_type'=>StockCount::class,'reference_id'=>$count->id,'quantity_in'=>max($variance,0),'quantity_out'=>max(-$variance,0),'unit_cost'=>$cost,'balance_after'=>$item->counted_quantity,'movement_date'=>$count->count_date->copy()->startOfDay(),'notes'=>$count->count_number,'created_by'=>auth()->id()]);
            }
            $count->update(['status'=>'posted','approved_by'=>auth()->id(),'counted_by'=>$count->counted_by ?? auth()->id()]);
            return $count->refresh();
        });
    }

    private function assertPurchaseOrderQuantity(GoodsReceiptItem $receiptItem, GoodsReceipt $receipt): void
    {
        if ($receiptItem->purchase_order_item_id === null) {
            if ($receipt->purchase_order_id !== null) {
                throw ValidationException::withMessages([
                    'purchase_order_item_id' => 'Every line on a purchase order receipt must reference one of that order’s lines.',
                ]);
            }

            return;
        }

        $orderItem = PurchaseOrderItem::query()->lockForUpdate()->findOrFail($receiptItem->purchase_order_item_id);

        if ($receipt->purchase_order_id === null
            || (int) $orderItem->purchase_order_id !== (int) $receipt->purchase_order_id
            || (int) $orderItem->ingredient_id !== (int) $receiptItem->ingredient_id
            || (int) $orderItem->unit_id !== (int) $receiptItem->unit_id) {
            throw ValidationException::withMessages([
                'purchase_order_item_id' => 'The selected purchase order line does not match this receipt line.',
            ]);
        }

        $previouslyReceived = (float) $orderItem->goodsReceiptItems()
            ->whereHas('goodsReceipt', fn ($query) => $query->where('status', 'posted')->where('id', '!=', $receipt->id))
            ->sum('quantity_received');
        $beingPosted = (float) $orderItem->goodsReceiptItems()
            ->where('goods_receipt_id', $receipt->id)
            ->sum('quantity_received');

        if ($previouslyReceived + $beingPosted > (float) $orderItem->quantity + 0.0005) {
            throw ValidationException::withMessages([
                'quantity_received' => 'The total received quantity cannot exceed the purchase order quantity.',
            ]);
        }
    }

    private function applyReceiptToBalance(GoodsReceipt $receipt, Ingredient $ingredient, float $quantity, float $unitCost): void
    {
        $balance = StockBalance::query()
            ->where('ingredient_id', $ingredient->id)
            ->where('stock_location_id', $receipt->stock_location_id)
            ->lockForUpdate()
            ->first();

        if ($balance === null) {
            StockBalance::create([
                'ingredient_id' => $ingredient->id,
                'stock_location_id' => $receipt->stock_location_id,
                'quantity_on_hand' => $quantity,
                'average_unit_cost' => $unitCost,
                'last_movement_at' => $receipt->received_date->copy()->startOfDay(),
            ]);

            return;
        }

        $oldQuantity = (float) $balance->quantity_on_hand;
        $newQuantity = round($oldQuantity + $quantity, 3);
        $oldCost = (float) $balance->average_unit_cost;
        $averageCost = $oldQuantity > 0 && $newQuantity > 0
            ? round((($oldQuantity * $oldCost) + ($quantity * $unitCost)) / $newQuantity, 6)
            : $unitCost;

        $balance->update([
            'quantity_on_hand' => $newQuantity,
            'average_unit_cost' => $averageCost,
            'last_movement_at' => $receipt->received_date->copy()->startOfDay(),
        ]);
    }

    private function recordMovement(GoodsReceipt $receipt, Ingredient $ingredient, float $quantity, float $unitCost): void
    {
        $balance = StockBalance::query()
            ->where('ingredient_id', $ingredient->id)
            ->where('stock_location_id', $receipt->stock_location_id)
            ->firstOrFail();

        StockMovement::create([
            'ingredient_id' => $ingredient->id,
            'stock_location_id' => $receipt->stock_location_id,
            'movement_type' => 'receipt',
            'reference_type' => GoodsReceipt::class,
            'reference_id' => $receipt->id,
            'quantity_in' => $quantity,
            'quantity_out' => 0,
            'unit_cost' => $unitCost,
            'balance_after' => $balance->quantity_on_hand,
            'movement_date' => $receipt->received_date->copy()->startOfDay(),
            'notes' => $receipt->receipt_number,
            'created_by' => auth()->id(),
        ]);
    }

    private function recordPriceHistory(GoodsReceipt $receipt, GoodsReceiptItem $item, float $unitCostBase): void
    {
        IngredientPriceHistory::create([
            'ingredient_id' => $item->ingredient_id,
            'supplier_id' => $receipt->supplier_id,
            'package_price' => round((float) $item->quantity_received * (float) $item->unit_price, 2),
            'package_quantity' => $item->quantity_received,
            'package_unit_id' => $item->unit_id,
            'unit_cost' => $unitCostBase,
            'effective_date' => $receipt->received_date,
            'notes' => 'Recorded from goods receipt '.$receipt->receipt_number,
        ]);
    }

    private function refreshPurchaseOrderStatuses(GoodsReceipt $receipt): void
    {
        if ($receipt->purchase_order_id === null) {
            return;
        }

        $order = PurchaseOrder::query()->with('items.goodsReceiptItems.goodsReceipt')->whereKey($receipt->purchase_order_id)->lockForUpdate()->firstOrFail();
        $anyReceived = false;
        $allReceived = $order->items->isNotEmpty();

        foreach ($order->items as $orderItem) {
            $received = (float) $orderItem->goodsReceiptItems
                ->filter(fn ($receiptItem) => $receiptItem->goodsReceipt?->status === 'posted')
                ->sum('quantity_received');
            $anyReceived = $anyReceived || $received > 0;
            $allReceived = $allReceived && $received + 0.0005 >= (float) $orderItem->quantity;
        }

        $order->update(['status' => $allReceived ? 'received' : ($anyReceived ? 'partially_received' : 'ordered')]);
    }
}
