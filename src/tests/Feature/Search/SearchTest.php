<?php

namespace Tests\Feature\Search;

use Tests\TestCase;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $category = Category::factory()->create(['name' => 'Pain Relief']);
        $manufacturer = Manufacturer::factory()->create(['name' => 'PharmaCorp']);
        
        $product = Product::factory()->create([
            'name' => 'Ibuprofen',
            'category_id' => $category->id,
            'manufacturer_id' => $manufacturer->id
        ]);

        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => 'Ibuprofen 200mg Tablets',
            'price' => 9.99
        ]);
    }

    public function test_can_search_products(): void
    {
        $response = $this->getJson('/api/v1/search?query=ibuprofen');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'description',
                            'product' => [
                                'name',
                                'category' => [
                                    'name'
                                ],
                                'manufacturer' => [
                                    'name'
                                ]
                            ]
                        ]
                    ]
                ])
                ->assertJsonFragment([
                    'name' => 'Ibuprofen 200mg Tablets'
                ]);
    }

    public function test_handles_typos_in_search(): void
    {
        $response = $this->getJson('/api/v1/search?query=ibuprofin'); // Intentional typo

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'name' => 'Ibuprofen 200mg Tablets'
                ]);
    }

    public function test_can_filter_by_category(): void
    {
        $response = $this->getJson('/api/v1/search?query=ibuprofen&category=pain-relief');

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'name' => 'Ibuprofen 200mg Tablets'
                ]);

        $response = $this->getJson('/api/v1/search?query=ibuprofen&category=wrong-category');

        $response->assertStatus(200)
                ->assertJsonCount(0, 'data');
    }

    public function test_can_filter_by_manufacturer(): void
    {
        $response = $this->getJson('/api/v1/search?query=ibuprofen&manufacturer=pharmacorp');

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'name' => 'Ibuprofen 200mg Tablets'
                ]);

        $response = $this->getJson('/api/v1/search?query=ibuprofen&manufacturer=wrong-manufacturer');

        $response->assertStatus(200)
                ->assertJsonCount(0, 'data');
    }

    public function test_can_filter_by_price_range(): void
    {
        $response = $this->getJson('/api/v1/search?query=ibuprofen&min_price=5&max_price=15');

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'name' => 'Ibuprofen 200mg Tablets'
                ]);

        $response = $this->getJson('/api/v1/search?query=ibuprofen&min_price=20&max_price=30');

        $response->assertStatus(200)
                ->assertJsonCount(0, 'data');
    }

    public function test_can_filter_by_availability(): void
    {
        $response = $this->getJson('/api/v1/search?query=ibuprofen&is_available=true');

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'name' => 'Ibuprofen 200mg Tablets'
                ]);
    }

    public function test_returns_empty_results_for_no_matches(): void
    {
        $response = $this->getJson('/api/v1/search?query=nonexistentproduct');

        $response->assertStatus(200)
                ->assertJsonCount(0, 'data');
    }
}