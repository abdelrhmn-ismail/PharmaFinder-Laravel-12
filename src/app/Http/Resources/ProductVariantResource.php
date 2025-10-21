<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray($request): array
    {
        $data = [
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        if ($this->relationLoaded('product')) {
            $data['product'] = new ProductResource($this->product);
        }

        if ($this->relationLoaded('inventories')) {
            $data['inventory'] = $this->inventories->first();
        }

        return $data;
    }
}