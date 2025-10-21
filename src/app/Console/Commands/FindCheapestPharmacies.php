<?php

namespace App\Console\Commands;

use App\Models\ProductVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FindCheapestPharmacies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:find-cheapest-pharmacies 
                            {product_variant_id : The ID of the product variant to search for}
                            {--min-quantity= : Only include pharmacies with at least this quantity in stock}
                            {--output= : Save the JSON output to a file instead of displaying it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find the top 5 cheapest pharmacies for a given product variant';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $variantId = $this->argument('product_variant_id');
        $minQuantity = $this->option('min-quantity');
        $outputPath = $this->option('output');

        try {
            // Find the product variant
            $variant = ProductVariant::with(['product'])->findOrFail($variantId);

            // Query for pharmacies with this variant
            $query = $variant->pharmacies()
                ->select([
                    'pharmacies.*',
                    'inventories.price',
                    'inventories.quantity',
                    'inventories.is_available'
                ])
                ->where('inventories.is_available', true)
                ->when($minQuantity, function ($query) use ($minQuantity) {
                    $query->where('inventories.quantity', '>=', $minQuantity);
                })
                ->orderBy('inventories.price', 'asc')
                ->limit(5);

            $pharmacies = $query->get()->map(function ($pharmacy) {
                return [
                    'pharmacy_id' => $pharmacy->id,
                    'name' => $pharmacy->name,
                    'address' => $pharmacy->address,
                    'city' => $pharmacy->city,
                    'phone' => $pharmacy->phone,
                    'price' => number_format($pharmacy->price, 2),
                    'quantity' => $pharmacy->quantity,
                    'location' => [
                        'latitude' => $pharmacy->latitude,
                        'longitude' => $pharmacy->longitude,
                    ],
                ];
            });

            $result = [
                'product_variant' => [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'sku' => $variant->sku,
                    'product_name' => $variant->product->name,
                    'dosage' => $variant->dosage,
                    'form' => $variant->form,
                    'package_size' => $variant->package_size,
                ],
                'pharmacies' => $pharmacies,
                'query_params' => [
                    'min_quantity' => $minQuantity ?? 'not specified',
                ],
                'timestamp' => now()->toIso8601String(),
            ];

            // Handle output
            if ($outputPath) {
                $outputPath = trim($outputPath);
                
                // Ensure the directory exists
                $directory = dirname($outputPath);
                if (!File::exists($directory)) {
                    File::makeDirectory($directory, 0755, true);
                }

                // Save to file
                File::put($outputPath, json_encode($result, JSON_PRETTY_PRINT));
                $this->info("Results have been saved to: {$outputPath}");
            } else {
                // Display in console
                $this->info('Found ' . $pharmacies->count() . ' pharmacies selling ' . $variant->name);
                $this->newLine();
                $this->info(json_encode($result, JSON_PRETTY_PRINT));
            }

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->error("Product variant with ID {$variantId} not found.");
            return 1;
        } catch (\Exception $e) {
            $this->error("An error occurred: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }
}