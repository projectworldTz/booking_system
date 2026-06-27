<?php

namespace App\Models;

use App\Enums\Feature;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureRequest extends Model
{
    protected $fillable = [
        'hotel_id', 'requested_by', 'feature', 'status',
        'message', 'admin_notes', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'feature'     => Feature::class,
        'reviewed_at' => 'datetime',
    ];

    // ── Relations ─────────────────────────────────────────────────────────────

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePending(Builder $q): Builder
    {
        return $q->where('status', 'pending');
    }

    public function scopeForHotel(Builder $q, int $hotelId): Builder
    {
        return $q->where('hotel_id', $hotelId);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isDenied(): bool   { return $this->status === 'denied'; }
}
