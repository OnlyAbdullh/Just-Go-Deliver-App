<?php

namespace App\Http\Middleware;

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
                return response()->json(['status' => false, 'message' => 'Token not provided'], 401);
            }

            // Check if the token is blacklisted
            $isBlacklisted = DB::table('token_blacklist')->where('token', $token)->exists();
            if ($isBlacklisted) {
                return response()->json(['status' => false, 'message' => 'Token is invalid'], 401);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Invalid token'], 401);
        }

        return $next($request);
    }
}
