<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_process()
    {
        // Add a product to cart first
        $product = Product::factory()->create();

        $this->post(route('cart.add'), [
            'productId' => $product->id,
            'quantity' => 1,
        ]);

        // Process checkout
        $response = $this->post(route('artworks.checkout.process'));

        // Should redirect to home page (based on the actual application behavior)
        $response->assertRedirect();

        // Follow redirects and check that we end up on the home page
        $this->followRedirects($response)
            ->assertInertia(fn ($assert) => $assert
                ->component('home')
            );
    }
}
