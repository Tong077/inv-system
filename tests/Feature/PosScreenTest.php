<?php

namespace Tests\Feature;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\StockLocation;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class PosScreenTest extends TestCase
{
    public function test_pos_screen_renders_category_filters_and_a_friendly_order_panel(): void
    {
        $category = new MenuCategory(['name' => 'Lunch']);
        $category->setAttribute('id', 3);

        $menuItem = new MenuItem([
            'code' => 'LUNCH-01',
            'name' => 'Garden bowl',
            'barcode' => '123456789',
            'selling_price' => '12.50',
            'currency' => 'USD',
        ]);
        $menuItem->setAttribute('id', 7);
        $menuItem->setAttribute('available_quantities', [2 => 12]);
        $menuItem->setRelation('category', $category);

        $location = new StockLocation(['name' => 'Main kitchen']);
        $location->setAttribute('id', 2);

        $this->withViewErrors([])->view('pos.index', [
            'menuItems' => new Collection([$menuItem]),
            'locations' => new Collection([$location]),
        ])
            ->assertSee('Choose items')
            ->assertSee('All items')
            ->assertSee('Lunch')
            ->assertSee('Garden bowl')
            ->assertSee('Current order')
            ->assertSee('Your order is empty')
            ->assertSee('Complete sale')
            ->assertSee('data-pos-cart-step', false);
    }
}
