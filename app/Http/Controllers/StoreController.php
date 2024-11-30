<?php

namespace App\Http\Controllers;

use App\Helper\JsonResponseHelper;
use App\Http\Requests\CreateStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Http\Resources\StoreResource;
use App\Models\Store;
use App\Services\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    private $storeSrevice;

    public function __construct(StoreService $storeService)
    {
        $this->storeSrevice = $storeService;
    }

    public function index(Request $request): JsonResponse
    {

        $page = $request->query('page', 1);
        $itemsPerPage = $request->query('items', 10);

        $data = $this->storeSrevice->getAllStores($page, $itemsPerPage);

        $message = "stores fetched successfully";

        if (!$data) {
            $message = "there is no stores available";
        }


        return JsonResponseHelper::successResponse($message, $data);
    }

    public function store(CreateStoreRequest $request): JsonResponse
    {
        $store = $this->storeSrevice->createStore($request->validated());

        return JsonResponseHelper::successResponse('store created successfully', StoreResource::make($store), 201);
    }

    public function update(UpdateStoreRequest $request, Store $store): JsonResponse
    {

        $store = $this->storeSrevice->updateStore($store->id, $request->validated());

        return JsonResponseHelper::successResponse('store updated successfully', $store, 200);
    }

    public function show(Store $store): JsonResponse
    {
        return JsonResponseHelper::successResponse('store displayed successfully', StoreResource::make($store), 200);
    }

    public function destroy(Store $store): JsonResponse
    {
        $store = $this->storeSrevice->deleteStore($store);
        return JsonResponseHelper::successResponse('store deleted successfully', $store, 200);
    }
    
}
