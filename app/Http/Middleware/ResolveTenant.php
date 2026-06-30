<?php

namespace App\Http\Middleware;

use App\Models\Hotel;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $host     = $request->getHost();          // e.g. tranquiloo.localhost
        $segments = explode('.', $host);

        // A host with 2+ dot-separated segments that isn't an IP has a subdomain.
        // We skip 'www' so www.yourdomain.com stays in platform mode.
        $isSubdomain = count($segments) >= 2
            && ! filter_var($host, FILTER_VALIDATE_IP)
            && $segments[0] !== 'www';

        $hotel = null;

        if ($isSubdomain) {
            $slug  = $segments[0];
            $hotel = Hotel::where('slug', $slug)->where('status', 'active')->first();
        }

        if ($hotel) {
            app()->instance('current_hotel', $hotel);
        }

        View::share('tenantMode',   (bool) $hotel);
        View::share('currentHotel', $hotel);

        return $next($request);
    }
}
