<?php

namespace App\Repositories;

use App\Models\ProductVariant;

class ProductVariantRepository extends BaseRepository
{
    public function __construct(ProductVariant $model)
    {
        parent::__construct($model);
    }

    public function getWithRelations()
    {
        return $this->model->with(['product'])->paginate();
    }

    public function findWithRelations(int $id)
    {
        return $this->model->with(['product', 'inventory'])->findOrFail($id);
    }

    public function findBySku(string $sku)
    {
        return $this->model->where('sku', $sku)->firstOrFail();
    }
}