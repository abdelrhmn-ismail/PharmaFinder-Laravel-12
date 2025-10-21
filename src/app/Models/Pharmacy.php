<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pharmacy extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'address',
        'city',
        'state',
        'postal_code',
        'phone',
        'email',
        'latitude',
        'longitude',
        'is_active',
        'is_24_hours',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_24_hours' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    // Product variants stocked by this pharmacy
    public function productVariants()
    {
        return $this->belongsToMany(ProductVariant::class, 'inventories')
            ->withPivot(['price', 'quantity', 'is_available', 'last_stock_update']);
    }

    // Scope for finding nearby pharmacies
    public function scopeNearby($query, $latitude, $longitude, $radius = 5)
    {
        $haversine = "(6371 * acos(cos(radians($latitude)) 
                     * cos(radians(latitude)) 
                     * cos(radians(longitude) - radians($longitude)) 
                     + sin(radians($latitude)) 
                     * sin(radians(latitude))))";
        
        return $query->selectRaw("*, {$haversine} AS distance")
                    ->having('distance', '<=', $radius)
                    ->orderBy('distance');
    }
}
