<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_theme_page_loads_correctly()
    {
        // Create test data
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->create();
        $category->products()->attach($product->id);

        // Test the response
        $response = $this->get("/themes/{$category->slug}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->component('theme/show')
            ->has('category')
            ->has('category.products')
        );
    }
}
