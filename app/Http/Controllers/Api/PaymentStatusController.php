<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PaymentStatusController extends Controller
{
    public function show(Payment $payment): JsonResponse
    {
        abort_unless($payment->booking->user_id === Auth::id(), 403);

        return response()->json([
            'payment_id'     => $payment->id,
            'status'         => $payment->status,
            'booking_status' => $payment->booking->status,
            'confirmed'      => $payment->status === 'paid',
            'booking_number' => $payment->booking->booking_number,
        ]);
    }
}
