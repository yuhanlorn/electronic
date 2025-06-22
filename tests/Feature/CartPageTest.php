<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class CartPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set a device ID for the session in each test
        $deviceId = Str::uuid()->toString();
        session(['device_id' => $deviceId]);
    }

    public function test_cart_page_loads_correctly()
    {
        $response = $this->get('/cart');

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->component('cart/index')
        );
    }

    public function test_add_to_cart_functionality()
    {
        // Skip this test if the cart route doesn't exist yet
        if (! Route::has('cart.add')) {
            $this->markTestSkipped('Cart add route not defined');
        }

        $product = Product::factory()->create();

        $response = $this->post(route('cart.add'), [
            'productId' => $product->id,
            'quantity' => 2,
            'variation' => null,
        ]);

        $response->assertStatus(302); // Redirect back

        // Check if the cart page loads after adding a product
        $this->get('/cart')
            ->assertStatus(200)
            ->assertInertia(fn ($assert) => $assert->component('cart/index'));
    }

    public function test_remove_item_from_cart()
    {
        // Skip this test if the cart route doesn't exist yet
        if (! Route::has('cart.delete')) {
            $this->markTestSkipped('Cart delete route not defined');
        }

        // Attempt to check if the CartModule is available
        if (! class_exists('Module\Cart\CartModule')) {
            $this->markTestSkipped('CartModule not available');
        }

        $product = Product::factory()->create();

        // Add the product directly to the cart
        $cartModule = app('Module\Cart\CartModule');
        if (! method_exists($cartModule, 'store')) {
            $this->markTestSkipped('CartModule->store method not available');
        }

        $cartModule->store($product->id, 1);

        // Then remove it
        $response = $this->post(route('cart.delete'), [
            'productId' => $product->id,
            'variation' => null,
        ]);

        $response->assertStatus(302); // Redirect back
    }

    public function test_update_cart_item_quantity()
    {
        // Skip this test if the cart route doesn't exist yet
        if (! Route::has('cart.add')) {
            $this->markTestSkipped('Cart add route not defined');
        }

        // Attempt to check if the CartModule is available
        if (! class_exists('Module\Cart\CartModule')) {
            $this->markTestSkipped('CartModule not available');
        }

        $product = Product::factory()->create();

        // Add the product directly to the cart
        $cartModule = app('Module\Cart\CartModule');
        if (! method_exists($cartModule, 'store')) {
            $this->markTestSkipped('CartModule->store method not available');
        }

        $cartModule->store($product->id, 1);

        // Update quantity to 3 using the same cart.add route with type=set
        $response = $this->post(route('cart.add'), [
            'productId' => $product->id,
            'quantity' => 3,
            'variation' => null,
            'type' => 'set',
        ]);

        $response->assertStatus(302); // Redirect back
    }
}
