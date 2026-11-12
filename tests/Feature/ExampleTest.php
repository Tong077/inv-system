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
            ->assertSee('Ingredient categories')
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
            ['/ingredient-categories', 'Ingredient categories', 'No ingredient categories yet'],
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

    public function test_success_feedback_renders_as_a_styled_toast(): void
    {
        $this->withSession(['success' => 'Ingredient category saved.'])
            ->get('/')
            ->assertOk()
            ->assertSee('class="toast toast-success border-0"', false)
            ->assertSee('bi-check-lg', false)
            ->assertSee('Success')
            ->assertSee('Ingredient category saved.')
            ->assertSee('data-bs-autohide="true"', false);
    }

    public function test_ingredient_and_menu_forms_render_the_image_upload_dropzone(): void
    {
        foreach (['items.index', 'menu-items.index'] as $route) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee('data-image-dropzone', false)
                ->assertSee('data-image-preview-input', false)
                ->assertSee('Drag and drop an image, or');
        }
    }
}
