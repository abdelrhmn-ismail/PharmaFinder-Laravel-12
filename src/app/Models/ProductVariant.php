<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class ProductVariant extends Model
{
    use HasFactory, Searchable;
    
    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        $array = $this->toArray();

        // Load the relationships we want to include in the search
        $this->load(['product.manufacturer', 'product.category']);

        // Add related data to the searchable array
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'dosage' => $this->dosage,
            'form' => $this->form,
            'package_size' => $this->package_size,
            'product_name' => $this->product->name,
            'manufacturer_name' => $this->product->manufacturer->name,
            'category_name' => $this->product->category->name,
        ];
    }
    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'description',
        'dosage',
        'form',
        'package_size',
        'suggested_price',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'suggested_price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function pharmacies()
    {
        return $this->belongsToMany(Pharmacy::class, 'inventories')
            ->withPivot(['price', 'quantity', 'is_available', 'last_stock_update']);
    }
}

