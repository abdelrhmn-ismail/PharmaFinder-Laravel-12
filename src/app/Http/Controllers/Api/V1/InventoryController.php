<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessInventoryUpdate;
use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InventoryController extends Controller
{
    public function bulkUpdate(Request $request, Pharmacy $pharmacy)
    {
        $validated = $request->validate([
            'inventory' => 'required|array|min:1',
            'inventory.*.sku' => 'required|string|exists:product_variants,sku',
            'inventory.*.price' => 'required|numeric|min:0',
            'inventory.*.quantity' => 'required|integer|min:0',
        ]);

        // Dispatch the job to process inventory updates
        ProcessInventoryUpdate::dispatch($pharmacy->id, $validated['inventory']);

        return response()->json([
            'message' => 'Inventory update has been queued for processing',
            'pharmacy_id' => $pharmacy->id,
            'items_count' => count($validated['inventory']),
        ], Response::HTTP_ACCEPTED);
    }
}