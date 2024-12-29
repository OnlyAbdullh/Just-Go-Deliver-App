<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;


use App\Services\OrderService;
use Illuminate\Http\Request;
use App\Helpers\JsonResponseHelper;

class OrderController extends Controller
{
    private $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function createOrders(Request $request)
    {
        $result = $this->orderService->createOrders($request->input('data'));

        return JsonResponseHelper::successResponse('Orders created successfully.', $result);
    }
}

