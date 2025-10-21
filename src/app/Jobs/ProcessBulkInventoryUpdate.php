<?php

namespace App\Jobs;

use App\Models\Inventory;
use App\Models\Pharmacy;
use App\Models\ProductVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class ProcessBulkInventoryUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        $pharmacy = Auth::user()->pharmacy;
        $processedSkus = [];

        foreach ($this->data['inventory'] as $item) {
            if (in_array($item['sku'], $processedSkus)) {
                continue; // Skip duplicate SKUs
            }

            $productVariant = ProductVariant::where('sku', $item['sku'])->first();
            
            if (!$productVariant) {
                continue; // Skip if variant not found
            }

            Inventory::updateOrCreate(
                [
                    'pharmacy_id' => $pharmacy->id,
                    'product_variant_id' => $productVariant->id,
                ],
                [
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'is_available' => $item['is_available'],
                    'last_stock_update' => now(),
                ]
            );

            $processedSkus[] = $item['sku'];
        }
    }
}