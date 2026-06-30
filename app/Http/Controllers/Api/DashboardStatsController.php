<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardStatsController extends Controller
{
    public function receptionist(Request $request): JsonResponse
    {
        /** @var Hotel|null $hotel */
        $hotel = $request->attributes->get('assigned_hotel');

        if (!$hotel) {
            return response()->json(['error' => 'No assigned hotel'], 403);
        }

        $today = now()->toDateString();

        return response()->json([
            'arrivals_today'   => Booking::where('hotel_id', $hotel->id)
                ->where('check_in', $today)
                ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_CHECKED_IN])
                ->count(),
            'departures_today' => Booking::where('hotel_id', $hotel->id)
                ->where('check_out', $today)
                ->where('status', Booking::STATUS_CHECKED_IN)
                ->count(),
            'pending_confirm'  => Booking::where('hotel_id', $hotel->id)
                ->where('status', Booking::STATUS_PENDING)
                ->count(),
            'currently_in'     => Booking::where('hotel_id', $hotel->id)
                ->where('status', Booking::STATUS_CHECKED_IN)
                ->count(),
        ]);
    }

    public function owner(): JsonResponse
    {
        $user  = Auth::user();
        $today = now()->toDateString();

        $hotelIds = $user->hotels()->pluck('id');

        return response()->json([
            'active_bookings' => Booking::whereIn('hotel_id', $hotelIds)
                ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_CHECKED_IN])
                ->count(),
            'pending_bookings' => Booking::whereIn('hotel_id', $hotelIds)
                ->where('status', Booking::STATUS_PENDING)
                ->count(),
        ]);
    }
}
