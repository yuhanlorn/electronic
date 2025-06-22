<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @throws ConnectionException
     */
    public function run(): void
    {
        // Create admin user
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@admin.com',
                'password' => Hash::make('admin123'),
            ]
        );

        // Generate permissions and roles with Filament Shield
        $this->call(ShieldSeeder::class);

        // Add our roles and permissions
        $this->call(RolePermissionSeeder::class);


        // After creating the roles, assign the 'admin' role to our admin user
        $adminUser = User::where('email', 'admin@admin.com')->first();
        $adminUser->assignRole('admin');

        // Seed application settings
        $this->call(SettingsSeeder::class);

        $this->call(CategorySeeder::class);
        $this->call(ProductSeeder::class);
    }
}
