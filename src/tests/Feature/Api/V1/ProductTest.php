<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use App\Models\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;
    protected Manufacturer $manufacturer;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create necessary relationships
        $this->category = Category::factory()->create();
        $this->manufacturer = Manufacturer::factory()->create();
    }

    public function test_can_get_products_list(): void
    {
        $products = Product::factory(3)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'slug',
                            'description',
                            'manufacturer_id',
                            'category_id',
                            'is_active',
                            'is_prescription_required',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]);
    }

    public function test_can_create_product(): void
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'manufacturer_id' => $this->manufacturer->id,
            'category_id' => $this->category->id,
            'is_active' => true,
            'is_prescription_required' => false
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(201)
                ->assertJsonFragment([
                    'name' => 'Test Product',
                    'description' => 'Test Description'
                ]);
    }

    public function test_can_update_product(): void
    {
        $product = Product::factory()->create();
        
        $updateData = [
            'name' => 'Updated Product',
            'description' => 'Updated Description'
        ];

        $response = $this->putJson("/api/v1/products/{$product->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonFragment($updateData);
    }

    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/products', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'manufacturer_id', 'category_id']);
    }
}