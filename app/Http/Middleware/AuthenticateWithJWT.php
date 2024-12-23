<?php

namespace App\Http\Middleware;

use App\Helpers\JsonResponseHelper;
use Closure;
use Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthenticateWithJWT
{
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (! $user) {
                return JsonResponseHelper::errorResponse('USER_NOT_FOUND', [], 404);
            }
        } catch (TokenExpiredException $e) {
            return JsonResponseHelper::errorResponse('TOKEN_EXPIRED', [], 401);
        } catch (TokenInvalidException $e) {
            return JsonResponseHelper::errorResponse('TOKEN_INVALID', [], 401);
        } catch (JWTException $e) {
            return JsonResponseHelper::errorResponse('TOKEN_NOT_PROVIDED', [], 401);
        }

        // Proceed with the request if no exception is thrown
        return $next($request);
    }
}
