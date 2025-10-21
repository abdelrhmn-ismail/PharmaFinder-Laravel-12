<?php

namespace Tests\Feature\Api\V1;

use App\Models\Pharmacy;
use App\Models\ProductVariant;
use App\Jobs\ProcessInventoryUpdate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class InventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private $pharmacy;
    private $variants;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pharmacy = Pharmacy::factory()->create();
        $this->variants = ProductVariant::factory()->count(3)->create();
    }

    public function test_can_queue_bulk_inventory_update()
    {
        Queue::fake();

        $data = [
            'items' => [
                [
                    'sku' => $this->variants[0]->sku,
                    'price' => 19.99,
                    'quantity' => 100,
                    'is_available' => true,
                ],
                [
                    'sku' => $this->variants[1]->sku,
                    'price' => 29.99,
                    'quantity' => 50,
                    'is_available' => true,
                ],
            ],
        ];

        $response = $this->postJson("/api/v1/pharmacies/{$this->pharmacy->id}/inventory/bulk-update", $data);

        $response->assertStatus(202)
            ->assertJson([
                'message' => 'Inventory update has been queued for processing',
                'job_id' => true,
            ]);

        Queue::assertPushed(ProcessInventoryUpdate::class, function ($job) use ($data) {
            return $job->pharmacy->id === $this->pharmacy->id &&
                   count($job->items) === count($data['items']);
        });
    }

    public function test_validates_inventory_update_data()
    {
        $response = $this->postJson("/api/v1/pharmacies/{$this->pharmacy->id}/inventory/bulk-update", [
            'items' => [
                [
                    'sku' => 'non-existent-sku',
                    'price' => -10, // invalid price
                    'quantity' => 'not-a-number', // invalid quantity
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.sku', 'items.0.price', 'items.0.quantity']);
    }

    public function test_handles_duplicate_skus_in_bulk_update()
    {
        $data = [
            'items' => [
                [
                    'sku' => $this->variants[0]->sku,
                    'price' => 19.99,
                    'quantity' => 100,
                ],
                [
                    'sku' => $this->variants[0]->sku, // duplicate SKU
                    'price' => 29.99,
                    'quantity' => 50,
                ],
            ],
        ];

        $response = $this->postJson("/api/v1/pharmacies/{$this->pharmacy->id}/inventory/bulk-update", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }
}