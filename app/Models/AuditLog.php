<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id', 'hotel_id', 'action', 'subject_type', 'subject_id',
        'properties', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function subject(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo('subject');
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'hotel.approved'           => 'Hotel Approved',
            'hotel.suspended'          => 'Hotel Suspended',
            'hotel.featured'           => 'Hotel Featured',
            'hotel.deleted'            => 'Hotel Deleted',
            'booking.confirmed'        => 'Booking Confirmed',
            'booking.cancelled'        => 'Booking Cancelled',
            'booking.checked_in'       => 'Guest Checked In',
            'booking.checked_out'      => 'Guest Checked Out',
            'impersonation.started'    => 'Impersonation Started',
            'impersonation.stopped'    => 'Impersonation Stopped',
            'user.role_assigned'       => 'Role Assigned',
            'user.role_revoked'        => 'Role Revoked',
            'user.toggled_active'      => 'User Toggled Active',
            default                    => ucwords(str_replace(['.', '_'], ' ', $this->action)),
        };
    }

    public function getActionColorAttribute(): string
    {
        return match (true) {
            str_contains($this->action, 'approved')  ||
            str_contains($this->action, 'confirmed') ||
            str_contains($this->action, 'checked_in')  => 'emerald',
            str_contains($this->action, 'suspended') ||
            str_contains($this->action, 'cancelled') ||
            str_contains($this->action, 'deleted')     => 'rose',
            str_contains($this->action, 'impersonation') => 'amber',
            default                                      => 'slate',
        };
    }
}
