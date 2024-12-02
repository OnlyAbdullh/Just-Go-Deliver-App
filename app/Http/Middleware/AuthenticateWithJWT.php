<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class AuthenticateWithJWT
{
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return ApiResponse::errorResponse('USER_NOT_FOUND', [], 404);
            }
        } catch (TokenExpiredException $e) {
            return ApiResponse::errorResponse('TOKEN_EXPIRED', [], 401);
        } catch (TokenInvalidException $e) {
            return ApiResponse::errorResponse('TOKEN_INVALID', [], 401);
        } catch (JWTException $e) {
            return ApiResponse::errorResponse('TOKEN_NOT_PROVIDED', [], 401);
        }

        // Proceed with the request if no exception is thrown
        return $next($request);
    }

}
