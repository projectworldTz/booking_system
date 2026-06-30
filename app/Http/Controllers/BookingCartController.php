<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Models\CorporateAccount;
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

        // Resolve corporate account if a code is submitted
        $corporate = null;
        if ($request->filled('corporate_code')) {
            $corporate = CorporateAccount::where('access_code', $request->corporate_code)
                ->where('hotel_id', $roomType->hotel_id)
                ->where('is_active', true)
                ->first();
            if ($corporate && $corporate->isContractActive()) {
                session(['corporate_account_id' => $corporate->id]);
            }
        }

        try {
            $this->bookingService->addToCart(
                $user,
                $roomType,
                $request->check_in,
                $request->check_out,
                (int) $request->guests,
                $corporate
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
     * Preview order total (tax). Used by checkout page.
     */
    public function preview(Request $request)
    {
        /** @var User $user */
        $user   = Auth::user();
        $cart   = $this->bookingService->getCart($user);
        $totals = $this->pricingService->calculateOrderTotal((float) $cart->sub_total);

        return response()->json($totals);
    }
}
