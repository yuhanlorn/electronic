<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Smartphones',
            'Laptops',
            'Tablets',
            'Smartwatches',
            'Earphones',
            'Gaming',
            'Cameras',
            'Monitors',
            'Printers',
            'Networking',
        ];

        foreach ($categories as $categoryName) {
            Category::firstOrCreate(
                ['name' => $categoryName],
                [
                    'type' => 'category',
                    'slug' => Str::slug($categoryName),
                    'description' => "High-quality {$categoryName} products",
                    'is_active' => true,
                    'show_in_menu' => true,
                ]
            );
        }

        $this->command->info('Categories seeded successfully!');
    }
}
