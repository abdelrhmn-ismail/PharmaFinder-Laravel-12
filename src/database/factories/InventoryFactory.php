<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory>
 */
class InventoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pharmacy_id' => \App\Models\Pharmacy::factory(),
            'product_variant_id' => \App\Models\ProductVariant::factory(),
            'price' => function (array $attributes) {
                $variant = \App\Models\ProductVariant::find($attributes['product_variant_id']);
                $basePrice = $variant ? $variant->suggested_price : 100;
                // Random price variation ±20%
                return fake()->randomFloat(2, $basePrice * 0.8, $basePrice * 1.2);
            },
            'quantity' => fake()->numberBetween(0, 1000),
            'is_available' => function (array $attributes) {
                return $attributes['quantity'] > 0;
            },
            'last_stock_update' => fake()->dateTimeThisMonth(),
        ];
    }
}
