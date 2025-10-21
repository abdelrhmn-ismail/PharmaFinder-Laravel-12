<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProductVariantResource;
use App\Services\ProductVariantService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductVariantController extends Controller
{
    protected $variantService;

    public function __construct(ProductVariantService $variantService)
    {
        $this->variantService = $variantService;
    }

    public function index()
    {
        $variants = $this->variantService->getAllVariants();
        return ProductVariantResource::collection($variants);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:product_variants,sku',
            'description' => 'required|string',
            'dosage' => 'required|string',
            'form' => 'required|string',
            'package_size' => 'required|string',
            'suggested_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $variant = $this->variantService->createVariant($validated);
        return new ProductVariantResource($variant);
    }

    public function show($id)
    {
        $variant = $this->variantService->getVariant($id);
        return new ProductVariantResource($variant);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'product_id' => 'sometimes|exists:products,id',
            'name' => 'sometimes|string|max:255',
            'sku' => 'sometimes|string|unique:product_variants,sku,' . $id,
            'description' => 'sometimes|string',
            'dosage' => 'sometimes|string',
            'form' => 'sometimes|string',
            'package_size' => 'sometimes|string',
            'suggested_price' => 'sometimes|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $variant = $this->variantService->updateVariant($id, $validated);
        return new ProductVariantResource($variant);
    }

    public function destroy($id)
    {
        $this->variantService->deleteVariant($id);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}