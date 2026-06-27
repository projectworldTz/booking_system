<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Feature;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use App\Repositories\BookingRepository;
use App\Services\BookingService;
use Illuminate\Http\Request;

class HotelDashboardController extends Controller
{
    public function __construct(
        private BookingService    $bookingService,
        private BookingRepository $bookingRepository,
    ) {}

    // ── Overview / hotel management hub ──────────────────────────────────────

    public function show(Hotel $hotel)
    {
        $hotel->loadMissing([
            'owner', 'images', 'amenities',
            'roomTypes.images', 'roomTypes.rooms',
            'category', 'staff.user',
        ]);

        $stats   = $this->bookingRepository->hotelStats($hotel);
        $revenue = $this->bookingRepository->revenueByMonthForHotel($hotel, 12);

        $stats['revenue_month'] = Booking::forHotel($hotel->id)
            ->whereIn('status', [
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_CHECKED_IN,
                Booking::STATUS_CHECKED_OUT,
            ])
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('grand_total');

        $stats['total_rooms'] = Room::whereIn(
            'room_type_id',
            $hotel->roomTypes->pluck('id')
        )->count();

        $recentBookings = $this->bookingRepository->recentForHotel($hotel, 8);

        return view('admin.hotels.show', compact(
            'hotel', 'stats', 'revenue', 'recentBookings'
        ))->with('activeTab', 'overview');
    }

    // ── Hotel-scoped bookings ─────────────────────────────────────────────────

    public function bookings(Hotel $hotel, Request $request)
    {
        $hotel->loadMissing(['owner']);

        $filters  = $request->only(['status', 'date_from', 'date_to', 'search']);
        $bookings = $this->bookingRepository->forHotel($hotel, $filters, 20);

        return view('admin.hotels.show', compact('hotel', 'bookings', 'filters'))
            ->with('activeTab', 'bookings');
    }

    // ── Hotel-scoped revenue report ───────────────────────────────────────────

    public function revenue(Hotel $hotel, Request $request)
    {
        $hotel->loadMissing(['owner']);

        $months  = (int) $request->input('months', 12);
        $revenue = $this->bookingRepository->revenueByMonthForHotel($hotel, $months);
        $stats   = $this->bookingRepository->hotelStats($hotel);

        return view('admin.hotels.show', compact('hotel', 'revenue', 'stats', 'months'))
            ->with('activeTab', 'revenue');
    }

    // ── Hotel rooms ───────────────────────────────────────────────────────────

    public function rooms(Hotel $hotel)
    {
        $hotel->loadMissing(['owner', 'roomTypes.rooms', 'roomTypes.images']);

        return view('admin.hotels.show', compact('hotel'))
            ->with('activeTab', 'rooms');
    }

    // ── Hotel staff ───────────────────────────────────────────────────────────

    public function staff(Hotel $hotel)
    {
        $hotel->loadMissing(['owner', 'staff.user']);

        return view('admin.hotels.show', compact('hotel'))
            ->with('activeTab', 'staff');
    }

    // ── Hotel guests ──────────────────────────────────────────────────────────

    public function guests(Hotel $hotel, Request $request)
    {
        $hotel->loadMissing(['owner']);

        $search = $request->input('search');

        $guests = \App\Models\User::whereHas('bookings', fn ($q) => $q->where('hotel_id', $hotel->id))
            ->withCount(['bookings as hotel_bookings_count' => fn ($q) => $q->where('hotel_id', $hotel->id)])
            ->withSum(['bookings as hotel_spend' => fn ($q) => $q->where('hotel_id', $hotel->id)
                ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_CHECKED_IN, Booking::STATUS_CHECKED_OUT])
            ], 'grand_total')
            ->when($search, fn ($q) => $q->where(fn ($q2) =>
                $q2->where('name', 'like', "%{$search}%")
                   ->orWhere('email', 'like', "%{$search}%")
            ))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.hotels.show', compact('hotel', 'guests', 'search'))
            ->with('activeTab', 'guests');
    }

    // ── Premium features management ───────────────────────────────────────────

    public function features(Hotel $hotel)
    {
        $hotel->loadMissing(['owner', 'hotelFeatures.grantedBy']);

        $allFeatures    = Feature::grouped();
        $grantedByValue = $hotel->hotelFeatures->keyBy(fn ($f) => $f->feature->value);

        return view('admin.hotels.show', compact('hotel', 'allFeatures', 'grantedByValue'))
            ->with('activeTab', 'features');
    }
}
