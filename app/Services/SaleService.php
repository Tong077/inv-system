<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\RecipeVersion;
use App\Models\Sale;
use App\Models\StockBalance;
use App\Models\StockLocation;
use App\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function __construct(
        private readonly UnitConversionService $units,
        private readonly DocumentNumberService $documentNumbers,
    ) {}

    /** @return array{menuItems: Collection<int, MenuItem>, locations: Collection<int, StockLocation>} */
    public function getPosOptions(): array
    {
        $today = today()->toDateString();
        $eligibleRecipe = fn (Builder $query) => $query
            ->where('is_current', true)
            ->where(fn (Builder $dates) => $dates->whereNull('effective_from')->orWhereDate('effective_from', '<=', $today))
            ->where(fn (Builder $dates) => $dates->whereNull('effective_until')->orWhereDate('effective_until', '>=', $today))
            ->whereHas('ingredients');

        $menuItems = MenuItem::query()
                ->where('is_active', true)
                ->where(function (Builder $query) use ($eligibleRecipe): void {
                    $query->where(fn (Builder $recipe) => $recipe->where('sale_mode', 'recipe')->whereHas('currentRecipe', $eligibleRecipe))
                        ->orWhere(fn (Builder $stock) => $stock->where('sale_mode', 'stock_item')->whereHas('stockIngredient', fn (Builder $ingredient) => $ingredient->where('is_active', true)));
                })
                ->with(['category', 'stockIngredient.baseUnit', 'currentRecipe.ingredients.ingredient.baseUnit', 'currentRecipe.ingredients.unit'])
                ->orderBy('name')
                ->get();
        $locations = StockLocation::query()->where('is_active', true)->orderBy('name')->get();

        $ingredientIds = $menuItems->flatMap(fn (MenuItem $menuItem) => collect([
            $menuItem->stock_ingredient_id,
            ...($menuItem->currentRecipe?->ingredients->pluck('ingredient_id')->all() ?? []),
        ]))
            ->unique()->values();
        $balances = $ingredientIds->isEmpty() || $locations->isEmpty()
            ? collect()
            : DB::table('stock_balances')
                ->whereIn('ingredient_id', $ingredientIds)
                ->whereIn('stock_location_id', $locations->modelKeys())
                ->selectRaw('ingredient_id, stock_location_id, SUM(quantity_on_hand) as quantity_on_hand')
                ->groupBy('ingredient_id', 'stock_location_id')
                ->get()
                ->mapWithKeys(fn ($balance): array => [
                    $balance->stock_location_id.':'.$balance->ingredient_id => (float) $balance->quantity_on_hand,
                ]);

        foreach ($menuItems as $menuItem) {
            $perPortionUsage = [];
            if ($menuItem->isDirectStockSale()) {
                if ($menuItem->stockIngredient !== null && (float) $menuItem->stock_quantity > 0) {
                    $perPortionUsage[(int) $menuItem->stock_ingredient_id] = (float) $menuItem->stock_quantity;
                }
            }

            $recipe = $menuItem->currentRecipe;
            $yieldQuantity = (float) ($recipe?->yield_quantity ?? 0);

            if (! $menuItem->isDirectStockSale() && $recipe !== null && $yieldQuantity > 0) {
                foreach ($recipe->ingredients as $line) {
                    if ($line->ingredient === null || $line->unit === null || $line->ingredient->baseUnit === null) {
                        continue;
                    }

                    try {
                        $factor = $this->units->conversionFactor($line->unit, $line->ingredient->baseUnit);
                    } catch (ValidationException) {
                        continue;
                    }

                    $ingredientId = (int) $line->ingredient_id;
                    $perPortionUsage[$ingredientId] = ($perPortionUsage[$ingredientId] ?? 0)
                        + ((float) $line->quantity * (1 + ((float) $line->waste_percentage / 100)) / $yieldQuantity * $factor);
                }
            }

            $availableQuantities = [];
            foreach ($locations as $location) {
                $available = $perPortionUsage === [] ? 0 : INF;
                foreach ($perPortionUsage as $ingredientId => $required) {
                    $onHand = $balances->get($location->id.':'.$ingredientId, 0.0);
                    $available = min($available, $required > 0 ? $onHand / $required : INF);
                }

                $availableQuantities[$location->id] = is_finite($available)
                    ? floor(($available + 0.0000001) * 1000) / 1000
                    : 0;
            }

            $menuItem->setAttribute('available_quantities', $availableQuantities);
        }

        return compact('menuItems', 'locations');
    }

    public function getSales(?string $search = null): LengthAwarePaginator
    {
        return Sale::query()
            ->with('stockLocation')
            ->withCount('items')
            ->when($search !== null && $search !== '', function (Builder $query) use ($search): void {
                $query->where('receipt_number', 'like', "%{$search}%")
                    ->orWhereHas('items', fn (Builder $items) => $items->where('item_name', 'like', "%{$search}%"));
            })
            ->orderByDesc('sold_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function findSale(Sale $sale): Sale
    {
        return $sale->load(['stockLocation', 'items.stockIngredient.baseUnit']);
    }

    /** @param array{stock_location_id:int,payment_method:string,amount_tendered?:int|float|string|null,items:array<int|string, int|float|string>} $data */
    public function checkout(array $data): Sale
    {
        return DB::transaction(function () use ($data): Sale {
            foreach (array_keys($data['items']) as $menuItemId) {
                if (! ctype_digit((string) $menuItemId) || (int) $menuItemId < 1) {
                    throw ValidationException::withMessages(['items' => 'The sale contains an invalid menu item.']);
                }
            }

            $quantities = collect($data['items'])
                ->mapWithKeys(fn ($quantity, $id): array => [(int) $id => round((float) $quantity, 3)])
                ->filter(fn (float $quantity): bool => $quantity > 0);

            if ($quantities->isEmpty()) {
                throw ValidationException::withMessages(['items' => 'Add at least one menu item to the sale.']);
            }

            $location = StockLocation::query()
                ->where('is_active', true)
                ->whereKey($data['stock_location_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $today = today()->toDateString();
            $items = MenuItem::query()
                ->where('is_active', true)
                ->whereIn('id', $quantities->keys())
                ->where(function (Builder $query) use ($today): void {
                    $query->where(fn (Builder $recipe) => $recipe->where('sale_mode', 'recipe')->whereHas('currentRecipe', fn (Builder $version) => $version
                        ->where('is_current', true)
                        ->where(fn (Builder $dates) => $dates->whereNull('effective_from')->orWhereDate('effective_from', '<=', $today))
                        ->where(fn (Builder $dates) => $dates->whereNull('effective_until')->orWhereDate('effective_until', '>=', $today))
                        ->whereHas('ingredients')))
                        ->orWhere(fn (Builder $stock) => $stock->where('sale_mode', 'stock_item')->whereHas('stockIngredient', fn (Builder $ingredient) => $ingredient->where('is_active', true)));
                })
                ->with(['stockIngredient.baseUnit', 'currentRecipe.ingredients.ingredient.baseUnit', 'currentRecipe.ingredients.unit'])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($items->count() !== $quantities->count()) {
                throw ValidationException::withMessages(['items' => 'One or more menu items are inactive or not configured for stock deduction.']);
            }

            $currencies = $items->pluck('currency')->unique();
            if ($currencies->count() !== 1) {
                throw ValidationException::withMessages(['items' => 'All items in one sale must use the same currency.']);
            }

            $recipeIds = $items->pluck('currentRecipe.id')->filter()->unique()->sort()->values();
            RecipeVersion::query()->whereIn('id', $recipeIds)->orderBy('id')->lockForUpdate()->get();

            $ingredientUsage = [];
            foreach ($items as $menuItem) {
                $soldQuantity = (float) $quantities->get($menuItem->id);

                if ($menuItem->isDirectStockSale()) {
                    $ingredient = $menuItem->stockIngredient;
                    $stockQuantity = (float) $menuItem->stock_quantity;

                    if ($ingredient === null || ! $ingredient->is_active || $stockQuantity <= 0) {
                        throw ValidationException::withMessages(['items' => "Set an active inventory item and positive stock quantity for {$menuItem->name}."]);
                    }

                    $ingredientId = (int) $ingredient->id;
                    $ingredientUsage[$ingredientId] = ($ingredientUsage[$ingredientId] ?? 0) + ($stockQuantity * $soldQuantity);
                    continue;
                }

                $recipe = $menuItem->currentRecipe;
                $yieldQuantity = (float) $recipe->yield_quantity;
                if ($yieldQuantity <= 0) {
                    throw ValidationException::withMessages(['items' => "The recipe yield for {$menuItem->name} must be greater than zero."]);
                }

                if ($recipe->ingredients->contains(fn ($line): bool => $line->ingredient === null || $line->unit === null || $line->ingredient->baseUnit === null)) {
                    throw ValidationException::withMessages(['items' => "The recipe for {$menuItem->name} has a missing ingredient or unit. Review it before selling."]);
                }

                foreach ($recipe->ingredients as $recipeIngredient) {
                    $ingredient = $recipeIngredient->ingredient;
                    try {
                        $factor = $this->units->conversionFactor($recipeIngredient->unit, $ingredient->baseUnit);
                    } catch (ValidationException) {
                        throw ValidationException::withMessages(['items' => "The recipe unit for {$ingredient->name} is not compatible with its base unit."]);
                    }

                    $baseQuantity = (float) $recipeIngredient->quantity
                        * (1 + ((float) $recipeIngredient->waste_percentage / 100))
                        * $soldQuantity / $yieldQuantity
                        * $factor;
                    $ingredientId = (int) $ingredient->id;
                    $ingredientUsage[$ingredientId] = ($ingredientUsage[$ingredientId] ?? 0) + $baseQuantity;
                }
            }

            ksort($ingredientUsage);
            foreach (array_keys($ingredientUsage) as $ingredientId) {
                Ingredient::query()->whereKey($ingredientId)->lockForUpdate()->firstOrFail();
            }

            $balances = [];
            foreach ($ingredientUsage as $ingredientId => $rawQuantity) {
                $quantity = round($rawQuantity, 3);
                $balance = StockBalance::query()
                    ->where('ingredient_id', $ingredientId)
                    ->where('stock_location_id', $location->id)
                    ->lockForUpdate()
                    ->first();

                if ($quantity <= 0 || $balance === null || (float) $balance->quantity_on_hand + 0.0005 < $quantity) {
                    $ingredient = Ingredient::query()->find($ingredientId);
                    $name = $ingredient?->name ?? 'An inventory item';
                    throw ValidationException::withMessages([
                        'items' => "Not enough {$name} at {$location->name} to complete this sale.",
                    ]);
                }

                $balances[$ingredientId] = ['model' => $balance, 'quantity' => $quantity];
            }

            $lines = [];
            $subtotal = 0.0;
            foreach ($items as $menuItem) {
                $quantity = (float) $quantities->get($menuItem->id);
                $unitPrice = (float) $menuItem->selling_price;
                $lineTotal = round($quantity * $unitPrice, 2);
                $subtotal += $lineTotal;
                $lines[] = [
                    'menu_item_id' => $menuItem->id,
                    'recipe_version_id' => $menuItem->currentRecipe?->id,
                    'stock_ingredient_id' => $menuItem->isDirectStockSale() ? $menuItem->stock_ingredient_id : null,
                    'stock_quantity' => $menuItem->isDirectStockSale()
                        ? round((float) $menuItem->stock_quantity * $quantity, 3)
                        : null,
                    'item_code' => $menuItem->code,
                    'item_name' => $menuItem->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            $total = round($subtotal, 2);
            $amountTendered = $data['payment_method'] === 'cash'
                ? round((float) ($data['amount_tendered'] ?? 0), 2)
                : $total;

            if ($amountTendered + 0.00001 < $total) {
                throw ValidationException::withMessages(['amount_tendered' => 'The amount received must cover the sale total.']);
            }

            $sale = Sale::query()->create([
                'receipt_number' => $this->documentNumbers->next('POS', 'sales', 'receipt_number'),
                'stock_location_id' => $location->id,
                'status' => 'posted',
                'sold_at' => now(),
                'currency' => $currencies->first(),
                'payment_method' => $data['payment_method'],
                'subtotal' => $total,
                'total' => $total,
                'amount_tendered' => $amountTendered,
                'change_due' => round($amountTendered - $total, 2),
                'sold_by' => auth()->id(),
            ]);
            $sale->items()->createMany($lines);

            foreach ($balances as $ingredientId => $entry) {
                /** @var StockBalance $balance */
                $balance = $entry['model'];
                $quantity = $entry['quantity'];
                $newQuantity = round((float) $balance->quantity_on_hand - $quantity, 3);
                $balance->update(['quantity_on_hand' => $newQuantity, 'last_movement_at' => now()]);

                StockMovement::query()->create([
                    'ingredient_id' => $ingredientId,
                    'stock_location_id' => $location->id,
                    'movement_type' => 'sale',
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                    'quantity_in' => 0,
                    'quantity_out' => $quantity,
                    'unit_cost' => $balance->average_unit_cost,
                    'balance_after' => $newQuantity,
                    'movement_date' => now(),
                    'notes' => $sale->receipt_number,
                    'created_by' => auth()->id(),
                ]);
            }

            return $sale;
        });
    }
}
