<?php

namespace App\Http\Controllers\Owner;

use App\Enums\Feature;
use App\Http\Controllers\Controller;
use App\Models\FeatureRequest;
use App\Models\Hotel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class FeatureRequestController extends Controller
{
    public function index(Request $request, Hotel $hotel): View
    {
        $this->authorizeHotel($hotel);

        // All feature requests ever made for this hotel, keyed by feature value
        $requests = FeatureRequest::forHotel($hotel->id)
            ->latest()
            ->get()
            ->keyBy(fn ($r) => $r->feature->value);

        // Granted features
        $granted = $hotel->hotelFeatures()
            ->with('grantedBy')
            ->get()
            ->keyBy(fn ($hf) => $hf->feature->value);

        $grouped = Feature::grouped();

        return view('owner.features.index', compact('hotel', 'grouped', 'requests', 'granted'));
    }

    public function store(Request $request, Hotel $hotel): RedirectResponse
    {
        $this->authorizeHotel($hotel);

        $data = $request->validate([
            'feature' => ['required', 'string'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        // Validate the feature enum value
        $feature = Feature::tryFrom($data['feature']);
        if (! $feature) {
            return back()->with('error', 'Invalid feature selected.');
        }

        // Already granted
        if ($hotel->hasFeature($feature)) {
            return back()->with('info', "{$feature->label()} is already active for your hotel.");
        }

        // Already has a pending request
        $existing = FeatureRequest::forHotel($hotel->id)
            ->where('feature', $feature->value)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return back()->with('info', 'You already have a pending request for this feature.');
        }

        FeatureRequest::create([
            'hotel_id'     => $hotel->id,
            'requested_by' => auth()->id(),
            'feature'      => $feature->value,
            'status'       => 'pending',
            'message'      => $data['message'] ?? null,
        ]);

        return back()->with('success', "Access request for \"{$feature->label()}\" sent to the platform admin. You will be notified once it is reviewed.");
    }

    private function authorizeHotel(Hotel $hotel): void
    {
        abort_unless(
            auth()->user()->isSuperAdmin() || $hotel->isOwnedBy(auth()->user()),
            403
        );
    }
}
