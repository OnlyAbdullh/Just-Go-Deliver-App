<?php

namespace App\Services;

use App\Http\Resources\StoreResource;
use App\Models\Store;
use App\Repositories\StoreRepository;

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
        $imagePath = $this->storeRepository->uploadLogo($data['logo'], 'stores', $data['name']);

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

}
