<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            'Apple',
            'Samsung',
            'Sony',
            'LG',
            'Dell',
            'HP',
            'Asus',
            'Acer',
            'Lenovo',
            'Microsoft',
            'Xiaomi',
            'OnePlus',
            'Oppo',
            'Realme',
            'Google',
            'Razer',
            'Amazon',
            'Huawei',
            'Nokia',
            'Canon',
        ];

        foreach ($brands as $brandName) {
            Brand::firstOrCreate(
                ['name' => $brandName],
                [
                    'slug' => Str::slug($brandName),
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Brands seeded successfully!');
    }
} 