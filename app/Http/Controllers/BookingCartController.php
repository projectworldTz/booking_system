<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Models\Coupon;
use App\Models\ReservationCartItem;
use App\Models\RoomType;
use App\Models\User;
use App\Services\BookingService;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingCartController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
        private PricingService $pricingService,
    ) {}

    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $cart = $this->bookingService->getCart($user);

        return view('booking.cart', compact('cart'));
    }

    /**
     * Add a room type to the reservation cart.
     * Accepts JSON (React) or form POST.
     */
    public function store(AddToCartRequest $request)
    {
        /** @var User $user */
        $user     = Auth::user();
        $roomType = RoomType::findOrFail($request->room_type_id);

        try {
            $this->bookingService->addToCart(
                $user,
                $roomType,
                $request->check_in,
                $request->check_out,
                (int) $request->guests
            );
        } catch (\RuntimeException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['availability' => $e->getMessage()]);
        }

        if ($request->expectsJson()) {
            $cart = $this->bookingService->getCart($user);
            return response()->json([
                'success'    => true,
                'message'    => 'Room added to your reservation.',
                'cart_count' => $cart->item_count,
                'subtotal'   => $cart->sub_total,
            ]);
        }

        return redirect()->route('booking.cart')->with('success', 'Room added to your reservation.');
    }

    public function destroy(ReservationCartItem $item)
    {
        abort_unless($item->cart->user_id === Auth::id(), 403);

        $this->bookingService->removeFromCart($item);

        if (request()->expectsJson()) {
            /** @var User $user */
            $user = Auth::user();
            $cart = $this->bookingService->getCart($user);
            return response()->json([
                'success'  => true,
                'subtotal' => $cart->sub_total,
                'count'    => $cart->item_count,
            ]);
        }

        return back()->with('success', 'Item removed from cart.');
    }

    /**
     * Preview coupon discount against current cart (AJAX).
     */
    public function coupon(Request $request)
    {
        $request->validate(['code' => 'required|string|max:50']);

        /** @var User $user */
        $user    = Auth::user();
        $cart    = $this->bookingService->getCart($user);
        $hotelId = $cart->items->first()?->room?->hotel_id;

        $result = $this->bookingService->applyCouponPreview($user, $request->input('code'), $hotelId);

        return response()->json($result);
    }

    /**
     * Preview order total (tax + optional coupon). Used by checkout page.
     */
    public function preview(Request $request)
    {
        /** @var User $user */
        $user   = Auth::user();
        $cart   = $this->bookingService->getCart($user);
        $coupon = null;

        if ($request->filled('coupon_code')) {
            $coupon = Coupon::where('code', strtoupper($request->input('coupon_code')))->valid()->first();
        }

        $totals = $this->pricingService->calculateOrderTotal((float) $cart->sub_total, $coupon);

        return response()->json($totals);
    }
}
