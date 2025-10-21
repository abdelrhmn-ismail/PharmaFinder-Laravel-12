<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductVariantTest extends TestCase
{
    use RefreshDatabase;

    protected Product $product;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->product = Product::factory()->create();
    }

    public function test_can_get_product_variants_list(): void
    {
        ProductVariant::factory(3)->create([
            'product_id' => $this->product->id
        ]);

        $response = $this->getJson('/api/v1/product-variants');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'product_id',
                            'name',
                            'sku',
                            'description',
                            'dosage',
                            'form',
                            'package_size',
                            'suggested_price',
                            'is_active',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]);
    }

    public function test_can_create_product_variant(): void
    {
        $variantData = [
            'product_id' => $this->product->id,
            'name' => 'Test Variant',
            'sku' => 'TEST-SKU-001',
            'description' => 'Test Description',
            'dosage' => '100mg',
            'form' => 'tablet',
            'package_size' => '30 units',
            'suggested_price' => 19.99,
            'is_active' => true
        ];

        $response = $this->postJson('/api/v1/product-variants', $variantData);

        $response->assertStatus(201)
                ->assertJsonFragment([
                    'name' => 'Test Variant',
                    'sku' => 'TEST-SKU-001'
                ]);
    }

    public function test_can_update_product_variant(): void
    {
        $variant = ProductVariant::factory()->create([
            'product_id' => $this->product->id
        ]);

        $updateData = [
            'name' => 'Updated Variant',
            'suggested_price' => 29.99
        ];

        $response = $this->putJson("/api/v1/product-variants/{$variant->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonFragment($updateData);
    }

    public function test_can_delete_product_variant(): void
    {
        $variant = ProductVariant::factory()->create([
            'product_id' => $this->product->id
        ]);

        $response = $this->deleteJson("/api/v1/product-variants/{$variant->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('product_variants', ['id' => $variant->id]);
    }

    public function test_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/product-variants', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['product_id', 'name', 'sku']);
    }

    public function test_validates_unique_sku(): void
    {
        $variant = ProductVariant::factory()->create([
            'product_id' => $this->product->id
        ]);

        $response = $this->postJson('/api/v1/product-variants', [
            'product_id' => $this->product->id,
            'name' => 'Another Variant',
            'sku' => $variant->sku // Using existing SKU
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['sku']);
    }
}