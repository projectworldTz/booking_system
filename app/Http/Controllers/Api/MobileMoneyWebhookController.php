<?php

namespace App\Http\Controllers\Api;

use App\Events\BookingCreated;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\BookingService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MobileMoneyWebhookController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private BookingService $bookingService,
    ) {}

    /**
     * Handle a callback from a mobile money provider.
     * Provider is one of: airtel_money, mpesa, halotel, mix_by_yas
     */
    public function handle(Request $request, string $provider): \Illuminate\Http\JsonResponse
    {
        Log::info("Mobile money webhook received from [{$provider}]", $request->all());

        // Providers send the booking reference in different fields — normalise here
        $reference     = $request->input('reference')
            ?? $request->input('AccountReference')  // M-Pesa
            ?? $request->input('ReferenceID');       // Mix by Yas

        $transactionId = $request->input('transaction_id')
            ?? $request->input('MpesaReceiptNumber')  // M-Pesa
            ?? $request->input('TransID')
            ?? $request->input('id');

        if (! $reference) {
            Log::warning("Mobile money webhook [{$provider}]: missing reference", $request->all());
            return response()->json(['status' => 'missing_reference'], 400);
        }

        $payment = Payment::where('method', $provider)
            ->where('status', 'pending')
            ->whereJsonContains('metadata->reference', $reference)
            ->first();

        if (! $payment) {
            Log::warning("Mobile money webhook [{$provider}]: no matching payment for reference [{$reference}]");
            return response()->json(['status' => 'not_found'], 404);
        }

        try {
            $this->paymentService->verify($payment, [
                'transaction_id' => $transactionId,
                'provider_data'  => $request->all(),
            ]);

            $booking = $payment->booking;
            $this->bookingService->confirm($booking);
            $booking->refresh();
            event(new BookingCreated($booking));

            Log::info("Booking [{$booking->booking_number}] confirmed via [{$provider}] payment.");

            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            Log::error("Mobile money webhook [{$provider}] error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
