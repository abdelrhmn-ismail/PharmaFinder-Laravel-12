<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository extends BaseRepository
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function getWithRelations()
    {
        return $this->model->with(['manufacturer', 'category'])->paginate();
    }

    public function findWithRelations(int $id)
    {
        return $this->model->with(['manufacturer', 'category', 'variants'])->findOrFail($id);
    }
}