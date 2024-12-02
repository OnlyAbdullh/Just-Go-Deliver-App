<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
/**
 * @OA\Info(
 *     title="My API Documentation",
 *     version="1.0.0",
 *     description="API documentation for Just-Go-Deliver-App"
 * )
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local development server"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
