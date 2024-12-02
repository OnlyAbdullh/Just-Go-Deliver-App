<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
class CheckBlacklistedToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        try {
            $token = JWTAuth::getToken();
            if (!$token) {
                return ApiResponse::errorResponse('Token not provided', [], 401);
            }
            $isBlacklisted = DB::table('token_blacklist')->where('token', $token)->exists();
            if ($isBlacklisted) {
                return ApiResponse::errorResponse('TOKEN_INVALID', [], 401);
            }
        } catch (\Exception $e) {
            return ApiResponse::errorResponse('TOKEN_INVALID', [], 401);
        }

        return $next($request);
    }
}
