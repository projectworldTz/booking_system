<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeatureRequest;
use App\Services\HotelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeatureRequestController extends Controller
{
    public function __construct(private HotelService $hotelService) {}

    public function index(Request $request): View
    {
        $status = $request->input('status', 'pending');

        $requests = FeatureRequest::with(['hotel.owner', 'requestedBy', 'reviewedBy'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20);

        $pendingCount = FeatureRequest::pending()->count();

        return view('admin.feature-requests.index', compact('requests', 'status', 'pendingCount'));
    }

    public function approve(Request $request, FeatureRequest $featureRequest): RedirectResponse
    {
        abort_if(! $featureRequest->isPending(), 422, 'This request has already been reviewed.');

        $data = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Grant the feature
        $featureRequest->hotel->grantFeature($featureRequest->feature, auth()->id());

        $featureRequest->update([
            'status'      => 'approved',
            'admin_notes' => $data['admin_notes'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', "\"{$featureRequest->feature->label()}\" has been granted to {$featureRequest->hotel->name}.");
    }

    public function deny(Request $request, FeatureRequest $featureRequest): RedirectResponse
    {
        abort_if(! $featureRequest->isPending(), 422, 'This request has already been reviewed.');

        $data = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $featureRequest->update([
            'status'      => 'denied',
            'admin_notes' => $data['admin_notes'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', "Request denied. {$featureRequest->hotel->name} has been notified.");
    }
}
