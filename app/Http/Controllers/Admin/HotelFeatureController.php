<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Feature;
use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelFeature;
use App\Services\AuditService;
use Illuminate\Http\Request;

class HotelFeatureController extends Controller
{
    public function __construct(private AuditService $auditService) {}

    public function grant(Hotel $hotel, Request $request)
    {
        $data = $request->validate([
            'feature'    => ['required', 'string', 'in:' . implode(',', array_column(Feature::cases(), 'value'))],
            'expires_at' => ['nullable', 'date', 'after:today'],
            'notes'      => ['nullable', 'string', 'max:500'],
        ]);

        $hotel->grantFeature(
            $data['feature'],
            auth()->id(),
            $data['expires_at'] ?? null,
            $data['notes'] ?? null,
        );

        $featureLabel = Feature::from($data['feature'])->label();

        $this->auditService->logHotelAction('feature.granted', $hotel, [
            'feature'    => $data['feature'],
            'label'      => $featureLabel,
            'expires_at' => $data['expires_at'] ?? 'permanent',
        ]);

        return back()->with('success', "\"{$featureLabel}\" has been granted to {$hotel->name}.");
    }

    public function revoke(Hotel $hotel, Request $request)
    {
        $data = $request->validate([
            'feature' => ['required', 'string', 'in:' . implode(',', array_column(Feature::cases(), 'value'))],
        ]);

        $hotel->revokeFeature($data['feature']);

        $featureLabel = Feature::from($data['feature'])->label();

        $this->auditService->logHotelAction('feature.revoked', $hotel, [
            'feature' => $data['feature'],
            'label'   => $featureLabel,
        ]);

        return back()->with('success', "\"{$featureLabel}\" has been revoked from {$hotel->name}.");
    }
}
