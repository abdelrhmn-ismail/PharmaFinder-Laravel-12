<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProductVariantResource;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|min:2',
            'category_id' => 'sometimes|exists:categories,id',
            'manufacturer_id' => 'sometimes|exists:manufacturers,id',
            'min_price' => 'sometimes|numeric|min:0',
            'max_price' => 'sometimes|numeric|gt:min_price',
            'is_available' => 'sometimes|boolean',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $query = ProductVariant::search($validated['query']);

        // Apply filters using whereIn after getting the search results
        $query->query(function (Builder $builder) use ($validated) {
            // Category filter (including child categories)
            if (isset($validated['category_id'])) {
                $categoryIds = \App\Models\Category::where('id', $validated['category_id'])
                    ->orWhere('parent_id', $validated['category_id'])
                    ->pluck('id');
                    
                $builder->whereHas('product', function ($query) use ($categoryIds) {
                    $query->whereIn('category_id', $categoryIds);
                });
            }

            // Manufacturer filter
            if (isset($validated['manufacturer_id'])) {
                $builder->whereHas('product', function ($query) use ($validated) {
                    $query->where('manufacturer_id', $validated['manufacturer_id']);
                });
            }

            // Price range filter
            if (isset($validated['min_price'])) {
                $builder->where('suggested_price', '>=', $validated['min_price']);
            }
            if (isset($validated['max_price'])) {
                $builder->where('suggested_price', '<=', $validated['max_price']);
            }

            // Availability filter
            if (isset($validated['is_available'])) {
                $builder->whereHas('inventory', function ($query) use ($validated) {
                    $query->where('is_available', $validated['is_available']);
                });
            }
        });

        $perPage = $validated['per_page'] ?? 15;
        $results = $query->paginate($perPage);

        return ProductVariantResource::collection($results);
    }
}