<?php

namespace App\Repositories;

use App\Models\Pharmacy;

class PharmacyRepository extends BaseRepository
{
    public function __construct(Pharmacy $model)
    {
        parent::__construct($model);
    }

    public function getWithRelations()
    {
        return $this->model->paginate();
    }

    public function findWithRelations(int $id)
    {
        return $this->model->with(['inventory.productVariant'])->findOrFail($id);
    }

    public function updateInventory(int $pharmacyId, array $inventoryData)
    {
        $pharmacy = $this->find($pharmacyId);
        return $pharmacy->inventory()->createMany($inventoryData);
    }

    public function findCheapestPharmaciesForVariant(int $variantId, ?int $minQuantity = null, int $limit = 5)
    {
        $query = $this->model
            ->join('inventories', 'pharmacies.id', '=', 'inventories.pharmacy_id')
            ->where('inventories.product_variant_id', $variantId)
            ->where('inventories.is_available', true);

        if ($minQuantity !== null) {
            $query->where('inventories.quantity', '>=', $minQuantity);
        }

        return $query->orderBy('inventories.price', 'asc')
            ->select('pharmacies.*', 'inventories.price', 'inventories.quantity')
            ->limit($limit)
            ->get();
    }
}