<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProductVariantResource;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\Manufacturer;
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
            'form' => 'sometimes|string',
            'min_price' => 'sometimes|numeric|min:0',
            'max_price' => 'sometimes|numeric|gt:min_price',
            'pharmacy_id' => 'sometimes|exists:pharmacies,id',
            'is_available' => 'sometimes|boolean',
            'per_page' => 'sometimes|integer|min:1|max:50',
            'sort_by' => 'sometimes|in:price,name,relevance',
            'sort_direction' => 'sometimes|in:asc,desc',
            'distance' => 'sometimes|numeric|min:0',
            'latitude' => 'required_with:distance|numeric',
            'longitude' => 'required_with:distance|numeric',
        ]);

        $query = ProductVariant::search($validated['query']);

        // Apply filters using whereIn after getting the search results
        $query->query(function (Builder $builder) use ($validated) {
            $builder->with(['product.manufacturer', 'product.category', 'inventories.pharmacy']);

            // Category filter (including child categories)
            if (isset($validated['category_id'])) {
                $categoryIds = Category::where('id', $validated['category_id'])
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

            // Form filter
            if (isset($validated['form'])) {
                $builder->where('form', $validated['form']);
            }

            // Price range filter
            if (isset($validated['min_price'])) {
                $builder->where('suggested_price', '>=', $validated['min_price']);
            }
            if (isset($validated['max_price'])) {
                $builder->where('suggested_price', '<=', $validated['max_price']);
            }

            // Pharmacy and availability filters
            if (isset($validated['pharmacy_id']) || isset($validated['is_available'])) {
                $builder->whereHas('inventories', function ($query) use ($validated) {
                    if (isset($validated['pharmacy_id'])) {
                        $query->where('pharmacy_id', $validated['pharmacy_id']);
                    }
                    if (isset($validated['is_available'])) {
                        $query->where('is_available', $validated['is_available']);
                    }
                });
            }

            // Distance-based search
            if (isset($validated['distance'])) {
                $builder->whereHas('inventories.pharmacy', function ($query) use ($validated) {
                    $query->selectRaw('*, 
                        ST_Distance_Sphere(
                            point(longitude, latitude),
                            point(?, ?)
                        ) * .001 as distance', [
                            $validated['longitude'],
                            $validated['latitude']
                        ])
                        ->having('distance', '<=', $validated['distance']);
                });
            }

            // Sorting
            if (isset($validated['sort_by'])) {
                $direction = $validated['sort_direction'] ?? 'asc';
                if ($validated['sort_by'] === 'price') {
                    $builder->orderBy('suggested_price', $direction);
                } elseif ($validated['sort_by'] === 'name') {
                    $builder->orderBy('name', $direction);
                }
            }
        });

        $perPage = $validated['per_page'] ?? 15;
        $results = $query->paginate($perPage);

        return ProductVariantResource::collection($results)
            ->additional([
                'meta' => [
                    'filters' => array_filter([
                        'category_id' => $validated['category_id'] ?? null,
                        'manufacturer_id' => $validated['manufacturer_id'] ?? null,
                        'form' => $validated['form'] ?? null,
                        'price_range' => [
                            'min' => $validated['min_price'] ?? null,
                            'max' => $validated['max_price'] ?? null,
                        ],
                        'pharmacy_id' => $validated['pharmacy_id'] ?? null,
                        'is_available' => $validated['is_available'] ?? null,
                        'distance' => $validated['distance'] ?? null,
                        'location' => isset($validated['latitude']) ? [
                            'lat' => $validated['latitude'],
                            'lng' => $validated['longitude'],
                        ] : null,
                    ]),
                    'sort' => [
                        'by' => $validated['sort_by'] ?? 'relevance',
                        'direction' => $validated['sort_direction'] ?? 'asc',
                    ],
                ],
            ]);
    }

    public function getFacets()
    {
        $facets = [
            'forms' => ProductVariant::distinct()->pluck('form'),
            'price_range' => [
                'min' => ProductVariant::min('suggested_price'),
                'max' => ProductVariant::max('suggested_price'),
            ],
            'categories' => Category::select('id', 'name')
                ->withCount('products')
                ->having('products_count', '>', 0)
                ->get(),
            'manufacturers' => Manufacturer::select('id', 'name')
                ->withCount('products')
                ->having('products_count', '>', 0)
                ->get(),
        ];

        return response()->json(['data' => $facets]);
    }
}