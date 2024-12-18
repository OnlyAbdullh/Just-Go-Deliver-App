<?php

namespace App\Http\Middleware;

use App\Helpers\JsonResponseHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class GuestOrAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if ($token) {
            try {
                $user = JWTAuth::parseToken()->authenticate();

                if (!$user) {
                    return JsonResponseHelper::errorResponse('USER_NOT_FOUND', [], 404);
                }
            } catch (TokenExpiredException $e) {
                return JsonResponseHelper::errorResponse('TOKEN_EXPIRED', [], 401);
            } catch (TokenInvalidException $e) {
                return JsonResponseHelper::errorResponse('TOKEN_INVALID', [], 401);
            } catch (JWTException $e) {
                return JsonResponseHelper::errorResponse('TOKEN_NOT_PROVIDED', [], 401);
            }
        }

        return $next($request);
    }
}
