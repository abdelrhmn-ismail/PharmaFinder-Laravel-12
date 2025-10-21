<?php

namespace App\Services;

use App\Jobs\ProcessInventoryUpdate;
use App\Repositories\PharmacyRepository;
use Illuminate\Support\Str;

class PharmacyService
{
    protected $pharmacyRepository;

    public function __construct(PharmacyRepository $pharmacyRepository)
    {
        $this->pharmacyRepository = $pharmacyRepository;
    }

    public function getAllPharmacies()
    {
        return $this->pharmacyRepository->getWithRelations();
    }

    public function getPharmacy(int $id)
    {
        return $this->pharmacyRepository->findWithRelations($id);
    }

    public function createPharmacy(array $data)
    {
        $data['slug'] = Str::slug($data['name']);
        return $this->pharmacyRepository->create($data);
    }

    public function updatePharmacy(int $id, array $data)
    {
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        return $this->pharmacyRepository->update($id, $data);
    }

    public function deletePharmacy(int $id)
    {
        return $this->pharmacyRepository->delete($id);
    }

    public function bulkUpdateInventory(int $pharmacyId, array $inventoryData)
    {
        // Dispatch job for async processing
        ProcessInventoryUpdate::dispatch($pharmacyId, $inventoryData);
    }

    public function findCheapestPharmacies(int $variantId, ?int $minQuantity = null, int $limit = 5)
    {
        return $this->pharmacyRepository->findCheapestPharmaciesForVariant($variantId, $minQuantity, $limit);
    }
}