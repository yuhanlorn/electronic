<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlashMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_flash_messages_are_shared_with_inertia()
    {
        // Create a test product
        $product = Product::factory()->create();

        // Simulate adding an item to the cart and check for flash message
        $response = $this->post(route('cart.add'), [
            'productId' => $product->id,
            'quantity' => 1,
        ]);

        // The redirect should include the flash message in the props
        $response = $this->get($response->headers->get('Location'));

        $response->assertInertia(fn ($assert) => $assert
            ->has('flash.success')
        );
    }

    public function test_flash_error_messages_work()
    {
        // Try to add a non-existent product to cart
        $response = $this->post(route('cart.add'), [
            'productId' => 99999, // Non-existent ID
            'quantity' => 1,
        ]);

        // The redirect should include the error flash message
        $response = $this->get($response->headers->get('Location'));

        $response->assertInertia(fn ($assert) => $assert
            ->has('flash.error')
        );
    }

    public function test_multiple_flash_messages_can_be_set()
    {
        // Set multiple flash messages manually
        session()->flash('success', 'Success message');
        session()->flash('info', 'Information message');

        // Visit any page
        $response = $this->get(route('home'));

        // Both messages should be available
        $response->assertInertia(fn ($assert) => $assert
            ->has('flash.success')
            ->has('flash.info')
        );
    }
}
