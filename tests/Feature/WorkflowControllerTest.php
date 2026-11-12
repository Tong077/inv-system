<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\IngredientCategory;
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

    public function test_search_filter_allows_custom_width_classes(): void
    {
        $this->blade('<x-search-filter search-container-class="col-md-4" search-class="border-success" />')
            ->assertSee('class="col-md-4"', false)
            ->assertSee('class="form-control border-success"', false);
    }

    public function test_ingredient_category_toolbar_keeps_add_search_and_status_controls_together(): void
    {
        $this->get(route('ingredient-categories.index'))
            ->assertOk()
            ->assertSee('class="category-toolbar"', false)
            ->assertSeeInOrder(['Add category', 'category-search-filter'], false)
            ->assertSee('category-search-field', false)
            ->assertSee('category-status-filter', false);
    }

    public function test_ingredient_categories_display_parent_count_and_support_search_and_status_filter(): void
    {
        $parent = IngredientCategory::create([
            'name' => 'Produce',
            'description' => 'Fruit and vegetables',
            'is_active' => true,
        ]);
        $child = IngredientCategory::create([
            'parent_id' => $parent->id,
            'name' => 'Leafy greens',
            'description' => 'Fresh greens',
            'is_active' => true,
        ]);
        $unit = Unit::create([
            'name' => 'Kilogram',
            'abbreviation' => 'kg',
            'dimension' => 'mass',
            'conversion_factor_to_base' => 1,
            'is_base_unit' => true,
            'is_active' => true,
        ]);
        Ingredient::create([
            'code' => 'LETTUCE',
            'name' => 'Lettuce',
            'ingredient_category_id' => $child->id,
            'base_unit_id' => $unit->id,
            'minimum_stock' => 0,
            'is_active' => true,
        ]);
        IngredientCategory::create([
            'name' => 'Archived category',
            'is_active' => false,
        ]);

        $this->get(route('ingredient-categories.index', ['search' => 'Leafy', 'status' => 'active']))
            ->assertOk()
            ->assertSee('Leafy greens')
            ->assertSee('Produce')
            ->assertSee('category-search-filter', false)
            ->assertSee('category-search-field', false)
            ->assertSee('category-status-filter', false)
            ->assertSee('1', false)
            ->assertDontSee('Archived category');
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
            'units', 'menu-categories', 'ingredient-categories', 'suppliers', 'supplier-contacts',
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
            'ingredient-categories.index',
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

            $currentPage = $this->get(route($routeName))->assertOk()->getContent();

            $this->assertMatchesRegularExpression(
                '/<a\s+href="'.preg_quote(route($routeName), '/').'"\s+class="nav-link active text-primary"[\s\S]*?aria-current="page"/',
                $currentPage,
            );
            $this->assertStringContainsString('class="nav-item menu-open"', $currentPage);
            $this->assertStringContainsString('aria-expanded="true"', $currentPage);
        }
    }

    public function test_sidebar_expands_only_the_group_for_the_current_route(): void
    {
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('class="nav-link active text-primary"', false)
            ->assertSee('aria-current="page"', false);

        $this->get(route('ingredient-categories.index'))
            ->assertOk()
            ->assertSee('class="nav-item menu-open"', false)
            ->assertSee('aria-expanded="true"', false)
            ->assertSee('aria-expanded="false"', false)
            ->assertSee('class="nav nav-treeview"', false)
            ->assertSee('aria-current="page"', false);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('class="nav-item menu-open"', false)
            ->assertSee('aria-expanded="false"', false);
    }
}
