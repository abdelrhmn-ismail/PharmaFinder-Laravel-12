<?php

namespace App\Repositories;

use App\Models\Category;

class CategoryRepository extends BaseRepository
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function getWithRelations()
    {
        return $this->model->with(['parent', 'children'])->paginate();
    }

    public function findWithRelations(int $id)
    {
        return $this->model->with(['parent', 'children', 'products'])->findOrFail($id);
    }

    public function getChildCategories(int $categoryId)
    {
        return $this->model->where('parent_id', $categoryId)->get();
    }
}