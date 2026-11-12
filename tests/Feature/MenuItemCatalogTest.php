<?php

namespace Tests\Feature;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class MenuItemCatalogTest extends TestCase
{
    public function test_menu_items_render_as_reusable_catalog_cards(): void
    {
        $category = new MenuCategory(['name' => 'Lunch']);
        $category->setAttribute('id', 3);

        $menuItem = new MenuItem([
            'code' => 'LUNCH-01',
            'name' => 'Garden bowl',
            'barcode' => '123456789',
            'selling_price' => '12.50',
            'currency' => 'USD',
            'is_active' => true,
        ]);
        $menuItem->setAttribute('id', 7);
        $menuItem->setAttribute('recipe_versions_count', 2);
        $menuItem->setRelation('category', $category);

        $this->withViewErrors([])->view('menu-items.partials.results', [
            'menuItems' => new LengthAwarePaginator([$menuItem], 1, 15),
            'categories' => new Collection(),
            'search' => '',
            'status' => null,
        ])
            ->assertSee('catalog-card')
            ->assertSee('Garden bowl')
            ->assertSee('LUNCH-01')
            ->assertSee('Lunch')
            ->assertSee('Active')
            ->assertSee('USD 12.50')
            ->assertSee('Recipe versions')
            ->assertSee('View details');
    }
}
