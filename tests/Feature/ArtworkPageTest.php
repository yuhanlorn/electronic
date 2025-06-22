<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtworkPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_artwork_page_loads_correctly()
    {
        // Create test data
        $product = Product::factory()->create();

        // Test the response
        $response = $this->get("/artworks/{$product->slug}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->component('artwork/show')
        );
    }
}
