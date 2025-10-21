<?php

namespace App\Services;

use App\Repositories\CategoryRepository;
use Illuminate\Support\Str;

class CategoryService
{
    protected $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function getAllCategories()
    {
        return $this->categoryRepository->getWithRelations();
    }

    public function getCategory(int $id)
    {
        return $this->categoryRepository->findWithRelations($id);
    }

    public function createCategory(array $data)
    {
        $data['slug'] = Str::slug($data['name']);
        return $this->categoryRepository->create($data);
    }

    public function updateCategory(int $id, array $data)
    {
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        return $this->categoryRepository->update($id, $data);
    }

    public function deleteCategory(int $id)
    {
        return $this->categoryRepository->delete($id);
    }

    public function getChildCategories(int $categoryId)
    {
        return $this->categoryRepository->getChildCategories($categoryId);
    }
}