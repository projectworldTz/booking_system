<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditService
{
    public function __construct(private ?Request $request = null) {}

    public function log(
        string  $action,
        ?Model  $subject  = null,
        array   $properties = [],
        ?int    $hotelId  = null,
        ?int    $userId   = null,
    ): AuditLog {
        $actorId = $userId ?? auth()->id();

        // Resolve hotel_id from subject when not explicitly passed
        if ($hotelId === null && $subject !== null && isset($subject->hotel_id)) {
            $hotelId = $subject->hotel_id;
        }

        return AuditLog::create([
            'user_id'      => $actorId,
            'hotel_id'     => $hotelId,
            'action'       => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject?->getKey(),
            'properties'   => $properties ?: null,
            'ip_address'   => $this->request?->ip(),
            'user_agent'   => $this->request?->userAgent(),
        ]);
    }

    public function logHotelAction(string $action, Model $hotel, array $extra = []): AuditLog
    {
        return $this->log($action, $hotel, $extra, $hotel->getKey());
    }

    public function logBookingAction(string $action, Model $booking, array $extra = []): AuditLog
    {
        return $this->log($action, $booking, $extra, $booking->hotel_id ?? null);
    }
}
