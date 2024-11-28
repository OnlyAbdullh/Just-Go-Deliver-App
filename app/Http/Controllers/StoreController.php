<?php

namespace App\Http\Controllers;

use App\Helper\JsonResponseHelper;
use App\Http\Requests\StoreRequest;
use App\Http\Resources\StoreResource;
use App\Services\StoreService;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    private $storeSrevice;

    public function __construct(StoreService $storeService)
    {
        $this->storeSrevice = $storeService;
    }

    public function index(Request $request)
    {

        $page = $request->query('page', 1);
        $itemsPerPage = $request->query('items', 10);

        $data = $this->storeSrevice->getAllStores($page, $itemsPerPage);

        return JsonResponseHelper::successResponse('success', $data);
    }

    public function store(StoreRequest $request)
    {
        $store = $this->storeSrevice->createStore($request->validated());

        return JsonResponseHelper::successResponse('store created successfully', StoreResource::make($store), 201);
    }

}
