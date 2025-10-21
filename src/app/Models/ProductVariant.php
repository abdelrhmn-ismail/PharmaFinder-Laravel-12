<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;
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

