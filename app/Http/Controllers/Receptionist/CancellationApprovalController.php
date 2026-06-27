<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CancellationApproval;
use Illuminate\Http\Request;

class CancellationApprovalController extends Controller
{
    public function index(Request $request)
    {
        $hotel = $request->attributes->get('assigned_hotel');

        $approvals = CancellationApproval::forHotel($hotel->id)
            ->with(['booking.user', 'requestedBy', 'approvedBy'])
            ->latest()
            ->paginate(20);

        return view('receptionist.cancellation-approvals.index', compact('hotel', 'approvals'));
    }

    public function request(Request $request, Booking $booking)
    {
        $hotel = $request->attributes->get('assigned_hotel');

        abort_if($booking->hotel_id !== $hotel->id, 403);
        abort_unless(\in_array($booking->status, ['confirmed', 'checked_in']), 422,
            'Only confirmed or checked-in bookings can be emergency-cancelled.'
        );
        abort_if($booking->cancellationApproval()->exists(), 422,
            'A cancellation request already exists for this booking.'
        );

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:20', 'max:1000'],
        ]);

        $totalPaid = (float) ($booking->grand_total ?? 0);

        CancellationApproval::create([
            'hotel_id'             => $hotel->id,
            'booking_id'           => $booking->id,
            'requested_by'         => auth()->id(),
            'status'               => CancellationApproval::STATUS_PENDING,
            'reason'               => $data['reason'],
            'total_paid'           => $totalPaid,
            'deduction_percentage' => 60,
            'refund_percentage'    => 40,
            'deduction_amount'     => round($totalPaid * 0.60, 2),
            'refund_amount'        => round($totalPaid * 0.40, 2),
        ]);

        return back()->with('success', 'Emergency cancellation request submitted. Awaiting owner approval.');
    }

    public function execute(Request $request, CancellationApproval $approval)
    {
        $hotel = $request->attributes->get('assigned_hotel');

        abort_if($approval->hotel_id !== $hotel->id, 403);
        abort_unless($approval->isApproved(), 403, 'This request has not been approved yet.');

        $booking = $approval->booking;

        abort_unless(\in_array($booking->status, ['confirmed', 'checked_in']), 422,
            'Booking is no longer cancellable.'
        );

        $booking->update([
            'status'             => 'cancelled',
            'cancellation_reason'=> 'Emergency cancellation (approved by owner). ' . $approval->reason,
            'cancelled_at'       => now(),
        ]);

        // Update the invoice to reflect the cancellation and partial refund
        if ($booking->invoice) {
            $booking->invoice->update([
                'status'                 => 'cancelled',
                'cancelled_at'           => now(),
                'cancellation_deduction' => $approval->deduction_amount,
                'refund_amount'          => $approval->refund_amount,
                'deduction_percentage'   => $approval->deduction_percentage,
                'notes'                  => 'Emergency cancellation — 60% deduction applied. Refund due: ' .
                                            number_format((float) $approval->refund_amount, 2) . ' ' . $booking->currency . '.',
            ]);
        }

        $approval->update([
            'status'      => CancellationApproval::STATUS_EXECUTED,
            'executed_at' => now(),
        ]);

        return back()->with('success',
            "Booking #{$booking->booking_number} cancelled. " .
            'Refund: TZS ' . number_format((float) $approval->refund_amount, 0) . ' (40%). ' .
            'Deducted: TZS ' . number_format((float) $approval->deduction_amount, 0) . ' (60%).'
        );
    }
}
