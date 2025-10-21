<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Pharmacy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up test data
        $manufacturer = Manufacturer::factory()->create(['name' => 'Test Manufacturer']);
        $category = Category::factory()->create(['name' => 'Test Category']);
        $childCategory = Category::factory()->create([
            'name' => 'Child Category',
            'parent_id' => $category->id,
        ]);

        $product = Product::factory()->create([
            'name' => 'Test Product',
            'manufacturer_id' => $manufacturer->id,
            'category_id' => $childCategory->id,
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => 'Test Variant',
            'form' => 'tablet',
            'suggested_price' => 19.99,
        ]);

        $pharmacy = Pharmacy::factory()->create();
        $variant->pharmacies()->attach($pharmacy->id, [
            'price' => 18.99,
            'quantity' => 100,
            'is_available' => true,
        ]);
    }

    public function test_can_search_products()
    {
        $response = $this->getJson('/api/v1/search?query=Test');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'sku',
                        'product',
                    ],
                ],
                'meta',
            ])
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_by_category()
    {
        $category = Category::where('name', 'Test Category')->first();

        $response = $this->getJson("/api/v1/search?query=Test&category_id={$category->id}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_by_manufacturer()
    {
        $manufacturer = Manufacturer::where('name', 'Test Manufacturer')->first();

        $response = $this->getJson("/api/v1/search?query=Test&manufacturer_id={$manufacturer->id}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_by_price_range()
    {
        $response = $this->getJson('/api/v1/search?query=Test&min_price=15&max_price=25');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $response = $this->getJson('/api/v1/search?query=Test&min_price=30');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_can_filter_by_availability()
    {
        $response = $this->getJson('/api/v1/search?query=Test&is_available=true');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_get_search_facets()
    {
        $response = $this->getJson('/api/v1/search/facets');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'forms',
                    'price_range' => ['min', 'max'],
                    'categories' => [
                        '*' => ['id', 'name'],
                    ],
                    'manufacturers' => [
                        '*' => ['id', 'name'],
                    ],
                ],
            ]);
    }
}