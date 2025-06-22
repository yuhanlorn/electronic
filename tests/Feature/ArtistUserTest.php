<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ArtistUserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that an artist can view products.
     */
    public function test_artist_can_view_products(): void
    {
        // Run the seeders to set up permissions
        $this->seed(\Database\Seeders\ShieldSeeder::class);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        // Create an artist user
        $artist = User::factory()->create([
            'name' => 'Test Artist',
            'email' => 'testartist@example.com',
        ]);

        // Get the artist role and assign it to the user
        $artistRole = Role::where('name', 'artist')->first();
        $artist->assignRole($artistRole);

        // Create a sample product for testing
        $product = Product::factory()->create([
            'user_id' => $artist->id,
            'name' => 'Test Artwork',
        ]);

        // Log in as the artist
        $response = $this->actingAs($artist)
            ->get('/admin/products');

        // Assert that the artist can access the products page
        $response->assertStatus(200);
    }

    /**
     * Test that an artist cannot access restricted areas.
     */
    public function test_artist_cannot_access_restricted_areas(): void
    {
        // Run the seeders to set up permissions
        $this->seed(\Database\Seeders\ShieldSeeder::class);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        // Create an artist user
        $artist = User::factory()->create([
            'name' => 'Test Artist',
            'email' => 'testartist@example.com',
        ]);

        // Get the artist role and assign it to the user
        $artistRole = Role::where('name', 'artist')->first();
        $artist->assignRole($artistRole);

        // Log in as the artist and try to access restricted areas
        $response = $this->actingAs($artist)
            ->get('/admin/users');

        // Assert that the artist cannot access restricted areas (should be redirected or forbidden)
        $response->assertStatus(403);
    }

    /**
     * Test that the artist role has the correct permissions.
     */
    public function test_artist_role_has_correct_permissions(): void
    {
        // Run the seeders to set up permissions
        $this->seed(\Database\Seeders\ShieldSeeder::class);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->seed(\Database\Seeders\ArtistSeeder::class);

        // Get the artist role
        $artistRole = Role::where('name', 'artist')->first();

        // Check that the artist role has the correct permissions
        $this->assertTrue($artistRole->hasPermissionTo('view_product'));
        $this->assertTrue($artistRole->hasPermissionTo('view_any_product'));
        $this->assertTrue($artistRole->hasPermissionTo('view_category'));
        $this->assertTrue($artistRole->hasPermissionTo('view_any_category'));
        $this->assertTrue($artistRole->hasPermissionTo('view_order'));
        $this->assertTrue($artistRole->hasPermissionTo('view_any_order'));

        // Check that the artist role doesn't have admin permissions
        $this->assertFalse($artistRole->hasPermissionTo('view_user'));
        $this->assertFalse($artistRole->hasPermissionTo('create_user'));
    }
}
