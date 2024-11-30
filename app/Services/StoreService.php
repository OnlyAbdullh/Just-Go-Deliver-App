<?php

namespace App\Services;

use App\Http\Resources\StoreResource;
use App\Models\Store;
use App\Repositories\StoreRepository;
use Illuminate\Support\Facades\Storage;

class StoreService
{
    private $storeRepository;

    public function __construct(StoreRepository $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    public function getAllStores($page, $items): array
    {
        $stores = $this->storeRepository->all_with_pagination($page, $items);

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
            'description' => $data['description'],
            'name' => $data['name'],
            'logo' => $imagePath
        ];

        if (!empty($data['location'])) {
            $storeData['location'] = $data['location'];
        }

        return $this->storeRepository->store($storeData);
    }

    public function updateStore(int $storeId, array $data)
    {

        $store = $this->storeRepository->findById($storeId);

        if(isset($data['logo'])){
            if((!empty($store->logo)) && Storage::disk('public')->exists($store->logo)){
                Storage::disk('public')->delete($store->logo);
            }

            $imagePath = $this->storeRepository->uploadLogo($data['logo'], 'stores');

            $data['logo'] = $imagePath;
        }

        return $this->storeRepository->update($store, $data);
    }

    public function deleteStore(Store $store): bool
    {
        return $this->storeRepository->delete($store);
    }


}
