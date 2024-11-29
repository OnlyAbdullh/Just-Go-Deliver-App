<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
class AuthenticateWithJWT
{
    public function handle($request, Closure $next)
    {
        try {
            // Attempt to authenticate the user using the token
            JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'Token expired', 'code' => 'TOKEN_EXPIRED'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['message' => 'Invalid token', 'code' => 'TOKEN_INVALID'], 401);
        } catch (Exception $e) {
            return response()->json(['message' => 'Token not provided', 'code' => 'TOKEN_NOT_PROVIDED'], 401);
        }

        // Proceed with the request if no exception is thrown
        return $next($request);
    }

}
