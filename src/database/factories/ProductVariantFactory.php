<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $forms = ['tablet', 'capsule', 'syrup', 'injection', 'cream', 'ointment', 'spray'];
        $dosages = ['25mg', '50mg', '100mg', '250mg', '500mg', '1000mg'];
        $packages = ['10 units', '20 units', '30 units', '50 units', '100 units', '60ml', '100ml', '250ml'];
        
        return [
            'product_id' => \App\Models\Product::factory(),
            'name' => function (array $attributes) {
                $product = \App\Models\Product::find($attributes['product_id']);
                return $product ? $product->name : fake()->words(3, true);
            },
            'sku' => fake()->unique()->ean13(),
            'description' => fake()->paragraph(),
            'dosage' => fake()->randomElement($dosages),
            'form' => fake()->randomElement($forms),
            'package_size' => fake()->randomElement($packages),
            'suggested_price' => fake()->randomFloat(2, 5, 1000),
            'is_active' => true,
        ];
    }
}
