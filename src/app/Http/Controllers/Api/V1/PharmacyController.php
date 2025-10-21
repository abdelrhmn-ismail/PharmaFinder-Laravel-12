<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PharmacyResource;
use App\Services\PharmacyService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PharmacyController extends Controller
{
    protected $pharmacyService;

    public function __construct(PharmacyService $pharmacyService)
    {
        $this->pharmacyService = $pharmacyService;
    }

    public function index()
    {
        $pharmacies = $this->pharmacyService->getAllPharmacies();
        return PharmacyResource::collection($pharmacies);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'postal_code' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'is_active' => 'boolean',
            'is_24_hours' => 'boolean',
        ]);

        $pharmacy = $this->pharmacyService->createPharmacy($validated);
        return new PharmacyResource($pharmacy);
    }

    public function show($id)
    {
        $pharmacy = $this->pharmacyService->getPharmacy($id);
        return new PharmacyResource($pharmacy);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'address' => 'sometimes|string',
            'city' => 'sometimes|string',
            'state' => 'sometimes|string',
            'postal_code' => 'sometimes|string',
            'phone' => 'sometimes|string',
            'email' => 'sometimes|email',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'is_active' => 'sometimes|boolean',
            'is_24_hours' => 'sometimes|boolean',
        ]);

        $pharmacy = $this->pharmacyService->updatePharmacy($id, $validated);
        return new PharmacyResource($pharmacy);
    }

    public function destroy($id)
    {
        $this->pharmacyService->deletePharmacy($id);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}