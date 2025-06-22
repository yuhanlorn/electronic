<?php

namespace Tests\Feature;

use App\Enums\SubscribeStatus;
use App\Enums\SubscriptionPeriod;
use App\Events\SubscriptionCreated;
use App\Jobs\RenewSubscription;
use App\Models\Subscribe;
use App\Models\SubscribePlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AutomaticRenewalTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected SubscribePlan $plan;

    protected Subscribe $subscription;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create();

        // Create a subscription plan
        $this->plan = SubscribePlan::factory()->create([
            'name' => 'Auto Renewal Plan',
            'price' => 25.00,
            'annual_price' => 250.00,
            'is_active' => true,
        ]);

        // Create an active subscription for the user
        $this->subscription = Subscribe::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'period' => SubscriptionPeriod::MONTHLY,
            'status' => SubscribeStatus::ACTIVE,
            'end_at' => Carbon::now()->addMonth(),
        ]);
    }

    public function test_subscription_created_event_dispatches_renewal_job(): void
    {
        // Fake the queue to catch dispatched jobs
        Queue::fake();

        // Manually register the event listener since we're in an isolated test
        $listener = new \App\Listeners\ScheduleSubscriptionRenewal;

        // Create a near-expiring subscription to ensure the listener will schedule a job
        $this->subscription->update([
            'end_at' => Carbon::now()->addDays(7),
            'status' => SubscribeStatus::ACTIVE,
        ]);

        // Trigger the event and let the listener handle it directly
        $event = new SubscriptionCreated($this->subscription);
        $listener->handle($event);

        // Assert that the renewal job was dispatched
        Queue::assertPushed(RenewSubscription::class, function ($job) {
            return $job->subscription->id === $this->subscription->id;
        });
    }

    public function test_subscription_check_command_schedules_renewals(): void
    {
        // Fake the event dispatcher to catch fired events
        Event::fake();

        // Set the subscription to be due for renewal (3 days before end date)
        $this->subscription->update([
            'end_at' => Carbon::now()->addDays(3),
            'status' => SubscribeStatus::ACTIVE,
        ]);

        // Run the command
        $this->artisan('subscriptions:check')
            ->assertSuccessful();

        // Assert that the SubscriptionCreated event was fired
        Event::assertDispatched(SubscriptionCreated::class, function ($event) {
            return $event->subscription->id === $this->subscription->id;
        });
    }

    public function test_subscription_check_does_not_schedule_renewals_for_far_future_dates(): void
    {
        // Fake the event dispatcher to catch fired events
        Event::fake();

        // Set the subscription to be far in the future
        $this->subscription->update([
            'end_at' => Carbon::now()->addMonths(2),
            'status' => SubscribeStatus::ACTIVE,
        ]);

        // Run the command
        $this->artisan('subscriptions:check')
            ->assertSuccessful();

        // Assert that the SubscriptionCreated event was NOT fired
        Event::assertNotDispatched(SubscriptionCreated::class);
    }

    public function test_subscription_check_does_not_schedule_renewals_for_cancelled_subscriptions(): void
    {
        // Fake the event dispatcher to catch fired events
        Event::fake();

        // Set the subscription to be cancelled but almost due
        $this->subscription->update([
            'end_at' => Carbon::now()->addDays(3),
            'status' => SubscribeStatus::CANCELLED,
        ]);

        // Run the command
        $this->artisan('subscriptions:check')
            ->assertSuccessful();

        // Assert that the SubscriptionCreated event was NOT fired
        Event::assertNotDispatched(SubscriptionCreated::class);
    }

    public function test_full_renewal_chain_workflow(): void
    {
        // This test runs through the entire auto-renewal chain

        // Start with a subscription nearly expired
        $endDate = Carbon::now()->addDays(2);
        $this->subscription->update([
            'end_at' => $endDate,
        ]);

        // Run the subscription check command
        $this->artisan('subscriptions:check');

        // Create and run the renewal job directly (simulating what would happen when the queue processes the job)
        $job = new RenewSubscription($this->subscription);
        $job->handle();

        // Refresh the subscription
        $this->subscription->refresh();

        // Assert the end_at has been extended
        $expectedNewEndDate = $endDate->copy()->addMonth()->startOfDay();
        $actualEndDate = Carbon::parse($this->subscription->end_at)->startOfDay();

        $this->assertEquals(
            $expectedNewEndDate->format('Y-m-d'),
            $actualEndDate->format('Y-m-d')
        );
    }
}
