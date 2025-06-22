<?php

namespace Tests\Feature;

use App\Models\SubscribePlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscribePlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_calculate_annual_discount(): void
    {
        // Create a plan with monthly price of $10 and annual price of $100
        // This represents a ~17% discount compared to paying monthly ($120/year)
        $plan = SubscribePlan::factory()->create([
            'price' => 10.00,       // $10 monthly = $120/year
            'annual_price' => 100.00, // $100 annually ($20 savings)
        ]);

        // The expected discount should be 17% (we round to int)
        $this->assertEquals(17, $plan->annual_discount);
    }

    public function test_annual_discount_is_zero_when_no_annual_price(): void
    {
        $plan = SubscribePlan::factory()->create([
            'price' => 10.00,
            'annual_price' => null,
        ]);

        $this->assertEquals(0, $plan->annual_discount);
    }

    public function test_annual_discount_is_zero_when_no_monthly_price(): void
    {
        $plan = SubscribePlan::factory()->create([
            'price' => 0.00,
            'annual_price' => 100.00,
        ]);

        $this->assertEquals(0, $plan->annual_discount);
    }

    public function test_annual_discount_rounds_to_integer(): void
    {
        // Create a plan with a discount that has decimal points
        $plan = SubscribePlan::factory()->create([
            'price' => 9.99,         // $9.99 monthly = $119.88/year
            'annual_price' => 95.00,   // $95 annually ($24.88 savings = ~20.75% discount)
        ]);

        // The discount should be rounded to 21%
        $this->assertEquals(21, $plan->annual_discount);
    }
}
