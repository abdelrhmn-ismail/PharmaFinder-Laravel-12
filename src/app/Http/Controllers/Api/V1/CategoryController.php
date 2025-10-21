<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        $categories = $this->categoryService->getAllCategories();
        return CategoryResource::collection($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $category = $this->categoryService->createCategory($validated);
        return new CategoryResource($category);
    }

    public function show($id)
    {
        $category = $this->categoryService->getCategory($id);
        return new CategoryResource($category);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $category = $this->categoryService->updateCategory($id, $validated);
        return new CategoryResource($category);
    }

    public function destroy($id)
    {
        $this->categoryService->deleteCategory($id);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}