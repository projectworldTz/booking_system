<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHotelOwnerHasHotel
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($user?->isHotelOwner() && $user->ownedHotels()->doesntExist()) {
            // Allow the creation routes through so the owner can complete setup
            if ($request->routeIs('owner.hotels.create', 'owner.hotels.store')) {
                return $next($request);
            }

            return redirect()->route('owner.hotels.create')
                ->with('info', 'Please set up your hotel profile before continuing.');
        }

        return $next($request);
    }
}
