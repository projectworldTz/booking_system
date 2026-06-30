<?php

namespace App\Listeners;

use App\Events\BookingCancelled;
use App\Mail\BookingCancelledMail;
use App\Mail\BookingCancelledOwnerMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendBookingCancellationEmail implements ShouldQueue
{
    public function handle(BookingCancelled $event): void
    {
        $booking = $event->booking;
        $booking->loadMissing(['user', 'hotel.owner', 'payment']);

        // Notify the guest
        if ($booking->user?->email) {
            Mail::to($booking->user->email)
                ->send(new BookingCancelledMail($booking));
        }

        // Notify the hotel owner only when a manual refund must be processed
        if ($booking->refund_amount > 0) {
            $ownerEmail = $booking->hotel->owner?->email;
            if ($ownerEmail) {
                Mail::to($ownerEmail)
                    ->send(new BookingCancelledOwnerMail($booking));
            }
        }
    }

    public function failed(BookingCancelled $event, \Throwable $exception): void
    {
        \Illuminate\Support\Facades\Log::error(
            'Failed to send booking cancellation email',
            ['booking' => $event->booking->booking_number, 'error' => $exception->getMessage()]
        );
    }
}
