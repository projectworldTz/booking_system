<?php

namespace App\Services;

use App\Events\BookingCancelled;
use App\Models\Booking;
use App\Models\Transaction;
use App\Repositories\AvailabilityRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CancellationService
{
    // Platform-level cancellation tiers (hours before check-in midnight)
    const FREE_HOURS    = 48;  // >= 48 h  → 100 % refund
    const PARTIAL_HOURS = 24;  // >= 24 h  →  50 % refund
    const PARTIAL_PCT   = 50;  // < 24 h  →   0 % refund

    public function __construct(
        private AvailabilityRepository $availabilityRepository,
    ) {}

    /** Human-readable policy description shown to guests. */
    public function policyDescription(): string
    {
        return implode(' ', [
            'Free cancellation 48+ hours before check-in (100% refund).',
            '50% refund if cancelled 24–48 hours before check-in.',
            'No refund if cancelled less than 24 hours before check-in.',
        ]);
    }

    /** Structured policy snapshot saved on the Booking when it is created. */
    public function policySnapshot(): array
    {
        return [
            'free_hours'      => self::FREE_HOURS,
            'partial_hours'   => self::PARTIAL_HOURS,
            'partial_percent' => self::PARTIAL_PCT,
            'description'     => $this->policyDescription(),
        ];
    }

    /**
     * Compute the refund amount for a cancellation request.
     * Returns 0.0 when no payment exists or payment was never confirmed.
     */
    public function computeRefund(Booking $booking): float
    {
        // Only confirmed (paid) payments are eligible for refund
        if (! $booking->payment || $booking->payment->status !== 'paid') {
            return 0.0;
        }

        // Hours remaining until check-in (negative = already passed)
        $hoursUntilCheckIn = now()->diffInHours(
            Carbon::parse($booking->check_in)->startOfDay(),
            false
        );

        if ($hoursUntilCheckIn >= self::FREE_HOURS) {
            return (float) $booking->grand_total; // 100 %
        }

        if ($hoursUntilCheckIn >= self::PARTIAL_HOURS) {
            return round((float) $booking->grand_total * self::PARTIAL_PCT / 100, 2); // 50 %
        }

        return 0.0; // no refund
    }

    /**
     * Which refund tier applies right now (for display).
     * Returns 'full' | 'partial' | 'none' | 'prepayment' (payment not confirmed).
     */
    public function refundTier(Booking $booking): string
    {
        if (! $booking->payment || $booking->payment->status !== 'paid') {
            return 'prepayment';
        }

        $hours = now()->diffInHours(Carbon::parse($booking->check_in)->startOfDay(), false);

        if ($hours >= self::FREE_HOURS) return 'full';
        if ($hours >= self::PARTIAL_HOURS) return 'partial';
        return 'none';
    }

    /**
     * Cancel the booking: release rooms, record refund, fire event.
     *
     * @throws \RuntimeException when the booking cannot be cancelled.
     */
    public function cancel(Booking $booking, string $reason = ''): Booking
    {
        if (! $booking->is_cancellable) {
            throw new \RuntimeException(
                "Booking #{$booking->booking_number} cannot be cancelled in its current status."
            );
        }

        $refundAmount = $this->computeRefund($booking);

        DB::transaction(function () use ($booking, $reason, $refundAmount) {

            // 1. Mark booking cancelled
            $booking->update([
                'status'              => Booking::STATUS_CANCELLED,
                'cancellation_reason' => $reason,
                'cancelled_at'        => now(),
            ]);

            // 2. Release blocked room-availability slots
            $this->availabilityRepository->releaseAllForBooking($booking);

            // 3. Apply refund to the payment record
            if ($booking->payment && $refundAmount > 0) {
                $booking->payment->update([
                    'status'        => 'refunded',
                    'refund_amount' => $refundAmount,
                ]);

                // Audit trail — status 'pending' means awaiting manual mobile-money transfer
                Transaction::create([
                    'booking_id'             => $booking->id,
                    'payment_id'             => $booking->payment->id,
                    'type'                   => 'refund',
                    'amount'                 => $refundAmount,
                    'currency'               => $booking->currency,
                    'status'                 => 'pending',
                    'gateway'                => $booking->payment->method,
                    'gateway_transaction_id' => null,
                    'gateway_response'       => [
                        'note' => 'Manual refund required — transfer via ' . $booking->payment->method_label,
                    ],
                ]);
            }
        });

        $booking->refresh();
        event(new BookingCancelled($booking));

        return $booking;
    }
}
