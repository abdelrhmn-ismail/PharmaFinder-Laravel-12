<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        return [
            'name' => ucwords($name),
            'slug' => str()->slug($name) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'description' => fake()->paragraph(),
            'manufacturer_id' => \App\Models\Manufacturer::factory(),
            'category_id' => \App\Models\Category::factory(),
            'is_active' => true,
            'is_prescription_required' => fake()->boolean(20), // 20% chance of requiring prescription
        ];
    }
}
