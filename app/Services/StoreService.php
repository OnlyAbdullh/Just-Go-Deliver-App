<?php

namespace App\Services;

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

    public function getAllStores($items)
    {
        return $this->storeRepository->all_with_pagination($items);
    }

    public function createStore(array $data): Store
    {
        $imagePath = $this->storeRepository->uploadLogo($data['logo'], 'stores');

        $storeData = [
            'user_id' => auth()->id(),
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'],
            'logo' => $imagePath,
        ];

        if (! empty($data['location_ar'])) {
            $storeData['location_ar'] = $data['location_ar'];
        }

        if (! empty($data['location_en'])) {
            $storeData['location_en'] = $data['location_en'];
        }

        if (! empty($data['description_ar'])) {
            $storeData['description_ar'] = $data['description_ar'];
        }

        if (! empty($data['description_en'])) {
            $storeData['description_en'] = $data['description_en'];
        }

        return $this->storeRepository->store($storeData);
    }

    public function updateStore(Store $store, array $data): ?Store
    {
        if (isset($data['logo'])) {
            if ((! empty($store->logo)) && Storage::disk('public')->exists($store->logo)) {
                Storage::disk('public')->delete($store->logo);
            }

            $imagePath = $this->storeRepository->uploadLogo($data['logo'], 'stores');

            $data['logo'] = $imagePath;
        }

        return $this->storeRepository->update($store, $data);
    }

    public function deleteLogoImage(Store $store)
    {
        if ((! empty($store->logo)) && Storage::disk('public')->exists($store->logo)) {
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

        if (! $store) {
            return null;
        }

        return $store;
    }
}
