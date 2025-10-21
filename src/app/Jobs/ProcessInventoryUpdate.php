<?php

namespace App\Jobs;

use App\Models\Pharmacy;
use App\Models\ProductVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessInventoryUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $pharmacyId;
    private $inventoryUpdates;

    public function __construct(int $pharmacyId, array $inventoryUpdates)
    {
        $this->pharmacyId = $pharmacyId;
        $this->inventoryUpdates = $inventoryUpdates;
    }

    public function handle(): void
    {
        $pharmacy = Pharmacy::findOrFail($this->pharmacyId);
        $skus = array_column($this->inventoryUpdates, 'sku');
        
        // Get all variants in one query to avoid N+1
        $variants = ProductVariant::whereIn('sku', $skus)
            ->get()
            ->keyBy('sku');

        DB::beginTransaction();
        try {
            foreach ($this->inventoryUpdates as $update) {
                $variant = $variants->get($update['sku']);
                
                if (!$variant) {
                    Log::warning("SKU not found: {$update['sku']} for pharmacy {$this->pharmacyId}");
                    continue;
                }

                // Update or create inventory record
                $pharmacy->inventory()->updateOrCreate(
                    ['product_variant_id' => $variant->id],
                    [
                        'price' => $update['price'],
                        'quantity' => $update['quantity'],
                        'is_available' => $update['quantity'] > 0,
                        'last_stock_update' => now(),
                    ]
                );
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Inventory update failed for pharmacy {$this->pharmacyId}: " . $e->getMessage());
            throw $e;
        }
    }
}