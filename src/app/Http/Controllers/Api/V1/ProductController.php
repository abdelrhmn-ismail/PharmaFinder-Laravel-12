<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index()
    {
        $products = $this->productService->getAllProducts();
        return ProductResource::collection($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'manufacturer_id' => 'required|exists:manufacturers,id',
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'boolean',
            'is_prescription_required' => 'boolean',
        ]);

        $product = $this->productService->createProduct($validated);
        return new ProductResource($product);
    }

    public function show($id)
    {
        $product = $this->productService->getProduct($id);
        return new ProductResource($product);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'manufacturer_id' => 'sometimes|exists:manufacturers,id',
            'category_id' => 'sometimes|exists:categories,id',
            'is_active' => 'sometimes|boolean',
            'is_prescription_required' => 'sometimes|boolean',
        ]);

        $product = $this->productService->updateProduct($id, $validated);
        return new ProductResource($product);
    }

    public function destroy($id)
    {
        $this->productService->deleteProduct($id);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}