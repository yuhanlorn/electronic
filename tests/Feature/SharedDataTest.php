<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Module\Cart\CartModule;
use Tests\TestCase;

class SharedDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_data_is_shared_with_all_pages()
    {
        // Set a device ID for the session
        $deviceId = Str::uuid()->toString();
        session(['device_id' => $deviceId]);

        // Create a product
        $product = Product::factory()->create();

        // Add to cart via the cart module
        app(CartModule::class)->store($product->id, 1);

        // Test that the cart data is shared with the home page
        $response = $this->get('/');

        $response->assertInertia(fn ($assert) => $assert
            ->component('home')
            ->has('carts')
        );

        // Test that the cart data is shared with the product page
        $response = $this->get("/artworks/{$product->slug}");

        $response->assertInertia(fn ($assert) => $assert
            ->component('artwork/show')
            ->has('carts')
        );
    }
}
