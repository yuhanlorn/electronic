<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads_correctly()
    {
        // Create test data
        $category = Category::factory()->create(['is_active' => true]);
        $featuredProduct = Product::factory()->create(['is_trend' => true]);
        $discountedProduct = Product::factory()->create([
            'discount' => 20,
            'discount_to' => now()->addDays(5),
        ]);

        // Test the response
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->component('home')
        );
    }
}
