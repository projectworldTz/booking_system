<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleImpersonation
{
    public function handle(Request $request, Closure $next): Response
    {
        // Make impersonation state available to all views
        if (session()->has('impersonating_original_id')) {
            view()->share('isImpersonating', true);
            view()->share('impersonatedUser', auth()->user());
        } else {
            view()->share('isImpersonating', false);
            view()->share('impersonatedUser', null);
        }

        return $next($request);
    }
}
