<?php

namespace App\Console\Commands;

use App\Models\ProductVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FindCheapestPharmaciesCommand extends Command
{
    protected $signature = 'app:find-cheapest-pharmacies 
                            {product_variant_id : The ID of the product variant to search for}
                            {--min-quantity=0 : Minimum quantity required in stock}
                            {--output= : Optional JSON file path to save results}';

    protected $description = 'Find the 5 cheapest pharmacies selling a specific product variant';

    public function handle()
    {
        $variantId = $this->argument('product_variant_id');
        $minQuantity = $this->option('min-quantity');
        $outputPath = $this->option('output');

        if (!is_numeric($minQuantity) || $minQuantity < 0) {
            $this->error('The min-quantity option must be a valid integer.');
            return 1;
        }

        $variant = ProductVariant::find($variantId);

        if (!$variant) {
            $this->error("Product variant with ID {$variantId} not found.");
            return 1;
        }

        $pharmacies = $variant->pharmacies()
            ->where('is_available', true)
            ->where('quantity', '>=', $minQuantity)
            ->orderBy('price')
            ->take(5)
            ->get(['pharmacies.*', 'inventories.price', 'inventories.quantity'])
            ->map(function ($pharmacy) {
                return [
                    'id' => $pharmacy->id,
                    'name' => $pharmacy->name,
                    'address' => $pharmacy->address,
                    'price' => $pharmacy->pivot->price,
                    'quantity' => $pharmacy->pivot->quantity,
                ];
            });

        $this->info("Found {$pharmacies->count()} pharmacies selling {$variant->name}");

        if ($outputPath) {
            $output = [
                'product_variant' => [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'sku' => $variant->sku,
                ],
                'pharmacies' => $pharmacies->toArray(),
            ];

            File::put($outputPath, json_encode($output, JSON_PRETTY_PRINT));
            $this->info("Results saved to {$outputPath}");
        } else {
            $this->table(
                ['Pharmacy', 'Price', 'Quantity'],
                $pharmacies->map(fn($p) => [$p['name'], $p['price'], $p['quantity']])
            );
        }

        return 0;
    }
}