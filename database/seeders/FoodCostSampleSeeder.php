<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\IngredientCategory;
use App\Models\IngredientPriceHistory;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\RecipeIngredient;
use App\Models\RecipeOtherCost;
use App\Models\RecipeVersion;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class FoodCostSampleSeeder extends Seeder
{
    /**
     * Add a small, repeatable sample of the Skybar food-cost workbook.
     *
     * Run with: php artisan db:seed --class=FoodCostSampleSeeder
     */
    public function run(): void
    {
        if (! Schema::hasColumns('menu_items', ['sale_mode', 'stock_ingredient_id', 'stock_quantity'])) {
            throw new RuntimeException(
                'The database is missing the POS menu-item columns. Run `php artisan migrate` first, then run this seeder again.'
            );
        }

        DB::transaction(function (): void {
            $gram = $this->unit('Gram', 'g', 'mass');
            $each = $this->unit('Each', 'ea', 'count');
            $ingredientCategory = $this->category(IngredientCategory::class, 'Sample Data - Food Ingredients');
            $menuCategory = $this->category(MenuCategory::class, 'Sample Data - Food');
            $today = Carbon::today()->toDateString();

            foreach ($this->dishes() as $dish) {
                $menuItem = MenuItem::withTrashed()->firstOrNew(['code' => $dish['code']]);
                $menuItem->fill([
                    'menu_category_id' => $menuCategory->id,
                    'name' => $dish['name'],
                    'description' => 'Sample menu item from Food Cost Skybar.xlsx. Imported for testing.',
                    'selling_price' => $dish['selling_price'],
                    'currency' => 'USD',
                    'sale_mode' => 'recipe',
                    'stock_ingredient_id' => null,
                    'stock_quantity' => null,
                    'is_active' => true,
                ]);
                if ($menuItem->trashed()) {
                    $menuItem->restore();
                }
                $menuItem->save();

                $recipe = RecipeVersion::firstOrNew([
                    'menu_item_id' => $menuItem->id,
                    'version_number' => 1,
                ]);
                $recipe->fill([
                    'yield_quantity' => 1,
                    'yield_unit_id' => $each->id,
                    'is_current' => true,
                    'effective_from' => $today,
                    'effective_until' => null,
                    'notes' => 'Sample recipe imported from Food Cost Skybar.xlsx.',
                ]);
                $recipe->save();

                // Keep repeated runs deterministic and only replace this sample recipe's lines.
                RecipeIngredient::where('recipe_version_id', $recipe->id)->delete();
                RecipeOtherCost::where('recipe_version_id', $recipe->id)->delete();

                foreach ($dish['ingredients'] as $sort => $line) {
                    $unit = $line['unit'] === 'each' ? $each : $gram;
                    $ingredient = $this->ingredient(
                        $line['name'],
                        $ingredientCategory,
                        $unit,
                        $line['quantity'],
                        $line['cost'],
                        $today,
                    );

                    RecipeIngredient::create([
                        'recipe_version_id' => $recipe->id,
                        'ingredient_id' => $ingredient->id,
                        'quantity' => $line['quantity'],
                        'unit_id' => $unit->id,
                        'waste_percentage' => 0,
                        'unit_cost_snapshot' => $line['cost'] / $line['quantity'],
                        'line_cost_snapshot' => $line['cost'],
                        'sort_order' => $sort + 1,
                        'notes' => 'Imported workbook line cost.',
                    ]);
                }

                foreach ($dish['other_costs'] as $sort => $cost) {
                    RecipeOtherCost::create([
                        'recipe_version_id' => $recipe->id,
                        'name' => $cost['name'],
                        'amount' => $cost['amount'],
                        'cost_type' => 'fixed',
                        'notes' => 'Imported workbook cost without a measurable ingredient quantity.',
                        'sort_order' => $sort + 1,
                    ]);
                }
            }
        });
    }

    private function unit(string $name, string $abbreviation, string $dimension): Unit
    {
        return Unit::firstOrCreate(
            ['abbreviation' => $abbreviation],
            [
                'name' => $name,
                'dimension' => $dimension,
                'conversion_factor_to_base' => 1,
                'is_base_unit' => true,
                'is_active' => true,
            ],
        );
    }

    /** Create or restore one of the clearly labelled sample categories. */
    private function category(string $model, string $name): IngredientCategory|MenuCategory
    {
        $category = $model::withTrashed()->firstOrNew(['name' => $name]);
        $category->fill(['description' => 'Created by the Skybar workbook sample seeder.', 'is_active' => true]);
        if ($category->trashed()) {
            $category->restore();
        }
        $category->save();

        return $category;
    }

    private function ingredient(
        string $name,
        IngredientCategory $category,
        Unit $unit,
        float $quantity,
        float $lineCost,
        string $effectiveDate,
    ): Ingredient {
        $code = 'SAMPLE-' . strtoupper(substr(hash('sha256', $name), 0, 12));
        $ingredient = Ingredient::withTrashed()->firstOrNew(['code' => $code]);
        $ingredient->fill([
            'name' => $name,
            'ingredient_category_id' => $category->id,
            'base_unit_id' => $unit->id,
            'description' => 'Sample ingredient from Food Cost Skybar.xlsx.',
            'minimum_stock' => 0,
            'is_active' => true,
        ]);
        if ($ingredient->trashed()) {
            $ingredient->restore();
        }
        $ingredient->save();

        // The source workbook gives the cost consumed by each recipe line. Store its
        // implied base-unit cost so new recipe lines can use the normal cost workflow.
        $unitCost = $lineCost / $quantity;
        $packageQuantity = $unit->dimension === 'mass' ? 1000 : 1;
        IngredientPriceHistory::updateOrCreate(
            [
                'ingredient_id' => $ingredient->id,
                'supplier_id' => null,
                'effective_date' => $effectiveDate,
                'notes' => 'Sample cost imported from Food Cost Skybar.xlsx.',
            ],
            [
                'package_price' => round($unitCost * $packageQuantity, 2),
                'package_quantity' => $packageQuantity,
                'package_unit_id' => $unit->id,
                'unit_cost' => $unitCost,
            ],
        );

        return $ingredient;
    }

    /**
     * Measured quantities and line costs are transcribed from Sheet1. Rows that
     * only have a cost in the workbook are represented as recipe other-cost lines.
     *
     * @return array<int, array<string, mixed>>
     */
    private function dishes(): array
    {
        return [
            [
                'code' => 'SAMPLE-FOOD-BEEF-JERKY',
                'name' => 'គោងៀត',
                'selling_price' => 3.50,
                'ingredients' => [
                    ['name' => 'សាច់គោ', 'quantity' => 100, 'unit' => 'gram', 'cost' => 0.90],
                    ['name' => 'រំដេង', 'quantity' => 90, 'unit' => 'gram', 'cost' => 0.09],
                    ['name' => 'ស្ករស', 'quantity' => 150, 'unit' => 'gram', 'cost' => 0.10],
                    ['name' => 'ប៊ីចេង', 'quantity' => 10, 'unit' => 'gram', 'cost' => 0.03],
                    ['name' => 'អំបិល', 'quantity' => 15, 'unit' => 'gram', 'cost' => 0.01],
                ],
                'other_costs' => [['name' => 'ផ្សេងៗ', 'amount' => 0.20]],
            ],
            [
                'code' => 'SAMPLE-FOOD-CORN-SALTED-EGG',
                'name' => 'ពោតលីង / ពងប្រៃ / ហៃប៊ី',
                'selling_price' => 3.50,
                'ingredients' => [
                    ['name' => 'ពោត', 'quantity' => 200, 'unit' => 'gram', 'cost' => 0.35],
                    ['name' => 'ពងប្រៃ ឬ ហៃប៊ី', 'quantity' => 1, 'unit' => 'each', 'cost' => 0.15],
                    ['name' => 'ប័រ', 'quantity' => 20, 'unit' => 'gram', 'cost' => 0.10],
                    ['name' => 'រ៉ត់ឌី', 'quantity' => 5, 'unit' => 'gram', 'cost' => 0.02],
                    ['name' => 'ស្លឹកខ្ទឹម', 'quantity' => 10, 'unit' => 'gram', 'cost' => 0.02],
                ],
                'other_costs' => [['name' => 'ផ្សេងៗ', 'amount' => 0.25]],
            ],
            [
                'code' => 'SAMPLE-FOOD-HOT-CHICKEN',
                'name' => 'ឆាក្តៅសាច់មាន់',
                'selling_price' => 4.50,
                'ingredients' => [
                    ['name' => 'ភ្លៅមាន់', 'quantity' => 200, 'unit' => 'gram', 'cost' => 0.65],
                    ['name' => 'គល់ស្លឹកគ្រៃ', 'quantity' => 50, 'unit' => 'gram', 'cost' => 0.03],
                    ['name' => 'ស្លឹកក្រូចសើច', 'quantity' => 5, 'unit' => 'gram', 'cost' => 0.01],
                    ['name' => 'ខ្ទឹមសរ', 'quantity' => 10, 'unit' => 'gram', 'cost' => 0.02],
                    ['name' => 'ខ្ទឹមក្រហម', 'quantity' => 15, 'unit' => 'gram', 'cost' => 0.01],
                    ['name' => 'រំដេង', 'quantity' => 15, 'unit' => 'gram', 'cost' => 0.02],
                    ['name' => 'ម្ទេសដៃនាងទុំ', 'quantity' => 50, 'unit' => 'gram', 'cost' => 0.08],
                    ['name' => 'ម្ទេសហិល', 'quantity' => 20, 'unit' => 'gram', 'cost' => 0.03],
                    ['name' => 'ទឹកSauceឆា', 'quantity' => 15, 'unit' => 'gram', 'cost' => 0.15],
                    ['name' => 'ម្រះព្រៅ', 'quantity' => 1, 'unit' => 'each', 'cost' => 0.05],
                ],
                'other_costs' => [['name' => 'ផ្សេងៗ', 'amount' => 0.20]],
            ],
            [
                'code' => 'SAMPLE-FOOD-FRENCH-FRIES',
                'name' => 'French Fries',
                'selling_price' => 3.00,
                'ingredients' => [
                    ['name' => 'French Fries', 'quantity' => 200, 'unit' => 'gram', 'cost' => 0.65],
                    ['name' => 'ទឹកប៉េងប៉ោះ', 'quantity' => 5, 'unit' => 'gram', 'cost' => 0.05],
                ],
                'other_costs' => [['name' => 'ផ្សេងៗ', 'amount' => 0.20]],
            ],
            [
                'code' => 'SAMPLE-FOOD-FRIED-CALAMARI',
                'name' => 'មឹកបំពងខ្ទឹមសរ / អំបិលម្ទេស',
                'selling_price' => 5.00,
                'ingredients' => [
                    ['name' => 'មឹក', 'quantity' => 150, 'unit' => 'gram', 'cost' => 1.31],
                    ['name' => 'ម្សៅបំពង', 'quantity' => 5, 'unit' => 'gram', 'cost' => 0.03],
                    ['name' => 'អំបិលម្ទេស', 'quantity' => 5, 'unit' => 'gram', 'cost' => 0.01],
                    ['name' => 'ទឹកត្រីកោះកុង', 'quantity' => 2, 'unit' => 'gram', 'cost' => 0.05],
                ],
                'other_costs' => [['name' => 'ផ្សេងៗ', 'amount' => 0.20]],
            ],
            [
                'code' => 'SAMPLE-FOOD-DUCK-TONGUE',
                'name' => 'អណ្តាតទាបំពងខ្ទឹមសរ',
                'selling_price' => 3.50,
                'ingredients' => [
                    ['name' => 'អណ្តាតទា', 'quantity' => 100, 'unit' => 'gram', 'cost' => 0.83],
                    ['name' => 'ខ្ទឹមសរ', 'quantity' => 15, 'unit' => 'gram', 'cost' => 0.04],
                ],
                'other_costs' => [
                    ['name' => 'ទឹកម្រេច', 'amount' => 0.03],
                    ['name' => 'ផ្សេងៗ', 'amount' => 0.20],
                ],
            ],
        ];
    }
}
