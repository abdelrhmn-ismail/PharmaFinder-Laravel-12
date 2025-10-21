<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantApiTest extends TestCase
{
    use RefreshDatabase;

    private $manufacturer;
    private $category;
    private $product;
    private $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manufacturer = Manufacturer::factory()->create();
        $this->category = Category::factory()->create();
        $this->product = Product::factory()->create([
            'manufacturer_id' => $this->manufacturer->id,
            'category_id' => $this->category->id,
        ]);
        $this->variant = ProductVariant::factory()->create([
            'product_id' => $this->product->id,
        ]);
    }

    public function test_can_list_product_variants()
    {
        $response = $this->getJson('/api/v1/product-variants');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'sku',
                        'description',
                        'dosage',
                        'form',
                        'package_size',
                        'suggested_price',
                        'is_active',
                        'product' => [
                            'id',
                            'name',
                            'manufacturer',
                            'category',
                        ],
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_can_create_product_variant()
    {
        $data = [
            'product_id' => $this->product->id,
            'name' => 'Test Variant',
            'sku' => 'TEST-SKU-001',
            'description' => 'Test description',
            'dosage' => '100mg',
            'form' => 'tablet',
            'package_size' => '30 units',
            'suggested_price' => 19.99,
            'is_active' => true,
        ];

        $response = $this->postJson('/api/v1/product-variants', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'sku',
                    'description',
                    'dosage',
                    'form',
                    'package_size',
                    'suggested_price',
                    'is_active',
                ],
            ])
            ->assertJson([
                'data' => [
                    'name' => $data['name'],
                    'sku' => $data['sku'],
                ],
            ]);
    }

    public function test_can_show_product_variant()
    {
        $response = $this->getJson("/api/v1/product-variants/{$this->variant->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'sku',
                    'description',
                    'dosage',
                    'form',
                    'package_size',
                    'suggested_price',
                    'is_active',
                    'product',
                ],
            ]);
    }

    public function test_can_update_product_variant()
    {
        $data = [
            'name' => 'Updated Variant Name',
            'description' => 'Updated description',
        ];

        $response = $this->putJson("/api/v1/product-variants/{$this->variant->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => $data['name'],
                    'description' => $data['description'],
                ],
            ]);
    }

    public function test_can_delete_product_variant()
    {
        $response = $this->deleteJson("/api/v1/product-variants/{$this->variant->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('product_variants', ['id' => $this->variant->id]);
    }
}