<?php

use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\InventoryController;
use App\Http\Controllers\Api\V1\PharmacyController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductVariantController;
use App\Http\Controllers\Api\V1\SearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Products
    Route::apiResource('products', ProductController::class);
    
    // Product Variants
    Route::apiResource('product-variants', ProductVariantController::class);
    
    // Categories
    Route::apiResource('categories', CategoryController::class);
    
    // Pharmacies
    Route::apiResource('pharmacies', PharmacyController::class);
    
    // Inventory Management
    Route::post('pharmacies/{pharmacy}/inventory/bulk-update', [InventoryController::class, 'bulkUpdate'])
        ->name('pharmacies.inventory.bulk-update');
    
    // Search
    Route::get('search', [SearchController::class, 'search'])->name('search');
    Route::get('search/facets', [SearchController::class, 'getFacets'])->name('search.facets');
});