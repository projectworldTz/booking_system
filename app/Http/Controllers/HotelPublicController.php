<?php

namespace App\Http\Controllers;

use App\Models\Hotel;

class HotelPublicController extends Controller
{
    public function show(Hotel $hotel)
    {
        abort_if($hotel->status !== 'active', 404);

        $hotel->loadMissing([
            'images',
            'amenities',
            'category',
            'roomTypes.images',
            'roomTypes.amenities',
            'approvedReviews.user',
        ]);

        return view('hotels.public', compact('hotel'));
    }
}
