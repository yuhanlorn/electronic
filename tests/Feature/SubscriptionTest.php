<?php

namespace Tests\Feature;

use App\Enums\SubscribeStatus;
use App\Enums\SubscriptionPeriod;
use App\Http\Controllers\CheckoutController;
use App\Jobs\RenewSubscription;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Subscribe;
use App\Models\SubscribePlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected SubscribePlan $bronzePlan;
    protected SubscribePlan $silverPlan;
    protected SubscribePlan $goldPlan;
    protected SubscribePlan $currentPlan;
    protected SubscribePlan $newPlan;
    protected Subscribe $currentSubscription;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user
        $this->user = User::factory()->create();

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

        // Set up plans for switching tests
        $this->currentPlan = $this->bronzePlan;
        $this->newPlan = $this->silverPlan;

        // Create an active subscription for the current plan
        $this->currentSubscription = Subscribe::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->currentPlan->id,
            'period' => SubscriptionPeriod::MONTHLY,
            'status' => SubscribeStatus::ACTIVE,
            'start_at' => Carbon::now()->subMonth(),
            'end_at' => Carbon::now()->addMonth(),
            'digital_downloads_remaining' => 3,
            'rolled_canvas_remaining' => $this->currentPlan->name !== 'Bronze' ? 1 : 0,
        ]);
    }

    //
    // SECTION 1: BASIC SUBSCRIPTION FUNCTIONALITY
    //

    public function test_subscription_plans_page_loads(): void
    {
        // Create some test plans
        $plans = SubscribePlan::factory()->count(3)->create(['is_active' => true]);

        // Test the response
        $response = $this->get(route('subscribe.plan'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->component('subscription/plans')
            ->has('plans', count($plans) + 3) // +3 for the plans we created in setUp
        );
    }

    public function test_user_can_subscribe_to_plan(): void
    {
        // Delete existing subscription to avoid conflicts
        $this->currentSubscription->delete();

        // Attempt to subscribe as authenticated user
        $response = $this->actingAs($this->user)
            ->post(route('subscribe.process', ['plan' => $this->bronzePlan]), [
                'period' => SubscriptionPeriod::MONTHLY->value,
            ]);

        // Check that a subscription was created
        $this->assertDatabaseHas('subscribes', [
            'user_id' => $this->user->id,
            'plan_id' => $this->bronzePlan->id,
            'status' => SubscribeStatus::ACTIVE->value,
            'period' => SubscriptionPeriod::MONTHLY->value,
        ]);
    }

    public function test_unauthenticated_user_cannot_subscribe(): void
    {
        // Attempt to subscribe as unauthenticated user
        $response = $this->post(route('subscribe.process', ['plan' => $this->bronzePlan]), [
            'period' => SubscriptionPeriod::MONTHLY->value,
        ]);

        // Should redirect to login
        $response->assertRedirect(route('login'));
    }

    public function test_user_cannot_subscribe_to_invalid_plan(): void
    {
        // Attempt to subscribe to a non-existent plan
        $response = $this->actingAs($this->user)
            ->post('/subscribe/9999', [
                'period' => SubscriptionPeriod::MONTHLY->value,
            ]);

        // Should return 404 for non-existent plan
        $response->assertStatus(404);
    }

    public function test_user_can_subscribe_with_annual_period(): void
    {
        // Delete existing subscription to avoid conflicts
        $this->currentSubscription->delete();

        // Attempt to subscribe as authenticated user with annual period
        $response = $this->actingAs($this->user)
            ->post(route('subscribe.process', ['plan' => $this->bronzePlan]), [
                'period' => SubscriptionPeriod::ANNUALLY->value,
            ]);

        // Check that a subscription was created with annual period
        $this->assertDatabaseHas('subscribes', [
            'user_id' => $this->user->id,
            'plan_id' => $this->bronzePlan->id,
            'status' => SubscribeStatus::ACTIVE->value,
            'period' => SubscriptionPeriod::ANNUALLY->value,
        ]);
    }

    public function test_user_can_pause_subscription(): void
    {
        // Pause the subscription
        $response = $this->actingAs($this->user)
            ->post(route('subscription.cancel', ['plan' => $this->currentPlan]));

        // Check that subscription was paused
        $this->assertDatabaseHas('subscribes', [
            'user_id' => $this->user->id,
            'plan_id' => $this->currentPlan->id,
            'status' => SubscribeStatus::PAUSED->value,
        ]);
    }

    //
    // SECTION 2: SUBSCRIPTION BENEFITS
    //

    public function test_bronze_plan_subscriber_gets_5_percent_discount(): void
    {
        // Create an order
        $order = $this->createOrder();

        // Apply subscription benefits
        $this->applySubscriptionBenefits($order);

        // Assert the correct discount is applied
        $this->assertEquals(5, $order->subscription_discount_percent);
        $this->assertFalse($order->has_free_shipping);
    }

    public function test_silver_plan_subscriber_gets_10_percent_discount_and_free_shipping(): void
    {
        // Cancel current subscription
        $this->currentSubscription->delete();

        // Create a silver subscription
        $subscription = $this->createSubscription($this->silverPlan);

        // Create an order
        $order = $this->createOrder();

        // Apply subscription benefits
        $this->applySubscriptionBenefits($order);

        // Assert the correct benefits are applied
        $this->assertEquals(10, $order->subscription_discount_percent);
        $this->assertTrue($order->has_free_shipping);
    }

    public function test_gold_plan_subscriber_gets_15_percent_discount_and_free_shipping(): void
    {
        // Cancel current subscription
        $this->currentSubscription->delete();

        // Create a gold subscription
        $subscription = $this->createSubscription($this->goldPlan);

        // Create an order
        $order = $this->createOrder();

        // Apply subscription benefits
        $this->applySubscriptionBenefits($order);

        // Assert the correct benefits are applied
        $this->assertEquals(15, $order->subscription_discount_percent);
        $this->assertTrue($order->has_free_shipping);
    }

    public function test_no_benefits_applied_without_subscription(): void
    {
        // Delete the current subscription
        $this->currentSubscription->delete();

        // Create an order without any subscription
        $order = $this->createOrder();

        // Apply subscription benefits (without an active subscription)
        Auth::login($this->user);
        $controller = new CheckoutController();
        $controller->applySubscriptionBenefits($order);

        // Assert no benefits are applied
        $this->assertEquals(0, $order->subscription_discount_percent);
        $this->assertNotTrue($order->has_free_shipping);
    }

    public function test_subscription_benefits_impact_order_total_calculation(): void
    {
        // Cancel current subscription
        $this->currentSubscription->delete();

        // Test with gold plan for maximum benefits
        $subscription = $this->createSubscription($this->goldPlan);

        // Create an order with known total
        $order = $this->createOrder();
        $order->total = 100.00; // Set base total to make calculations predictable
        $order->save();

        // Calculate expected values
        $expectedDiscount = 15.00; // 15% of 100
        $expectedShipping = 0.00; // Free shipping

        // Apply subscription benefits
        $this->applySubscriptionBenefits($order);

        // Simulate the frontend calculation (which we need to ensure works correctly)
        $subtotal = 100.00;
        $subscriptionDiscount = $order->subscription_discount_percent ? ($subtotal * $order->subscription_discount_percent / 100) : 0;
        $shipping = $order->has_free_shipping ? 0 : 4.99;
        $tax = $subtotal * 0.07;
        $total = $subtotal - $subscriptionDiscount + $shipping + $tax;

        // Assert the frontend calculation matches expectations
        $this->assertEquals($expectedDiscount, $subscriptionDiscount);
        $this->assertEquals($expectedShipping, $shipping);
        $this->assertEquals(100.00 - 15.00 + 0.00 + 7.00, $total); // 92.00
    }

    //
    // SECTION 3: SUBSCRIPTION RENEWAL
    //

    public function test_user_can_renew_active_subscription(): void
    {
        // Store the original end date
        $originalEndDate = $this->currentSubscription->end_at;

        // Send renewal request
        $response = $this->actingAs($this->user)
            ->post(route('subscription.renew', ['plan' => $this->currentPlan->id]));

        // Assert response
        $response->assertRedirect(route('account.subscription'));
        $response->assertSessionHas('success');

        // Reload the subscription from the database
        $this->currentSubscription->refresh();

        // Assert the subscription is still active
        $this->assertEquals(SubscribeStatus::ACTIVE, $this->currentSubscription->status);

        // Assert the end date has been extended by one month (since it's a monthly subscription)
        $expectedNewEndDate = Carbon::parse($originalEndDate)->addMonth()->startOfDay();
        $this->assertEquals(
            $expectedNewEndDate->format('Y-m-d'),
            Carbon::parse($this->currentSubscription->end_at)->startOfDay()->format('Y-m-d')
        );
    }

    public function test_user_can_reactivate_cancelled_subscription(): void
    {
        // Set the subscription to cancelled
        $this->currentSubscription->update([
            'status' => SubscribeStatus::CANCELLED,
        ]);

        // Send renewal request
        $response = $this->actingAs($this->user)
            ->post(route('subscription.renew', ['plan' => $this->currentPlan->id]));

        // Assert response
        $response->assertRedirect(route('account.subscription'));
        $response->assertSessionHas('success');

        // Reload the subscription from the database
        $this->currentSubscription->refresh();

        // Assert the subscription is now active
        $this->assertEquals(SubscribeStatus::ACTIVE, $this->currentSubscription->status);
    }

    public function test_renewal_job_extends_subscription(): void
    {
        // Queue should be fake to track dispatched jobs
        Queue::fake();

        // Create a renewal job
        $job = new RenewSubscription($this->currentSubscription);

        // Store original end date
        $originalEndDate = $this->currentSubscription->end_at;

        // Process the job
        $job->handle();

        // Reload the subscription from the database
        $this->currentSubscription->refresh();

        // Assert the end date has been extended
        $expectedNewEndDate = Carbon::parse($originalEndDate)->addMonths($this->currentSubscription->period->months())->startOfDay();
        $this->assertEquals(
            $expectedNewEndDate->format('Y-m-d'),
            Carbon::parse($this->currentSubscription->end_at)->startOfDay()->format('Y-m-d')
        );
    }

    public function test_cancelled_subscription_is_not_renewed_by_job(): void
    {
        // Set the subscription to cancelled
        $this->currentSubscription->update([
            'status' => SubscribeStatus::CANCELLED,
        ]);

        // Store original end date
        $originalEndDate = $this->currentSubscription->end_at;

        // Create a renewal job
        $job = new RenewSubscription($this->currentSubscription);

        // Process the job
        $job->handle();

        // Reload the subscription from the database
        $this->currentSubscription->refresh();

        // Assert the end date has NOT been extended
        $this->assertEquals(
            Carbon::parse($originalEndDate)->startOfDay()->format('Y-m-d'),
            Carbon::parse($this->currentSubscription->end_at)->startOfDay()->format('Y-m-d')
        );
    }

    public function test_expired_subscription_is_not_renewed_by_job(): void
    {
        // Set the subscription to expired
        $this->currentSubscription->update([
            'end_at' => Carbon::now()->subDay(),
        ]);

        // Store original end date
        $originalEndDate = $this->currentSubscription->end_at;

        // Create a renewal job
        $job = new RenewSubscription($this->currentSubscription);

        // Process the job
        $job->handle();

        // Reload the subscription from the database
        $this->currentSubscription->refresh();

        // Assert the end date has NOT been extended
        $this->assertEquals(
            Carbon::parse($originalEndDate)->startOfDay()->format('Y-m-d'),
            Carbon::parse($this->currentSubscription->end_at)->startOfDay()->format('Y-m-d')
        );
    }

    public function test_command_marks_expired_subscriptions_as_inactive(): void
    {
        // Set the subscription to expired but still active
        $this->currentSubscription->update([
            'end_at' => Carbon::now()->subDays(2),
            'status' => SubscribeStatus::ACTIVE,
        ]);

        // Run the command
        $this->artisan('subscriptions:check')
            ->assertSuccessful();

        // Reload the subscription from the database
        $this->currentSubscription->refresh();

        // Assert the subscription is now inactive
        $this->assertEquals(SubscribeStatus::INACTIVE, $this->currentSubscription->status);
    }

    public function test_command_marks_expired_cancelled_subscriptions_as_inactive(): void
    {
        // Set the subscription to expired and cancelled
        $this->currentSubscription->update([
            'end_at' => Carbon::now()->subDays(2),
            'status' => SubscribeStatus::CANCELLED,
        ]);

        // Run the command
        $this->artisan('subscriptions:check')
            ->assertSuccessful();

        // Reload the subscription from the database
        $this->currentSubscription->refresh();

        // Assert the subscription is now inactive
        $this->assertEquals(SubscribeStatus::INACTIVE, $this->currentSubscription->status);
    }

    public function test_annual_subscription_renewal_job_extends_for_one_year(): void
    {
        // Update subscription to annual period
        $this->currentSubscription->update([
            'period' => SubscriptionPeriod::ANNUALLY,
        ]);

        // Store original end date
        $originalEndDate = $this->currentSubscription->end_at;

        // Create a renewal job
        $job = new RenewSubscription($this->currentSubscription);

        // Process the job
        $job->handle();

        // Reload the subscription from the database
        $this->currentSubscription->refresh();

        // Assert the end date has been extended by 12 months
        $expectedNewEndDate = Carbon::parse($originalEndDate)->addMonths(12)->startOfDay();
        $this->assertEquals(
            $expectedNewEndDate->format('Y-m-d'),
            Carbon::parse($this->currentSubscription->end_at)->startOfDay()->format('Y-m-d')
        );
    }

    //
    // SECTION 4: SUBSCRIPTION SWITCHING
    //

    public function test_regular_plan_switch_creates_scheduled_subscription(): void
    {
        // Login as the user
        $this->actingAs($this->user);

        // Switch to the new plan
        $response = $this->post(route('subscribe.process', ['plan' => $this->newPlan]), [
            'period' => SubscriptionPeriod::MONTHLY->value,
        ]);

        // Assert redirection
        $response->assertRedirect(route('account.subscription'));

        // Reload the current subscription
        $this->currentSubscription->refresh();

        // Assert the current subscription is cancelled
        $this->assertEquals(SubscribeStatus::CANCELLED, $this->currentSubscription->status);

        // Find the scheduled subscription
        $scheduledSubscription = $this->user->subscriptions()
            ->where('plan_id', $this->newPlan->id)
            ->where('status', SubscribeStatus::SCHEDULED)
            ->first();

        // Assert the scheduled subscription exists
        $this->assertNotNull($scheduledSubscription);

        // Assert the scheduled subscription has the correct start date (matching current subscription end date)
        $this->assertEquals(
            Carbon::parse($this->currentSubscription->end_at)->format('Y-m-d'),
            Carbon::parse($scheduledSubscription->start_at)->format('Y-m-d')
        );
    }

    public function test_switching_with_existing_scheduled_subscription(): void
    {
        // First, create a scheduled subscription
        $firstScheduledSubscription = Subscribe::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->silverPlan->id,
            'period' => SubscriptionPeriod::MONTHLY,
            'status' => SubscribeStatus::SCHEDULED,
            'start_at' => Carbon::parse($this->currentSubscription->end_at),
            'end_at' => Carbon::parse($this->currentSubscription->end_at)->addMonth(),
        ]);

        // Login as the user
        $this->actingAs($this->user);

        // Try to switch to the gold plan now
        $response = $this->post(route('subscribe.process', ['plan' => $this->goldPlan]), [
            'period' => SubscriptionPeriod::MONTHLY->value,
        ]);

        // Assert redirection
        $response->assertRedirect(route('account.subscription'));

        // The scheduled subscription should be updated to the new plan (not cancelled)
        $firstScheduledSubscription->refresh();
        $this->assertEquals(SubscribeStatus::SCHEDULED, $firstScheduledSubscription->status);
        $this->assertEquals($this->goldPlan->id, $firstScheduledSubscription->plan_id);

        // There should only be one scheduled subscription
        $scheduledCount = $this->user->subscriptions()
            ->where('status', SubscribeStatus::SCHEDULED)
            ->count();
        $this->assertEquals(1, $scheduledCount);
    }

    public function test_subscription_lifecycle_with_switching(): void
    {
        // Set up dates
        $now = Carbon::now();

        // Create a current subscription that's about to expire
        $expiringSubscription = Subscribe::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->currentPlan->id,
            'period' => SubscriptionPeriod::MONTHLY,
            'status' => SubscribeStatus::CANCELLED, // Already cancelled because user has switched
            'start_at' => $now->copy()->subMonth(),
            'end_at' => $now->copy()->addDay(), // Expires tomorrow
            'digital_downloads_remaining' => 3,
            'rolled_canvas_remaining' => 0,
        ]);

        // Create a scheduled subscription set to start when the current one expires
        $scheduledSubscription = Subscribe::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->newPlan->id,
            'period' => SubscriptionPeriod::MONTHLY,
            'status' => SubscribeStatus::SCHEDULED,
            'start_at' => $now->copy()->addDay(), // Starts tomorrow
            'end_at' => $now->copy()->addDays(31), // Ends in one month + 1 day
            'digital_downloads_remaining' => 3,
            'rolled_canvas_remaining' => 1,
        ]);

        // Travel forward in time by 2 days
        $this->travel(2)->days();

        // Run the subscription check command
        $this->artisan('subscriptions:check')
            ->assertSuccessful();

        // Reload the subscriptions
        $expiringSubscription->refresh();
        $scheduledSubscription->refresh();

        // Assert the expired subscription is now inactive
        $this->assertEquals(SubscribeStatus::INACTIVE, $expiringSubscription->status);

        // Assert the scheduled subscription is now active
        $this->assertEquals(SubscribeStatus::ACTIVE, $scheduledSubscription->status);
    }

    public function test_command_activates_scheduled_subscriptions(): void
    {
        // Create a scheduled subscription
        $scheduledSubscription = Subscribe::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->newPlan->id,
            'period' => SubscriptionPeriod::MONTHLY,
            'status' => SubscribeStatus::SCHEDULED,
            'start_at' => Carbon::now()->subDay(), // Start date is in the past
            'end_at' => Carbon::now()->addMonths(2),
            'digital_downloads_remaining' => 3,
            'rolled_canvas_remaining' => 1,
        ]);

        // Run the check subscriptions command
        $this->artisan('subscriptions:check')
            ->assertSuccessful();

        // Reload the subscription from the database
        $scheduledSubscription->refresh();

        // Assert the scheduled subscription is now active
        $this->assertEquals(SubscribeStatus::ACTIVE, $scheduledSubscription->status);
    }

    //
    // Helper methods
    //

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

    private function createOrder(): Order
    {
        return Order::create([
            'uuid' => 'test-' . uniqid(),
            'user_id' => $this->user->id,
            'status' => Order::DRAFT_STATUS,
            'total' => 0,
        ]);
    }

    private function applySubscriptionBenefits(Order $order): void
    {
        Auth::login($this->user);
        $controller = new CheckoutController();
        $controller->applySubscriptionBenefits($order);
    }
}
