<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating categories...');
        // Create main categories
        $mainCategories = \App\Models\Category::factory(10)->create(['parent_id' => null]);
        
        // Create subcategories
        foreach ($mainCategories as $category) {
            \App\Models\Category::factory(rand(3, 5))->create(['parent_id' => $category->id]);
        }

        $this->command->info('Creating manufacturers...');
        // Create manufacturers (100 manufacturers)
        $manufacturers = \App\Models\Manufacturer::factory(100)->create();

        $this->command->info('Creating products and variants...');
        // Create products in chunks to avoid memory issues
        $chunkSize = 1000;
        $totalProducts = 50000;
        
        for ($i = 0; $i < $totalProducts; $i += $chunkSize) {
            $this->command->info("Creating products batch " . ($i / $chunkSize + 1));
            
            $products = [];
            for ($j = 0; $j < min($chunkSize, $totalProducts - $i); $j++) {
                $productId = $i + $j + 1;
                $products[] = [
                    'name' => "Product {$productId}",
                    'slug' => "product-{$productId}",
                    'description' => fake()->paragraph(),
                    'manufacturer_id' => $manufacturers->random()->id,
                    'category_id' => \App\Models\Category::inRandomOrder()->first()->id,
                    'is_active' => true,
                    'is_prescription_required' => fake()->boolean(20),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            \App\Models\Product::insert($products);
            
            // Create variants for the products we just created
            $lastBatchProducts = \App\Models\Product::orderBy('id', 'desc')->take($chunkSize)->get();
            foreach ($lastBatchProducts as $product) {
                $variantCount = rand(1, 3);
                $variants = [];
                for ($v = 0; $v < $variantCount; $v++) {
                    $variants[] = [
                        'product_id' => $product->id,
                        'name' => $product->name . ' Variant ' . ($v + 1),
                        'sku' => 'SKU-' . $product->id . '-' . ($v + 1),
                        'description' => fake()->paragraph(),
                        'dosage' => fake()->randomElement(['25mg', '50mg', '100mg', '250mg', '500mg']),
                        'form' => fake()->randomElement(['tablet', 'capsule', 'syrup', 'injection']),
                        'package_size' => fake()->randomElement(['10 units', '20 units', '30 units', '50 units']),
                        'suggested_price' => fake()->randomFloat(2, 5, 1000),
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                \App\Models\ProductVariant::insert($variants);
            }
        }

        $this->command->info('Creating pharmacies...');
        // Create pharmacies in chunks
        $totalPharmacies = 20000;
        for ($i = 0; $i < $totalPharmacies; $i += $chunkSize) {
            $this->command->info("Creating pharmacies batch " . ($i / $chunkSize + 1));
            
            $pharmacies = [];
            for ($j = 0; $j < min($chunkSize, $totalPharmacies - $i); $j++) {
                $pharmacyId = $i + $j + 1;
                $pharmacies[] = [
                    'name' => "Pharmacy {$pharmacyId}",
                    'slug' => "pharmacy-{$pharmacyId}",
                    'description' => fake()->paragraph(),
                    'address' => fake()->streetAddress(),
                    'city' => fake()->city(),
                    'state' => fake()->state(),
                    'postal_code' => fake()->postcode(),
                    'phone' => fake()->phoneNumber(),
                    'email' => fake()->email(),
                    'latitude' => fake()->latitude(24.0, 49.0),
                    'longitude' => fake()->longitude(-125.0, -67.0),
                    'is_active' => true,
                    'is_24_hours' => fake()->boolean(10),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            \App\Models\Pharmacy::insert($pharmacies);
        }

        $this->command->info('Creating inventory records...');
        // Create inventory records in chunks
        $variants = \App\Models\ProductVariant::all(['id', 'suggested_price']);
        $pharmacies = \App\Models\Pharmacy::all(['id']);
        
        foreach ($pharmacies->chunk(100) as $pharmacyChunk) {
            $inventoryRecords = [];
            foreach ($pharmacyChunk as $pharmacy) {
                // Each pharmacy stocks 100-300 random variants
                $randomVariants = $variants->random(rand(100, 300));
                foreach ($randomVariants as $variant) {
                    $inventoryRecords[] = [
                        'pharmacy_id' => $pharmacy->id,
                        'product_variant_id' => $variant->id,
                        'price' => fake()->randomFloat(2, $variant->suggested_price * 0.8, $variant->suggested_price * 1.2),
                        'quantity' => fake()->numberBetween(0, 1000),
                        'is_available' => true,
                        'last_stock_update' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            \App\Models\Inventory::insert($inventoryRecords);
        }

        $this->command->info('Seeding completed successfully!');
    }
}
