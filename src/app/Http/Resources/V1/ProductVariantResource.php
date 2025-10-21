<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'dosage' => $this->dosage,
            'form' => $this->form,
            'package_size' => $this->package_size,
            'suggested_price' => $this->suggested_price,
            'is_active' => $this->is_active,
            'inventory' => $this->whenLoaded('inventory'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}