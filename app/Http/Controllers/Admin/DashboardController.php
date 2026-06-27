<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Role;
use App\Models\User;
use App\Services\BookingService;
use App\Services\HotelService;

class DashboardController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
        private HotelService   $hotelService,
    ) {}

    public function index()
    {
        // ── Platform-level counts ─────────────────────────────────────────────
        $hotelStats = $this->hotelService->stats();

        $platformStats = [
            'hotels'        => $hotelStats,
            'total_owners'  => User::whereHas('roles', fn ($q) => $q->where('name', 'hotel-owner'))->count(),
            'total_users'   => User::count(),
            'total_bookings' => Booking::count(),
            'total_revenue' => Booking::whereIn('status', [
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_CHECKED_IN,
                Booking::STATUS_CHECKED_OUT,
            ])->sum('grand_total'),
            'revenue_month' => Booking::whereIn('status', [
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_CHECKED_IN,
                Booking::STATUS_CHECKED_OUT,
            ])->whereYear('created_at', now()->year)
              ->whereMonth('created_at', now()->month)
              ->sum('grand_total'),
        ];

        // ── Revenue trend (platform-wide) ─────────────────────────────────────
        $revenue = $this->bookingService->revenueByMonth(12);

        // ── Recent hotel registrations ─────────────────────────────────────────
        $recentHotels = Hotel::with('owner')
            ->latest()
            ->take(8)
            ->get();

        // ── Pending approvals ──────────────────────────────────────────────────
        $pendingHotels = Hotel::where('status', 'pending')
            ->with('owner')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'platformStats', 'revenue', 'recentHotels', 'pendingHotels'
        ));
    }
}
