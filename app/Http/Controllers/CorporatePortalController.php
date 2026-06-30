<?php

namespace App\Http\Controllers;

use App\Models\CorporateAccount;
use App\Models\Hotel;
use App\Models\RoomType;
use Illuminate\View\View;

class CorporatePortalController extends Controller
{
    public function show(Hotel $hotel, string $code): View
    {
        $corporate = CorporateAccount::where('hotel_id', $hotel->id)
            ->where('access_code', $code)
            ->where('is_active', true)
            ->firstOrFail();

        abort_unless($corporate->isContractActive(), 410, 'This corporate portal link has expired.');

        $roomTypes = $hotel->roomTypes()
            ->where('status', 'active')
            ->with('images')
            ->get()
            ->map(function (RoomType $rt) use ($corporate) {
                $rt->corporate_price = $corporate->applyDiscount((float) $rt->base_price);
                return $rt;
            });

        return view('corporate.portal', compact('hotel', 'corporate', 'roomTypes'));
    }
}
