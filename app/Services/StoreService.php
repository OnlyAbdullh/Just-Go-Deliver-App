<?php

namespace App\Services;

use App\Http\Resources\StoreResource;
use App\Models\Store;
use App\Repositories\Contracts\StoreRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class StoreService
{
    private $storeRepository;

    public function __construct(StoreRepositoryInterface $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    public function getAllStores($items): array|null
    {
        $stores = $this->storeRepository->all_with_pagination($items);

        if (!$stores) {
            return null;
        }

        $hasMorePages = $stores->hasMorePages();

        return [
            'stores' => StoreResource::collection($stores),
            'hasMorePages' => $hasMorePages
        ];
    }

    public function createStore(array $data): Store
    {
        $imagePath = $this->storeRepository->uploadLogo($data['logo'], 'stores');

        $storeData = [
            'user_id' => $data['user_id'],
            'name' => $data['name'],
            'logo' => $imagePath
        ];

        if (!empty($data['location'])) {
            $storeData['location'] = $data['location'];
        }
        if (!empty($data['description'])) {
            $storeData['description'] = $data['description'];
        }

        return $this->storeRepository->store($storeData);
    }

    public function updateStore(int $storeId, array $data): Store|null
    {

        $store = $this->storeRepository->findById($storeId);

        if (!$store) {
            return null;
        }

        if (isset($data['logo'])) {
            if ((!empty($store->logo)) && Storage::disk('public')->exists($store->logo)) {
                Storage::disk('public')->delete($store->logo);
            }

            $imagePath = $this->storeRepository->uploadLogo($data['logo'], 'stores');

            $data['logo'] = $imagePath;
        }

        return $this->storeRepository->update($store, $data);
    }

    public function deleteStore(int $storeId): bool|null
    {
        $store = $this->storeRepository->findById($storeId);

        if (!$store) {
            return null;
        }
        return $this->storeRepository->delete($store);
    }

    public function showStore($storeId)
    {
        $store = $this->storeRepository->findById($storeId);

        if (!$store) {
            return null;
        }

        return $store;
    }
}
