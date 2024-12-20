<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiLocalization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('Accept-Language')) {
            $lang = $request->header('Accept-Language');

            if (in_array($lang, ['ar', 'en'])) {
                app()->setLocale($lang);
            } else {
                app()->setLocale('en');
            }
            
        }
        return $next($request);
    }
}
