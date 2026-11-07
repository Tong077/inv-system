<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Stockroom')
            ->assertSee('Inventory overview')
            ->assertSee('Build a reliable inventory foundation.')
            ->assertSee('Primary navigation')
            ->assertSee('Workspace navigation')
            ->assertSee('Stock waste')
            ->assertSee('class="app-wrapper"', false)
            ->assertSee('data-lte-toggle="sidebar"', false)
            ->assertSee('class="bi bi-list"', false)
            ->assertSee('href="#" role="button"', false)
            ->assertSee('class="app-sidebar', false)
            ->assertSee('class="info-box"', false);
    }

    public function test_sidebar_destinations_load_and_mark_the_current_section(): void
    {
        $pages = [
            ['/items', 'Ingredients', 'No items yet'],
            ['/stock-locations', 'Stock locations', 'No stock locations yet'],
            ['/stock-wastes', 'Stock waste', 'No waste records yet'],
        ];

        foreach ($pages as [$url, $label, $emptyState]) {
            $this->get($url)
                ->assertOk()
                ->assertSee($label)
                ->assertSee($emptyState)
                ->assertSee('aria-current="page"', false);
        }
    }
}
