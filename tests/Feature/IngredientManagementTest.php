<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\IngredientCategory;
use App\Models\StockBalance;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class IngredientManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_ingredient_index_uses_database_records_and_stock_balances(): void
    {
        $unit = $this->createUnit();
        $category = IngredientCategory::create(['name' => 'Produce']);
        $ingredient = Ingredient::create([
            'code' => 'CHICKEN',
            'name' => 'Chicken breast',
            'ingredient_category_id' => $category->id,
            'base_unit_id' => $unit->id,
            'minimum_stock' => 2.5,
            'is_active' => true,
        ]);
        $locationId = \DB::table('stock_locations')->insertGetId([
            'code' => 'MAIN',
            'name' => 'Main store',
            'is_active' => true,
        ]);
        StockBalance::create([
            'ingredient_id' => $ingredient->id,
            'stock_location_id' => $locationId,
            'quantity_on_hand' => 6.25,
            'average_unit_cost' => 4.5,
        ]);
        $lowStockIngredient = Ingredient::create([
            'code' => 'LOW-STOCK',
            'name' => 'Low stock ingredient',
            'base_unit_id' => $unit->id,
            'minimum_stock' => 5,
            'is_active' => true,
        ]);
        StockBalance::create([
            'ingredient_id' => $lowStockIngredient->id,
            'stock_location_id' => $locationId,
            'quantity_on_hand' => 2,
            'average_unit_cost' => 1,
        ]);
        Ingredient::create([
            'code' => 'OUT-OF-STOCK',
            'name' => 'Out of stock ingredient',
            'base_unit_id' => $unit->id,
            'minimum_stock' => 1,
            'is_active' => true,
        ]);

        $this->get(route('items.index', ['status' => 'active']))
            ->assertOk()
            ->assertSee('Chicken breast')
            ->assertSee('Low stock ingredient')
            ->assertSee('Out of stock ingredient')
            ->assertSee('CHICKEN')
            ->assertSee('6.250')
            ->assertSee('2.500')
            ->assertSee('ingredient-card-grid', false)
            ->assertSee('In stock')
            ->assertSee('Low stock')
            ->assertSee('Out of stock')
            ->assertSee('Kilogram (kg)')
            ->assertSee('(kg)')
            ->assertSee('Search by code, name, or description')
            ->assertSee('name="status"', false)
            ->assertSee('data-select2="true"', false)
            ->assertSee('data-placeholder="Show all"', false)
            ->assertSee('value="active" selected', false);
    }

    public function test_ingredient_pages_only_expose_the_index_listing(): void
    {
        $this->assertTrue(Route::has('items.index'));
        $this->assertFalse(Route::has('items.create'));
        $this->assertFalse(Route::has('items.edit'));
        $this->assertFalse(Route::has('items.store'));
        $this->assertFalse(Route::has('items.update'));
        $this->assertFalse(Route::has('items.destroy'));
        $this->get('/items/create')->assertNotFound();
    }

    public function test_dashboard_displays_live_database_totals(): void
    {
        $category = IngredientCategory::create(['name' => 'Seasoning']);
        Ingredient::create([
            'code' => 'SALT',
            'name' => 'Salt',
            'ingredient_category_id' => $category->id,
            'base_unit_id' => $this->createUnit()->id,
            'minimum_stock' => 0,
            'is_active' => true,
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('>1</span>', false)
            ->assertSee('>0</span>', false);
    }

    private function createUnit(): Unit
    {
        return Unit::create([
            'name' => 'Kilogram',
            'abbreviation' => 'kg',
            'dimension' => 'mass',
            'conversion_factor_to_base' => 1,
            'is_base_unit' => true,
            'is_active' => true,
        ]);
    }
}
