<?php

namespace App\Http\Middleware;

use App\Helpers\JsonResponseHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            if (! $token) {
                return JsonResponseHelper::errorResponse('Token not provided', [], 401);
            }
            $isBlacklisted = DB::table('token_blacklist')->where('token', $token)->exists();
            if ($isBlacklisted) {
                return JsonResponseHelper::errorResponse('TOKEN_INVALID', [], 401);
            }
        } catch (\Exception $e) {
            return JsonResponseHelper::errorResponse('TOKEN_INVALID', [], 401);
        }

        return $next($request);
    }
}
