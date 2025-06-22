<?php

namespace Tests\Feature;

use App\Enums\SubscribeStatus;
use App\Enums\SubscriptionPeriod;
use App\Http\Controllers\CheckoutController;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\Subscribe;
use App\Models\SubscribePlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckoutSubscriptionBenefitsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;
    private SubscribePlan $bronzePlan;
    private SubscribePlan $silverPlan;
    private SubscribePlan $goldPlan;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Skip tests if required routes don't exist
        if (!Route::has('artworks.checkout.process') || !Route::has('artworks.checkout')) {
            $this->markTestSkipped('Required routes not defined');
        }
        
        // Set a device ID for the session in each test
        $deviceId = Str::uuid()->toString();
        session(['device_id' => $deviceId]);

        // Create a user
        $this->user = User::factory()->create();

        // Create a product
        $this->product = Product::factory()->create([
            'price' => 100.00, // Use a nice round number for easy calculations
            'name' => 'Test Product' // Add product name
        ]);

        // Create subscription plans
        $this->bronzePlan = SubscribePlan::create([
            'name' => 'Bronze',
            'price' => 9.99,
            'annual_price' => 79.99,
            'currency' => 'USD',
            'is_active' => true,
            'is_popular' => false,
            'description' => 'Perfect for casual art lovers who want to enjoy digital downloads and small discounts on custom work.',
            'features_list' => [
                'Any 3 Digital Art Download',
                '5% Discount on Original Art and Custom work'
            ],
        ]);

        $this->silverPlan = SubscribePlan::create([
            'name' => 'Silver',
            'price' => 29.99,
            'annual_price' => 299.99,
            'currency' => 'USD',
            'is_active' => true,
            'is_popular' => true,
            'description' => 'Ideal for art enthusiasts who want both digital and physical artwork with better discounts.',
            'features_list' => [
                'Any 3 Digital Art Download',
                '1 Rolled Canva print',
                'Upload your photo for Custom Canvas',
                'Free Shipping (Up to 19" x 27")',
                '10% Discount on Original Art and Custom Work',
            ],
        ]);

        $this->goldPlan = SubscribePlan::create([
            'name' => 'Gold',
            'price' => 49.99,
            'annual_price' => 499.99,
            'currency' => 'USD',
            'is_active' => true,
            'is_popular' => false,
            'description' => 'Our premium tier for serious art collectors who want maximum benefits and exclusive discounts.',
            'features_list' => [
                'Any 3 Digital Art Download',
                '1 Rolled Canva print',
                'Upload your photo for Custom Canvas',
                'Free Shipping (Up to 19" x 27")',
                '15% Discount on Original Art and Custom Work',
                '15% Discount on Wedding packages'
            ],
        ]);
    }
    
    #[Test]
    public function subscription_benefits_are_applied_during_checkout_process(): void
    {
        // Create a gold subscription for max benefits
        $subscription = $this->createSubscription($this->goldPlan);
        
        // Create cart item
        $this->createCartItem();
        
        // Login as the user
        $this->actingAs($this->user);
        
        // Process the cart into a draft order
        $response = $this->post(route('artworks.checkout.process'));
        
        // Check that a draft order was created
        $order = Order::where('user_id', $this->user->id)
            ->where('status', Order::DRAFT_STATUS)
            ->latest()
            ->first();
            
        $this->assertNotNull($order);
        
        // Now visit the checkout page which should apply the subscription benefits
        $response = $this->get(route('artworks.checkout', $order->uuid));
        $response->assertStatus(200);
        
        // Refresh the order to get the updated values
        $order->refresh();
        
        // Check that the expected subscription benefits were applied
        $this->assertEquals(15, $order->subscription_discount_percent);
        $this->assertTrue($order->has_free_shipping);
    }
    
    #[Test]
    public function subscription_benefits_are_included_in_checkout_page_response(): void
    {
        // Create a gold subscription for max benefits
        $subscription = $this->createSubscription($this->goldPlan);
        
        // Create cart item
        $this->createCartItem();
        
        // Login as the user
        $this->actingAs($this->user);
        
        // Process the cart into a draft order
        $this->post(route('artworks.checkout.process'));
        
        // Get the latest draft order
        $order = Order::where('user_id', $this->user->id)
            ->where('status', Order::DRAFT_STATUS)
            ->latest()
            ->first();
            
        // Now check that the checkout page includes the active subscription
        $response = $this->get(route('artworks.checkout', $order->uuid));
        
        $response->assertInertia(fn ($assert) => $assert
            ->component('checkout/index')
            ->has('activeSubscription')
            ->where('activeSubscription.plan.name', 'Gold')
        );
    }
    
    #[Test]
    public function subscription_discount_is_calculated_in_order_total(): void
    {
        // Create a silver subscription
        $subscription = $this->createSubscription($this->silverPlan);
        
        // Create cart item with price of 100
        $this->createCartItem();
        
        // Login as the user
        $this->actingAs($this->user);
        
        // Process the cart into a draft order
        $this->post(route('artworks.checkout.process'));
        
        // Get the latest draft order
        $order = Order::where('user_id', $this->user->id)
            ->where('status', Order::DRAFT_STATUS)
            ->latest()
            ->first();
            
        // Visit the checkout page to apply the subscription benefits
        $this->get(route('artworks.checkout', $order->uuid));
        
        // Refresh the order to get the updated values
        $order->refresh();
        
        // Check that the subscription discount is 10%
        $this->assertEquals(10, $order->subscription_discount_percent);
        
        // Check that free shipping is applied
        $this->assertTrue($order->has_free_shipping);
        
        // Now process the order to the next step
        $controller = new CheckoutController();
        
        // Create a mock request with required data
        $request = new \Illuminate\Http\Request();
        $request->merge([
            'address_id' => 1, // Provide a test address ID
            'token' => $order->uuid,
        ]);
        
        // Apply subscription benefits again (which happens during order processing)
        $controller->applySubscriptionBenefits($order);
        
        // Verify that the benefits are still applied
        $this->assertEquals(10, $order->subscription_discount_percent);
        $this->assertTrue($order->has_free_shipping);
    }
    
    #[Test]
    public function non_subscriber_gets_no_benefits(): void
    {
        // No subscription is created for this test
        
        // Create cart item
        $this->createCartItem();
        
        // Login as the user
        $this->actingAs($this->user);
        
        // Process the cart into a draft order
        $this->post(route('artworks.checkout.process'));
        
        // Get the latest draft order
        $order = Order::where('user_id', $this->user->id)
            ->where('status', Order::DRAFT_STATUS)
            ->latest()
            ->first();
            
        // Visit the checkout page which should attempt to apply subscription benefits
        $this->get(route('artworks.checkout', $order->uuid));
        
        // Refresh the order
        $order->refresh();
        
        // Check that no subscription benefits were applied
        $this->assertEquals(0, $order->subscription_discount_percent);
        $this->assertNotTrue($order->has_free_shipping);
        
        // Also check that the activeSubscription is not included in the response
        $response = $this->get(route('artworks.checkout', $order->uuid));
        
        $response->assertInertia(fn ($assert) => $assert
            ->component('checkout/index')
            ->where('activeSubscription', null)
        );
    }
    
    private function createSubscription(SubscribePlan $plan): Subscribe
    {
        $subscription = Subscribe::create([
            'user_id' => $this->user->id,
            'plan_id' => $plan->id,
            'period' => SubscriptionPeriod::MONTHLY,
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addMonth(),
            'status' => SubscribeStatus::ACTIVE,
            'digital_downloads_remaining' => 3,
            'rolled_canvas_remaining' => $plan->name !== 'Bronze' ? 1 : 0,
        ]);
        
        return $subscription;
    }
    
    private function createCartItem(): void
    {
        // Add an item to the user's cart
        Cart::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'price' => $this->product->price,
            'qty' => 1,
            'item' => $this->product->name ?? 'Test Product', // Add required item field
            'session_id' => session('device_id'), // Add session_id for non-authenticated users
        ]);
    }
} 