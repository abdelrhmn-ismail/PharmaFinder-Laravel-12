<?php

namespace App\Services;

use App\Repositories\ProductVariantRepository;
use Illuminate\Support\Str;

class ProductVariantService
{
    protected $variantRepository;

    public function __construct(ProductVariantRepository $variantRepository)
    {
        $this->variantRepository = $variantRepository;
    }

    public function getAllVariants()
    {
        return $this->variantRepository->getWithRelations();
    }

    public function getVariant(int $id)
    {
        return $this->variantRepository->findWithRelations($id);
    }

    public function createVariant(array $data)
    {
        // Generate SKU if not provided
        if (!isset($data['sku'])) {
            $data['sku'] = $this->generateSku($data['product_id'], $data['name']);
        }
        
        return $this->variantRepository->create($data);
    }

    public function updateVariant(int $id, array $data)
    {
        return $this->variantRepository->update($id, $data);
    }

    public function deleteVariant(int $id)
    {
        return $this->variantRepository->delete($id);
    }

    private function generateSku(int $productId, string $name): string
    {
        return Str::upper(Str::slug($name)) . '-' . $productId;
    }
}