<?php

namespace Tests\Feature\Console;

use App\Models\ProductVariant;
use App\Models\Pharmacy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class FindCheapestPharmaciesCommandTest extends TestCase
{
    use RefreshDatabase;

    private $variant;
    private $pharmacies;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a product variant
        $this->variant = ProductVariant::factory()->create();

        // Create pharmacies with different prices
        $this->pharmacies = collect();
        $prices = [10.99, 11.99, 12.99, 13.99, 14.99, 15.99];
        $quantities = [5, 10, 15, 20, 25, 30];

        foreach ($prices as $index => $price) {
            $pharmacy = Pharmacy::factory()->create();
            $this->variant->pharmacies()->attach($pharmacy->id, [
                'price' => $price,
                'quantity' => $quantities[$index],
                'is_available' => true,
            ]);
            $this->pharmacies->push($pharmacy);
        }
    }

    public function test_command_returns_top_5_cheapest_pharmacies()
    {
        $this->artisan("app:find-cheapest-pharmacies {$this->variant->id}")
            ->assertSuccessful()
            ->expectsOutput('Found 5 pharmacies selling ' . $this->variant->name);
    }

    public function test_command_respects_minimum_quantity()
    {
        $this->artisan("app:find-cheapest-pharmacies {$this->variant->id} --min-quantity=20")
            ->assertSuccessful()
            ->expectsOutput('Found 3 pharmacies selling ' . $this->variant->name);
    }

    public function test_command_can_save_output_to_file()
    {
        $outputPath = storage_path('app/test-output.json');

        // Ensure the file doesn't exist before the test
        if (File::exists($outputPath)) {
            File::delete($outputPath);
        }

        $this->artisan("app:find-cheapest-pharmacies {$this->variant->id} --output={$outputPath}")
            ->assertSuccessful();

        $this->assertTrue(File::exists($outputPath));
        
        $content = json_decode(File::get($outputPath), true);
        
        $this->assertArrayHasKey('product_variant', $content);
        $this->assertArrayHasKey('pharmacies', $content);
        $this->assertCount(5, $content['pharmacies']);
        
        // Clean up
        File::delete($outputPath);
    }

    public function test_command_fails_for_nonexistent_variant()
    {
        $this->artisan('app:find-cheapest-pharmacies 99999')
            ->assertExitCode(1)
            ->expectsOutput('Product variant with ID 99999 not found.');
    }

    public function test_command_validates_minimum_quantity()
    {
        $this->artisan("app:find-cheapest-pharmacies {$this->variant->id} --min-quantity=abc")
            ->assertExitCode(1)
            ->expectsOutput('The min-quantity option must be a valid integer.');
    }
}