<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Services\AuditService;
use App\Services\HotelService;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    public function __construct(
        private HotelService  $hotelService,
        private AuditService  $auditService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'search', 'city', 'star_rating']);
        $hotels  = $this->hotelService->allForAdmin($filters);

        return view('admin.hotels.index', compact('hotels', 'filters'));
    }

    public function approve(Hotel $hotel)
    {
        $oldStatus = $hotel->status;
        $this->hotelService->approve($hotel);

        $this->auditService->logHotelAction('hotel.approved', $hotel, [
            'from_status' => $oldStatus,
            'to_status'   => 'active',
        ]);

        return back()->with('success', "\"{$hotel->name}\" has been approved and is now live.");
    }

    public function suspend(Hotel $hotel, Request $request)
    {
        $reason = $request->input('reason', '');
        $this->hotelService->suspend($hotel, $reason);

        $this->auditService->logHotelAction('hotel.suspended', $hotel, [
            'reason' => $reason,
        ]);

        return back()->with('success', "\"{$hotel->name}\" has been suspended.");
    }

    public function toggleFeatured(Hotel $hotel)
    {
        $this->hotelService->toggleFeatured($hotel);
        $featured = $hotel->fresh()->featured;

        $this->auditService->logHotelAction('hotel.featured', $hotel, [
            'featured' => $featured,
        ]);

        return back()->with('success', "\"{$hotel->name}\" is now " . ($featured ? 'featured' : 'unfeatured') . '.');
    }

    public function destroy(Hotel $hotel)
    {
        $name = $hotel->name;
        $this->auditService->logHotelAction('hotel.deleted', $hotel);
        $this->hotelService->delete($hotel);

        return redirect()->route('admin.hotels.index')
            ->with('success', "Hotel \"{$name}\" deleted.");
    }
}
