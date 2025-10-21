<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use Illuminate\Support\Str;

class ProductService
{
    protected $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getAllProducts()
    {
        return $this->productRepository->getWithRelations();
    }

    public function getProduct(int $id)
    {
        return $this->productRepository->findWithRelations($id);
    }

    public function createProduct(array $data)
    {
        // Add slug generation logic
        $data['slug'] = Str::slug($data['name']);
        
        return $this->productRepository->create($data);
    }

    public function updateProduct(int $id, array $data)
    {
        // Add slug update logic if name changes
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        return $this->productRepository->update($id, $data);
    }

    public function deleteProduct(int $id)
    {
        return $this->productRepository->delete($id);
    }
}