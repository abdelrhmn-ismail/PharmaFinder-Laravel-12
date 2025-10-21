<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'pharmacy_id',
        'product_variant_id',
        'price',
        'quantity',
        'is_available',
        'last_stock_update',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'is_available' => 'boolean',
        'last_stock_update' => 'datetime',
    ];

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    // Scope to find cheapest pharmacies for a product variant
    public function scopeCheapestPharmacies($query, $productVariantId, $minQuantity = 0)
    {
        return $query->where('product_variant_id', $productVariantId)
                    ->where('is_available', true)
                    ->where('quantity', '>=', $minQuantity)
                    ->orderBy('price', 'asc')
                    ->with('pharmacy');
    }
}
