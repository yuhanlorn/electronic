<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'category' => 'Smartphones',
                'brand' => 'Apple',
                'name' => 'iPhone 15',
                'slug' => 'iphone-15-apple',
                'image' => public_path('frontend/images/product_images/1738389144.jpg'),
                'description' => 'Latest Apple smartphone with A16 chip.',
                'price' => 999.99,
                'quantity' => 50,
                'is_active' => true,
                'is_featured' => true,
                'in_stock' => true,
                'on_sale' => false,
            ],
            [
                'category' => 'Smartphones',
                'brand' => 'Samsung',
                'name' => 'Samsung Galaxy S23',
                'slug' => 'samsung-galaxy-s23-ultra',
                'image' => public_path('frontend/images/product_images/1738389501.jpg'),
                'description' => 'Flagship Samsung smartphone with great camera.',
                'price' => 899.99,
                'quantity' => 40,
                'is_active' => true,
                'is_featured' => true,
                'in_stock' => true,
                'on_sale' => false,
            ],
            [
                'category' => 'Laptops',
                'brand' => 'Dell',
                'name' => 'Dell XPS 15',
                'slug' => 'dell-xps-15-2024',
                'image' => public_path('frontend/images/product_images/1738390051.png'),
                'description' => 'Powerful Dell laptop with Intel i7 processor.',
                'price' => 1599.99,
                'quantity' => 30,
                'is_active' => true,
                'is_featured' => false,
                'in_stock' => true,
                'on_sale' => false,
            ],
            [
                'category' => 'Laptops',
                'brand' => 'HP',
                'name' => 'HP Spectre x360',
                'slug' => 'hp-spectre-x360-gen2',
                'image' => public_path('frontend/images/product_images/1738390446.jpg'),
                'description' => 'Improving on the best 2-in-1 laptop of the past few years,',
                'price' => 1399.99,
                'quantity' => 20,
                'is_active' => true,
                'is_featured' => true,
                'in_stock' => true,
                'on_sale' => false,
            ],
            [
                'category' => 'Tablets',
                'brand' => 'Sony',
                'name' => 'Sony Xperia Tablet',
                'slug' => 'sony-xperia-tablet-2024',
                'image' => public_path('frontend/images/product_images/1738390651.jpg'),
                'description' => 'High-resolution display tablet from Sony.',
                'price' => 499.99,
                'quantity' => 25,
                'is_active' => true,
                'is_featured' => false,
                'in_stock' => true,
                'on_sale' => false,
            ],
            [
                'category' => 'Tablets',
                'brand' => 'Asus',
                'name' => 'Asus ROG Tablet',
                'slug' => 'asus-rog-tablet-pro',
                'image' => public_path('frontend/images/product_images/1738390940.jpg'),
                'description' => 'ASUS ROG Flow Z13 (2023) Gaming Laptop Tablet',
                'price' => 799.99,
                'quantity' => 15,
                'is_active' => true,
                'is_featured' => false,
                'in_stock' => true,
                'on_sale' => true,
            ],
            [
                'category' => 'Smartwatches',
                'brand' => 'Microsoft',
                'name' => 'Microsoft Surface Watch',
                'slug' => 'microsoft-surface-watch-2',
                'image' => public_path('frontend/images/product_images/1738391562.jpg'),
                'description' => 'Smartwatch with productivity features.',
                'price' => 299.99,
                'quantity' => 35,
                'is_active' => true,
                'is_featured' => true,
                'in_stock' => true,
                'on_sale' => false,
            ],
            [
                'category' => 'Smartwatches',
                'brand' => 'Xiaomi',
                'name' => 'Xiaomi Mi Watch',
                'slug' => 'xiaomi-mi-watch-lite',
                'image' => public_path('frontend/images/product_images/1738391616.png'),
                'description' => 'Affordable smartwatch with health tracking.',
                'price' => 129.99,
                'quantity' => 50,
                'is_active' => true,
                'is_featured' => false,
                'in_stock' => true,
                'on_sale' => true,
            ],
            [
                'category' => 'Earphones',
                'brand' => 'Realme',
                'name' => 'Realme Buds Wireless',
                'slug' => 'realme-buds-wireless-v2',
                'image' => public_path('frontend/images/product_images/1738393053.jpg'),
                'description' => 'High-quality wireless earbuds.',
                'price' => 79.99,
                'quantity' => 100,
                'is_active' => true,
                'is_featured' => true,
                'in_stock' => true,
                'on_sale' => true,
            ],
            [
                'category' => 'Earphones',
                'brand' => 'OnePlus',
                'name' => 'OnePlus Buds Pro',
                'slug' => 'oneplus-buds-pro-2',
                'image' => public_path('frontend/images/product_images/1738393239.png'),
                'description' => 'Premium earbuds with noise cancellation.',
                'price' => 149.99,
                'quantity' => 70,
                'is_active' => true,
                'is_featured' => false,
                'in_stock' => true,
                'on_sale' => false,
            ],
            [
                'category' => 'Gaming',
                'brand' => 'Google',
                'name' => 'Google Stadia Controller',
                'slug' => 'google-stadia-controller-elite',
                'image' => public_path('frontend/images/product_images/1738393310.jpg'),
                'description' => 'Cloud gaming controller from Google.',
                'price' => 69.99,
                'quantity' => 45,
                'is_active' => true,
                'is_featured' => true,
                'in_stock' => true,
                'on_sale' => false,
            ],
            [
                'category' => 'Gaming',
                'brand' => 'Nokia',
                'name' => 'Nokia Streaming Box',
                'slug' => 'nokia-streaming-box-4k',
                'image' => public_path('frontend/images/product_images/1738393438.jpg'),
                'description' => 'Smart TV streaming device.',
                'price' => 129.99,
                'quantity' => 60,
                'is_active' => true,
                'is_featured' => false,
                'in_stock' => true,
                'on_sale' => true,
            ],
            [
                'category' => 'Smartphones',
                'brand' => 'Huawei',
                'name' => 'HUAWEI PURA 70 PRO 4G',
                'slug' => 'huawei-mirrorless-camera-x',
                'image' => public_path('frontend/images/product_images/1738394788.png'),
                'description' => 'High-end mirrorless camera from Huawei.',
                'price' => 299.99,
                'quantity' => 10,
                'is_active' => true,
                'is_featured' => true,
                'in_stock' => true,
                'on_sale' => false,
            ],
            [
                'category' => 'Cameras',
                'brand' => 'LG',
                'name' => 'LG 4K Camera',
                'slug' => 'lg-4k-camera-pro',
                'image' => public_path('frontend/images/product_images/1738394877.jpg'),
                'description' => '4K resolution camera from LG.',
                'price' => 899.99,
                'quantity' => 15,
                'is_active' => true,
                'is_featured' => false,
                'in_stock' => true,
                'on_sale' => false,
            ],
            [
                'category' => 'Monitors',
                'brand' => 'Lenovo',
                'name' => 'Lenovo ThinkVision Monitor',
                'slug' => 'lenovo-thinkvision-monitor-qhd',
                'image' => public_path('frontend/images/product_images/1738395096.png'),
                'description' => 'High-performance monitor for professionals.',
                'price' => 399.99,
                'quantity' => 25,
                'is_active' => true,
                'is_featured' => true,
                'in_stock' => true,
                'on_sale' => true,
            ],
            [
                'category' => 'Monitors',
                'brand' => 'Asus',
                'name' => 'Asus Gaming Monitor',
                'slug' => 'asus-gaming-monitor-144hz',
                'image' => public_path('frontend/images/product_images/1738395171.jpg'),
                'description' => 'Curved gaming monitor with 144Hz.',
                'price' => 499.99,
                'quantity' => 30,
                'is_active' => true,
                'is_featured' => false,
                'in_stock' => true,
                'on_sale' => false,
            ],
            [
                'category' => 'Printers',
                'brand' => 'HP',
                'name' => 'HP Deskjet Colour Printer',
                'slug' => 'razer-printer-x1',
                'image' => public_path('frontend/images/product_images/1738396050.jpg'),
                'description' => 'Gaming-themed printer from Razer.',
                'price' => 299.99,
                'quantity' => 15,
                'is_active' => true,
                'is_featured' => true,
                'in_stock' => true,
                'on_sale' => false,
            ],
            [
                'category' => 'Printers',
                'brand' => 'HP',
                'name' => 'HP Laser Printer',
                'slug' => 'HP-laser-printer-fast',
                'image' => public_path('frontend/images/product_images/1738395831.jpg'),
                'description' => 'Fast laser printer for office use.',
                'price' => 199.99,
                'quantity' => 40,
                'is_active' => true,
                'is_featured' => false,
                'in_stock' => true,
                'on_sale' => true,
            ],
            [
                'category' => 'Networking',
                'brand' => 'Amazon',
                'name' => 'Amazon WiFi Router',
                'slug' => 'amazon-wifi-router-max',
                'image' => public_path('frontend/images/product_images/1738395859.jpg'),
                'description' => 'High-speed router from Amazon.',
                'price' => 99.99,
                'quantity' => 100,
                'is_active' => true,
                'is_featured' => true,
                'in_stock' => true,
                'on_sale' => true,
            ]
        ];

        foreach ($products as $productData) {
            $this->createProduct($productData);
        }

        $this->command->info('Products seeded successfully!');
    }

    private function createProduct(array $productData): void
    {
        // Find category by name
        $category = Category::where('name->en', $productData['category'])->first();
        if (!$category) {
            $this->command->warn("Category '{$productData['category']}' not found for product '{$productData['name']}'");
            return;
        }

        // Check if product already exists
        if (Product::where('slug', $productData['slug'])->exists()) {
            $this->command->info("Product '{$productData['name']}' already exists, skipping...");
            return;
        }

        // Create the product with fields that match the actual Product model
        $product = Product::create([
            'category_id' => $category->id,
            'name' => $productData['name'],
            'slug' => $productData['slug'],
            'description' => $productData['description'],
            'price' => $productData['price'],
            'type' => 'product',
            'is_activated' => $productData['is_active'],
            'is_in_stock' => $productData['in_stock'],
            'is_shipped' => false,
            'is_trend' => $productData['is_featured'], // Map is_featured to is_trend
            'has_options' => false,
            'has_multi_price' => false,
            'has_unlimited_stock' => true,
            'has_max_cart' => false,
            'min_cart' => 1,
            'max_cart' => 10,
            'has_stock_alert' => false,
            'min_stock_alert' => 0,
            'max_stock_alert' => 0,
            'vat' => 0,
            'discount' => $productData['on_sale'] ? 10.00 : 0.00, // Add 10% discount if on sale
        ]);

        // Store brand information in product meta
        $product->meta('brand', $productData['brand']);
        $product->meta('quantity', $productData['quantity']);
        $product->meta('on_sale', $productData['on_sale']);

        // Handle image collection - always store to media collection
        if (method_exists($product, 'addMedia') && !empty($productData['image'])) {
            try {
                $imagePath = $productData['image'];

                if (file_exists($imagePath)) {
                    $product->addMedia($imagePath)
                        ->toMediaCollection('feature_image');
                    $this->command->info("Added image for product: {$productData['name']}");
                } else {
                    $this->command->warn("Image file not found for product '{$productData['name']}': {$imagePath}");
                    // Store path as meta for reference
                    $product->meta('image_path', $imagePath);
                }
            } catch (\Exception $e) {
                $this->command->warn("Could not add media for product '{$productData['name']}': " . $e->getMessage());
                // Store image path as fallback
                $product->meta('image_path', $productData['image']);
            }
        }

        $this->command->info("Created product: {$productData['name']}");
    }
}
