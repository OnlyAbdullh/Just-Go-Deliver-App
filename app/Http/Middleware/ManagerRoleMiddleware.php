<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class ManagerRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $urlSegments = explode('/', $request->getPathInfo());
        $operation = end($urlSegments);

        if ($operation == 'assign-role') {
            if (!Gate::allows('assignRole', User::class)) {
                return response()->json(['message' => 'Only manager can assign roles', 'status_code' => 403], 403);
            }
        } else {
            if (!Gate::allows('revokeRole', User::class)) {
                return response()->json(['message' => 'Only manager can revoke roles', 'status_code' => 403], 403);
            }
        }

        return $next($request);
    }
}
