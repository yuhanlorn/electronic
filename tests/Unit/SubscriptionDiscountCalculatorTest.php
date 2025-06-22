<?php

namespace Tests\Unit;

use App\Enums\SubscribeStatus;
use App\Enums\SubscriptionPeriod;
use App\Http\Controllers\CheckoutController;
use App\Models\Order;
use App\Models\Subscribe;
use App\Models\SubscribePlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SubscriptionDiscountCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private CheckoutController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->controller = new CheckoutController();
    }

    #[Test]
    public function discount_is_calculated_correctly_for_bronze_plan(): void
    {
        // Create a Bronze plan with 5% discount
        $plan = SubscribePlan::create([
            'name' => 'Bronze',
            'price' => 9.99,
            'annual_price' => 99.99,
            'features_list' => ['5% Discount on Original Art and Custom work'],
        ]);

        // Create an order and a subscription
        $order = $this->createOrder();
        $this->createSubscription($plan);

        // Login as the user
        Auth::login($this->user);

        // Apply the subscription benefits
        $this->controller->applySubscriptionBenefits($order);

        // Check discount calculation
        $this->assertEquals(5, $order->subscription_discount_percent);
        
        // For a $100 order, the discount should be $5
        $subtotal = 100.00;
        $discount = $subtotal * ($order->subscription_discount_percent / 100);
        $this->assertEquals(5.00, $discount);
    }

    #[Test]
    public function discount_is_calculated_correctly_for_silver_plan(): void
    {
        // Create a Silver plan with 10% discount
        $plan = SubscribePlan::create([
            'name' => 'Silver',
            'price' => 29.99,
            'annual_price' => 299.99,
            'features_list' => [
                '10% Discount on Original Art and Custom Work',
                'Free Shipping (Up to 19" x 27")',
            ],
        ]);

        // Create an order and a subscription
        $order = $this->createOrder();
        $this->createSubscription($plan);

        // Login as the user
        Auth::login($this->user);

        // Apply the subscription benefits
        $this->controller->applySubscriptionBenefits($order);

        // Check discount calculation
        $this->assertEquals(10, $order->subscription_discount_percent);
        $this->assertTrue($order->has_free_shipping);
        
        // For a $100 order, the discount should be $10
        $subtotal = 100.00;
        $discount = $subtotal * ($order->subscription_discount_percent / 100);
        $this->assertEquals(10.00, $discount);
        
        // Free shipping should be applied
        $shipping = $order->has_free_shipping ? 0 : 4.99;
        $this->assertEquals(0, $shipping);
    }

    #[Test]
    public function discount_is_calculated_correctly_for_gold_plan(): void
    {
        // Create a Gold plan with 15% discount
        $plan = SubscribePlan::create([
            'name' => 'Gold',
            'price' => 49.99,
            'annual_price' => 499.99,
            'features_list' => [
                '15% Discount on Original Art and Custom Work',
                'Free Shipping (Up to 19" x 27")',
            ],
        ]);

        // Create an order and a subscription
        $order = $this->createOrder();
        $this->createSubscription($plan);

        // Login as the user
        Auth::login($this->user);

        // Apply the subscription benefits
        $this->controller->applySubscriptionBenefits($order);

        // Check discount calculation
        $this->assertEquals(15, $order->subscription_discount_percent);
        $this->assertTrue($order->has_free_shipping);
        
        // For a $100 order, the discount should be $15
        $subtotal = 100.00;
        $discount = $subtotal * ($order->subscription_discount_percent / 100);
        $this->assertEquals(15.00, $discount);
    }

    #[Test]
    public function expired_subscription_provides_no_benefits(): void
    {
        // Create a Gold plan with 15% discount
        $plan = SubscribePlan::create([
            'name' => 'Gold',
            'price' => 49.99,
            'annual_price' => 499.99,
            'features_list' => [
                '15% Discount on Original Art and Custom Work',
                'Free Shipping (Up to 19" x 27")',
            ],
        ]);

        // Create an order
        $order = $this->createOrder();
        
        // Create an expired subscription (ended in the past)
        Subscribe::create([
            'user_id' => $this->user->id,
            'plan_id' => $plan->id,
            'period' => SubscriptionPeriod::MONTHLY,
            'start_at' => Carbon::now()->subMonths(2),
            'end_at' => Carbon::now()->subMonth(),
            'status' => SubscribeStatus::ACTIVE,
        ]);

        // Login as the user
        Auth::login($this->user);

        // Apply the subscription benefits
        $this->controller->applySubscriptionBenefits($order);

        // Check that no benefits are applied since the subscription is expired
        $this->assertEquals(0, $order->subscription_discount_percent);
        $this->assertNotTrue($order->has_free_shipping);
    }

    #[Test]
    public function inactive_subscription_provides_no_benefits(): void
    {
        // Create a Gold plan with 15% discount
        $plan = SubscribePlan::create([
            'name' => 'Gold',
            'price' => 49.99,
            'annual_price' => 499.99,
            'features_list' => [
                '15% Discount on Original Art and Custom Work',
                'Free Shipping (Up to 19" x 27")',
            ],
        ]);

        // Create an order
        $order = $this->createOrder();
        
        // Create an inactive subscription
        Subscribe::create([
            'user_id' => $this->user->id,
            'plan_id' => $plan->id,
            'period' => SubscriptionPeriod::MONTHLY,
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addMonth(),
            'status' => SubscribeStatus::PAUSED,
        ]);

        // Login as the user
        Auth::login($this->user);

        // Apply the subscription benefits
        $this->controller->applySubscriptionBenefits($order);

        // Check that no benefits are applied since the subscription is inactive
        $this->assertEquals(0, $order->subscription_discount_percent);
        $this->assertNotTrue($order->has_free_shipping);
    }

    #[Test]
    public function plan_with_no_features_provides_no_benefits(): void
    {
        // Create a plan with no specified features
        $plan = SubscribePlan::create([
            'name' => 'Basic',
            'price' => 4.99,
            'annual_price' => 49.99,
            'features_list' => [], // Empty features list
        ]);

        // Create an order and a subscription
        $order = $this->createOrder();
        $this->createSubscription($plan);

        // Login as the user
        Auth::login($this->user);

        // Apply the subscription benefits
        $this->controller->applySubscriptionBenefits($order);

        // Check that no discount or free shipping is applied
        $this->assertEquals(0, $order->subscription_discount_percent);
        $this->assertNotTrue($order->has_free_shipping);
    }

    private function createOrder(): Order
    {
        return Order::create([
            'uuid' => 'test-' . uniqid(),
            'user_id' => $this->user->id,
            'status' => Order::DRAFT_STATUS,
            'total' => 100.00,
        ]);
    }

    private function createSubscription(SubscribePlan $plan): Subscribe
    {
        return Subscribe::create([
            'user_id' => $this->user->id,
            'plan_id' => $plan->id,
            'period' => SubscriptionPeriod::MONTHLY,
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addMonth(),
            'status' => SubscribeStatus::ACTIVE,
        ]);
    }
} 