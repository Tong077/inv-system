<?php

namespace Tests\Feature;

use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_workflow_index_displays_database_records_with_related_data(): void
    {
        Unit::create([
            'name' => 'Gram',
            'abbreviation' => 'g',
            'dimension' => 'mass',
            'conversion_factor_to_base' => 1,
            'is_base_unit' => true,
            'is_active' => true,
        ]);

        $this->get(route('units.index'))
            ->assertOk()
            ->assertSee('Gram')
            ->assertSee('g')
            ->assertSee('Search records');
    }

    public function test_workflow_index_searches_only_matching_records(): void
    {
        foreach ([
            ['name' => 'Gram', 'abbreviation' => 'g'],
            ['name' => 'Millilitre', 'abbreviation' => 'ml'],
        ] as $unit) {
            Unit::create($unit + [
                'dimension' => 'count',
                'conversion_factor_to_base' => 1,
                'is_base_unit' => false,
                'is_active' => true,
            ]);
        }

        $this->get(route('units.index', ['search' => 'Gram']))
            ->assertOk()
            ->assertSee('Gram')
            ->assertDontSee('Millilitre');
    }

    public function test_table_component_paginates_and_preserves_search_query(): void
    {
        foreach (range(1, 16) as $number) {
            Unit::create([
                'name' => sprintf('Test unit %02d', $number),
                'abbreviation' => 't'.$number,
                'dimension' => 'count',
                'conversion_factor_to_base' => 1,
                'is_base_unit' => false,
                'is_active' => true,
            ]);
        }

        $this->get(route('units.index', ['search' => 'Test unit', 'page' => 2]))
            ->assertOk()
            ->assertSee('Test unit 01')
            ->assertSee('pagination')
            ->assertSee('search=Test%20unit', false);
    }

    public function test_stock_location_index_preserves_its_empty_state(): void
    {
        $this->get(route('stock-locations.index'))
            ->assertOk()
            ->assertSee('No stock locations yet')
            ->assertSee('Your storage locations will appear here when they are set up.');
    }

    public function test_inventory_workflow_pages_load_from_their_database_models(): void
    {
        $resources = [
            'units', 'menu-categories', 'suppliers', 'supplier-contacts',
            'stock-locations', 'menu-items', 'ingredient-price-histories',
            'purchase-orders', 'purchase-order-items', 'goods-receipts',
            'goods-receipt-items', 'supplier-payments', 'recipe-versions',
            'recipe-ingredients', 'recipe-instructions', 'recipe-other-costs',
            'stock-balances', 'stock-movements', 'stock-transfers',
            'stock-transfer-items', 'stock-adjustments', 'stock-adjustment-items',
            'stock-counts', 'stock-count-items', 'stock-wastes', 'stock-waste-items',
        ];

        foreach ($resources as $resource) {
            $this->get(route($resource.'.index'))->assertOk();
        }
    }

    public function test_user_listing_is_not_exposed_without_authentication(): void
    {
        $this->get('/users')->assertNotFound();
    }

    public function test_sidebar_links_to_each_registered_inventory_workflow_route(): void
    {
        $response = $this->get(route('dashboard'))->assertOk();

        $routes = [
            'items.index',
            'units.index',
            'stock-locations.index',
            'stock-balances.index',
            'stock-movements.index',
            'suppliers.index',
            'supplier-contacts.index',
            'purchase-orders.index',
            'purchase-order-items.index',
            'goods-receipts.index',
            'goods-receipt-items.index',
            'ingredient-price-histories.index',
            'supplier-payments.index',
            'menu-categories.index',
            'menu-items.index',
            'recipe-versions.index',
            'recipe-ingredients.index',
            'recipe-instructions.index',
            'recipe-other-costs.index',
            'stock-transfers.index',
            'stock-transfer-items.index',
            'stock-adjustments.index',
            'stock-adjustment-items.index',
            'stock-counts.index',
            'stock-count-items.index',
            'stock-wastes.index',
            'stock-waste-items.index',
        ];

        foreach ($routes as $routeName) {
            $response->assertSee(route($routeName), false);
        }
    }
}
