<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\CancellationApproval;
use App\Models\Hotel;
use Illuminate\Http\Request;

class CancellationApprovalController extends Controller
{
    public function index(Hotel $hotel)
    {
        abort_unless($hotel->owner_id === auth()->id() || auth()->user()->isSuperAdmin(), 403);

        $approvals = CancellationApproval::forHotel($hotel->id)
            ->with(['booking.user', 'requestedBy', 'approvedBy'])
            ->latest()
            ->paginate(20);

        $pendingCount = CancellationApproval::forHotel($hotel->id)->pending()->count();

        return view('owner.cancellation-approvals.index', compact('hotel', 'approvals', 'pendingCount'));
    }

    public function approve(Hotel $hotel, CancellationApproval $approval)
    {
        $this->authorizeApproval($approval);
        abort_unless($approval->isPending(), 422, 'This request is no longer pending.');

        $approval->update([
            'status'      => CancellationApproval::STATUS_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success',
            'Cancellation approved. Receptionist can now execute it. ' .
            'Refund: TZS ' . number_format((float) $approval->refund_amount, 0) . ' (40%).'
        );
    }

    public function deny(Request $request, Hotel $hotel, CancellationApproval $approval)
    {
        $this->authorizeApproval($approval);
        abort_unless($approval->isPending(), 422, 'This request is no longer pending.');

        $data = $request->validate([
            'denial_reason' => ['required', 'string', 'max:500'],
        ]);

        $approval->update([
            'status'        => CancellationApproval::STATUS_DENIED,
            'approved_by'   => auth()->id(),
            'denial_reason' => $data['denial_reason'],
        ]);

        return back()->with('success', 'Cancellation request denied.');
    }

    private function authorizeApproval(CancellationApproval $approval): void
    {
        abort_unless($approval->hotel->owner_id === auth()->id() || auth()->user()->isSuperAdmin(), 403);
    }
}
