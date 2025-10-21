<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkInventoryUpdateRequest;
use App\Jobs\ProcessBulkInventoryUpdate;
use Illuminate\Http\Response;

class InventoryManagementController extends Controller
{
    public function bulkUpdate(BulkInventoryUpdateRequest $request)
    {
        ProcessBulkInventoryUpdate::dispatch($request->validated());
        
        return response()->json(['message' => 'Inventory update queued for processing'], Response::HTTP_ACCEPTED);
    }
}