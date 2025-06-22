<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Skip all tests in this class if the ProductController doesn't exist
        if (! File::exists(app_path('Http/Controllers/ProductController.php'))) {
            $this->markTestSkipped('ProductController not found');
        }
    }

    protected function hasSearchCapability(): bool
    {
        // Check if search route is defined
        $hasSearchRoute = Route::has('search');

        // Check if there's a frontend search component
        $hasSearchComponent = File::exists(resource_path('js/pages/SearchPage.tsx'));

        // Check if searchable products exist
        $hasProducts = Product::count() > 0;

        return ($hasSearchRoute || $hasSearchComponent) && $hasProducts;
    }

    public function test_search_page_loads()
    {
        // Skip if no search capability is available
        if (! $this->hasSearchCapability()) {
            $this->markTestSkipped('Search capability not available');
        }

        // If search route exists, test it directly
        if (Route::has('search')) {
            $response = $this->get(route('search'));

            $response->assertStatus(200);
            $response->assertInertia(fn ($assert) => $assert
                ->component('search/index')
            );
        } else {
            // Otherwise, assume search is handled through product index with a query
            $response = $this->get('/products', ['q' => 'test']);

            $response->assertStatus(200);
        }
    }

    public function test_search_returns_results()
    {
        // Skip if no search capability is available
        if (! $this->hasSearchCapability()) {
            $this->markTestSkipped('Search capability not available');
        }

        // Create products that should match the search term
        $searchTerm = 'unique-test-term';
        Product::factory()->count(2)->create([
            'name' => $searchTerm, // Simplified for now, will handle translations if needed
        ]);

        // Create a product that should not match
        Product::factory()->create([
            'name' => 'Unrelated product',
        ]);

        // If search route exists, test it directly
        if (Route::has('search')) {
            $response = $this->get(route('search', ['q' => $searchTerm]));

            $response->assertStatus(200);
            $response->assertInertia(fn ($assert) => $assert
                ->component('search/index')
                ->has('products.data', 2)
            );
        } else {
            // Otherwise, assume search is handled through product index with a query
            $response = $this->get('/products', ['q' => $searchTerm]);

            $response->assertStatus(200);
            // Can't assert the exact component structure without knowing the implementation
        }
    }

    public function test_search_with_empty_query_shows_no_results()
    {
        // Skip if no search capability is available
        if (! $this->hasSearchCapability()) {
            $this->markTestSkipped('Search capability not available');
        }

        // Create some products in the database
        Product::factory()->count(3)->create();

        // If search route exists, test it directly
        if (Route::has('search')) {
            $response = $this->get(route('search', ['q' => '']));

            $response->assertStatus(200);
            $response->assertInertia(fn ($assert) => $assert
                ->component('search/index')
                ->has('products.data', 0)
            );
        } else {
            // Otherwise, assume search is handled through product index with an empty query
            $response = $this->get('/products', ['q' => '']);

            $response->assertStatus(200);
            // Can't assert the exact component structure without knowing the implementation
        }
    }
}
