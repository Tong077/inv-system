<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\Ingredient;
use App\Models\IngredientPriceHistory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\StockWaste;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryReportService
{
    public function __construct(private readonly UnitConversionService $units) {}

    /** @return array{ingredientCount:int,locationCount:int,postedWasteCount:int,lowStockCount:int,openOrderCount:int,draftReceiptCount:int,recentMovements:Collection<int,StockMovement>,trackedIngredients:Collection<int,Ingredient>,trackedIngredientId:int|null,movementChart:array<string,mixed>,replenishmentSuggestions:list<array<string,mixed>>} */
    public function dashboard(?int $trackedIngredientId = null): array
    {
        $onHand = DB::table('stock_balances')
            ->selectRaw('COALESCE(SUM(quantity_on_hand), 0)')
            ->whereColumn('stock_balances.ingredient_id', 'ingredients.id');

        $recentMovements = StockMovement::query()->with(['ingredient.baseUnit', 'stockLocation'])
            ->orderByDesc('movement_date')->orderByDesc('id')->limit(8)->get();
        $trackedIngredients = Ingredient::query()
            ->where('is_active', true)
            ->whereHas('stockMovements')
            ->with('baseUnit')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'base_unit_id']);
        $selectedIngredient = $trackedIngredients->firstWhere('id', $trackedIngredientId)
            ?? $trackedIngredients->firstWhere('id', $recentMovements->first()?->ingredient_id)
            ?? $trackedIngredients->first();
        $movementChart = $this->movementTrend(
            $selectedIngredient?->id,
            $selectedIngredient?->baseUnit?->abbreviation,
        );

        return [
            'ingredientCount' => Ingredient::query()->where('is_active', true)->count(),
            'locationCount' => StockLocation::query()->where('is_active', true)->count(),
            'postedWasteCount' => StockWaste::query()->where('status', 'posted')->count(),
            'lowStockCount' => Ingredient::query()->where('is_active', true)
                ->whereRaw('minimum_stock > ('.$onHand->toSql().')', $onHand->getBindings())->count(),
            'openOrderCount' => PurchaseOrder::query()->whereIn('status', ['ordered', 'partially_received'])->count(),
            'draftReceiptCount' => GoodsReceipt::query()->where('status', 'draft')->count(),
            'recentMovements' => $recentMovements,
            'trackedIngredients' => $trackedIngredients,
            'trackedIngredientId' => $selectedIngredient?->id,
            'movementChart' => $movementChart,
            'replenishmentSuggestions' => $this->replenishmentSuggestions(),
        ];
    }

    /** @return array{unit:?string,days:list<array{label:string,in:float,out:float,x:int,inY:int,outY:int}>,ticks:list<array{label:string,y:int}>,inPoints:string,outPoints:string,hasData:bool} */
    private function movementTrend(?int $ingredientId, ?string $unit): array
    {
        $days = [];
        $start = today()->subDays(13);
        $rows = $ingredientId === null
            ? collect()
            : StockMovement::query()
                ->where('ingredient_id', $ingredientId)
                ->whereDate('movement_date', '>=', $start)
                ->whereDate('movement_date', '<=', today())
                ->selectRaw('DATE(movement_date) as movement_day, SUM(quantity_in) as quantity_in, SUM(quantity_out) as quantity_out')
                ->groupByRaw('DATE(movement_date)')
                ->get()
                ->keyBy('movement_day');

        for ($index = 0; $index < 14; $index++) {
            $date = $start->copy()->addDays($index);
            $row = $rows->get($date->toDateString());
            $days[] = [
                'label' => $date->format('M j'),
                'in' => (float) ($row?->quantity_in ?? 0),
                'out' => (float) ($row?->quantity_out ?? 0),
            ];
        }

        $maximum = max(1, ...array_merge(
            array_column($days, 'in'),
            array_column($days, 'out'),
        ));
        $plotTop = 24;
        $plotBottom = 210;
        $plotHeight = $plotBottom - $plotTop;
        $plotLeft = 60;
        $plotWidth = 700;

        foreach ($days as $index => &$day) {
            $day['x'] = (int) round($plotLeft + ($plotWidth * $index / 13));
            $day['inY'] = (int) round($plotBottom - ($day['in'] / $maximum * $plotHeight));
            $day['outY'] = (int) round($plotBottom - ($day['out'] / $maximum * $plotHeight));
        }
        unset($day);

        $ticks = [];
        foreach ([0, 0.5, 1] as $fraction) {
            $ticks[] = [
                'label' => number_format($maximum * $fraction, $maximum < 10 ? 1 : 0),
                'y' => (int) round($plotBottom - ($fraction * $plotHeight)),
            ];
        }

        return [
            'unit' => $unit,
            'days' => $days,
            'ticks' => $ticks,
            'inPoints' => collect($days)->map(fn (array $day) => $day['x'].','.$day['inY'])->implode(' '),
            'outPoints' => collect($days)->map(fn (array $day) => $day['x'].','.$day['outY'])->implode(' '),
            'hasData' => collect($days)->contains(fn (array $day) => $day['in'] > 0 || $day['out'] > 0),
        ];
    }

    /** @return list<array{ingredient:Ingredient,onHand:float,minimum:float,shortage:float,incoming:float,uncoveredShortage:float,supplier:mixed,packageUnit:mixed,packageQuantity:float,packagePrice:float,priceDate:mixed,supplierPrices:list<array<string,mixed>>,packages:int,estimatedCost:float,orderable:bool,reason:string}> */
    public function replenishmentSuggestions(int $limit = 10): array
    {
        $onHand = DB::table('stock_balances')
            ->selectRaw('COALESCE(SUM(quantity_on_hand), 0)')
            ->whereColumn('stock_balances.ingredient_id', 'ingredients.id');

        $ingredients = Ingredient::query()
            ->where('is_active', true)
            ->whereRaw('minimum_stock > ('.$onHand->toSql().')', $onHand->getBindings())
            ->withSum('stockBalances as total_on_hand', 'quantity_on_hand')
            ->with('baseUnit')
            ->orderByRaw('COALESCE((select SUM(quantity_on_hand) from stock_balances where stock_balances.ingredient_id = ingredients.id), 0) / NULLIF(ingredients.minimum_stock, 0)')
            ->orderBy('name')
            ->limit(500)
            ->get();

        $openOrderItems = PurchaseOrderItem::query()
            ->whereIn('ingredient_id', $ingredients->modelKeys())
            ->whereHas('purchaseOrder', fn ($orders) => $orders->whereIn('status', ['ordered', 'partially_received']))
            ->with(['ingredient.baseUnit', 'unit'])
            ->get();

        $incomingByIngredient = [];

        if ($openOrderItems->isNotEmpty()) {
            $receivedByOrderItem = DB::table('goods_receipt_items')
                ->join('goods_receipts', 'goods_receipts.id', '=', 'goods_receipt_items.goods_receipt_id')
                ->where('goods_receipts.status', 'posted')
                ->whereIn('goods_receipt_items.purchase_order_item_id', $openOrderItems->modelKeys())
                ->selectRaw('goods_receipt_items.purchase_order_item_id, SUM(goods_receipt_items.quantity_received) as received_quantity')
                ->groupBy('goods_receipt_items.purchase_order_item_id')
                ->pluck('received_quantity', 'purchase_order_item_id');

            foreach ($openOrderItems as $orderItem) {
                $remainingQuantity = max(
                    0,
                    (float) $orderItem->quantity - (float) ($receivedByOrderItem->get($orderItem->id) ?? 0),
                );

                if ($remainingQuantity <= 0) {
                    continue;
                }

                try {
                    $baseQuantity = $remainingQuantity * $this->units->conversionFactor(
                        $orderItem->unit,
                        $orderItem->ingredient->baseUnit,
                    );
                } catch (\Illuminate\Validation\ValidationException) {
                    continue;
                }

                $ingredientId = (int) $orderItem->ingredient_id;
                $incomingByIngredient[$ingredientId] = ($incomingByIngredient[$ingredientId] ?? 0) + $baseQuantity;
            }
        }

        $priceHistoriesByIngredient = IngredientPriceHistory::query()
            ->with(['supplier', 'packageUnit'])
            ->whereIn('ingredient_id', $ingredients->modelKeys())
            ->where('effective_date', '<=', today())
            ->whereNotExists(function ($newerPrices): void {
                $newerPrices->selectRaw('1')
                    ->from('ingredient_price_histories as newer')
                    ->whereColumn('newer.ingredient_id', 'ingredient_price_histories.ingredient_id')
                    ->whereColumn('newer.supplier_id', 'ingredient_price_histories.supplier_id')
                    ->where('newer.effective_date', '<=', today())
                    ->where(function ($query): void {
                        $query->whereColumn('newer.effective_date', '>', 'ingredient_price_histories.effective_date')
                            ->orWhere(function ($sameDate): void {
                                $sameDate->whereColumn('newer.effective_date', 'ingredient_price_histories.effective_date')
                                    ->whereColumn('newer.id', '>', 'ingredient_price_histories.id');
                            });
                    });
            })
            ->whereHas('supplier', fn ($suppliers) => $suppliers->where('is_active', true))
            ->whereHas('packageUnit', fn ($units) => $units->where('is_active', true))
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->get()
            ->groupBy('ingredient_id');

        return $ingredients->map(function (Ingredient $ingredient) use ($incomingByIngredient, $priceHistoriesByIngredient): array {
            $onHand = (float) ($ingredient->total_on_hand ?? 0);
            $minimum = (float) $ingredient->minimum_stock;
            $shortage = max(0, round($minimum - $onHand, 3));
            $incoming = round((float) ($incomingByIngredient[$ingredient->id] ?? 0), 3);
            $uncoveredShortage = max(0, round($shortage - $incoming, 3));
            $supplierPrices = $this->supplierPriceOptions(
                $ingredient,
                $priceHistoriesByIngredient->get($ingredient->id, collect()),
            );
            $bestPrice = $supplierPrices[0] ?? null;
            $supplier = $bestPrice['supplier'] ?? null;
            $packageUnit = $bestPrice['packageUnit'] ?? null;
            $packageQuantity = (float) ($bestPrice['packageQuantity'] ?? 0);
            $packagePrice = (float) ($bestPrice['packagePrice'] ?? 0);
            $priceDate = $bestPrice['priceDate'] ?? null;
            $packages = $bestPrice !== null && $uncoveredShortage > 0
                ? (int) ceil($uncoveredShortage / $bestPrice['baseQuantity'])
                : 0;
            $estimatedCost = round($packages * $packagePrice, 2);
            $orderable = $bestPrice !== null && $packages > 0;
            $reason = $this->replenishmentReason(
                $onHand,
                $minimum,
                $shortage,
                $incoming,
                $uncoveredShortage,
                $bestPrice,
                $packages,
                count($supplierPrices),
                $ingredient->baseUnit?->abbreviation,
            );

            return compact(
                'ingredient', 'onHand', 'minimum', 'shortage', 'incoming', 'uncoveredShortage',
                'supplier', 'packageUnit', 'packageQuantity', 'packagePrice', 'priceDate',
                'supplierPrices', 'packages', 'estimatedCost', 'orderable', 'reason',
            );
        })
            ->filter(fn (array $suggestion): bool => $suggestion['shortage'] > 0)
            ->sortBy(fn (array $suggestion): float => ($suggestion['onHand'] + $suggestion['incoming']) / $suggestion['minimum'])
            ->take($limit)
            ->all();
    }

    /** @return list<array{supplier:mixed,packageUnit:mixed,packageQuantity:float,packagePrice:float,baseQuantity:float,unitCost:float,priceDate:mixed}> */
    private function supplierPriceOptions(Ingredient $ingredient, Collection $histories): array
    {
        $seenSuppliers = [];
        $options = [];

        foreach ($histories as $history) {
            $supplierId = (int) $history->supplier_id;

            if (isset($seenSuppliers[$supplierId])) {
                continue;
            }

            $seenSuppliers[$supplierId] = true;
            $packageQuantity = (float) $history->package_quantity;

            if ($packageQuantity <= 0 || $history->package_unit_id === null) {
                continue;
            }

            try {
                $baseQuantity = $packageQuantity * $this->units->conversionFactor(
                    $history->packageUnit,
                    $ingredient->baseUnit,
                );
            } catch (\Illuminate\Validation\ValidationException) {
                continue;
            }

            if ($baseQuantity <= 0) {
                continue;
            }

            $packagePrice = (float) $history->package_price;
            $options[] = [
                'supplier' => $history->supplier,
                'packageUnit' => $history->packageUnit,
                'packageQuantity' => $packageQuantity,
                'packagePrice' => $packagePrice,
                'baseQuantity' => $baseQuantity,
                'unitCost' => round($packagePrice / $baseQuantity, 6),
                'priceDate' => $history->effective_date,
            ];
        }

        usort($options, fn (array $left, array $right): int => $left['unitCost'] <=> $right['unitCost']);

        return $options;
    }

    private function replenishmentReason(
        float $onHand,
        float $minimum,
        float $shortage,
        float $incoming,
        float $uncoveredShortage,
        ?array $bestPrice,
        int $packages,
        int $supplierCount,
        ?string $baseUnit,
    ): string {
        $reason = sprintf(
            'On hand is %s %s, below the minimum of %s %s by %s %s.',
            number_format($onHand, 3),
            $baseUnit,
            number_format($minimum, 3),
            $baseUnit,
            number_format($shortage, 3),
            $baseUnit,
        );

        if ($incoming > 0) {
            $reason .= sprintf(
                ' Open purchase orders have %s %s remaining, leaving %s %s uncovered.',
                number_format($incoming, 3),
                $baseUnit,
                number_format($uncoveredShortage, 3),
                $baseUnit,
            );
        }

        if ($uncoveredShortage <= 0) {
            return $reason.' Incoming stock covers the shortfall; no duplicate order is suggested.';
        }

        if ($bestPrice === null) {
            return $reason.' Add an active supplier price with a unit compatible with this inventory item to prepare a draft.';
        }

        $recommendation = sprintf(
            ' %d package(s) from %s cover the remaining need.',
            $packages,
            $bestPrice['supplier']->name,
        );

        if ($supplierCount > 1) {
            $recommendation .= sprintf(
                ' This supplier has the lowest latest comparable unit price among %d active suppliers.',
                $supplierCount,
            );
        } else {
            $recommendation .= ' This is the latest compatible active supplier quote.';
        }

        return $reason.$recommendation;
    }
}
