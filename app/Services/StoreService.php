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
            'user_id' => auth()->id(),
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

    public function updateStore(Store $store, array $data): Store|null
    {
        if (isset($data['logo'])) {
            if ((!empty($store->logo)) && Storage::disk('public')->exists($store->logo)) {
                Storage::disk('public')->delete($store->logo);
            }

            $imagePath = $this->storeRepository->uploadLogo($data['logo'], 'stores');

            $data['logo'] = $imagePath;
        }

        return $this->storeRepository->update($store, $data);
    }

    public function deleteLogoImage(Store $store)
    {
        if ((!empty($store->logo)) && Storage::disk('public')->exists($store->logo)) {
            Storage::disk('public')->delete($store->logo);
        }
    }

    public function deleteStore(Store $store)
    {
        $this->deleteLogoImage($store);
        $this->storeRepository->delete($store);
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
