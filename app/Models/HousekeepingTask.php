<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HousekeepingTask extends Model
{
    const STATUS_PENDING     = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED   = 'completed';
    const STATUS_INSPECTED   = 'inspected';

    const TYPE_CHECKOUT  = 'checkout_cleaning';
    const TYPE_ROUTINE   = 'routine_cleaning';
    const TYPE_DEEP      = 'deep_clean';
    const TYPE_TURNDOWN  = 'turndown';

    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH   = 'high';
    const PRIORITY_URGENT = 'urgent';

    protected $fillable = [
        'hotel_id', 'room_id', 'booking_id', 'assigned_to', 'inspected_by',
        'type', 'status', 'priority', 'notes', 'inspector_notes',
        'started_at', 'completed_at', 'inspected_at',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
        'inspected_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function hotel(): BelongsTo   { return $this->belongsTo(Hotel::class); }
    public function room(): BelongsTo    { return $this->belongsTo(Room::class); }
    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function inspectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForHotel($query, int $hotelId)
    {
        return $query->where('hotel_id', $hotelId);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeCompletedToday($query)
    {
        return $query->where('status', self::STATUS_COMPLETED)
                     ->whereDate('completed_at', today());
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_CHECKOUT => 'Checkout Cleaning',
            self::TYPE_ROUTINE  => 'Routine Cleaning',
            self::TYPE_DEEP     => 'Deep Clean',
            self::TYPE_TURNDOWN => 'Turndown',
            default             => ucfirst($this->type),
        };
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING     => 'amber',
            self::STATUS_IN_PROGRESS => 'blue',
            self::STATUS_COMPLETED   => 'emerald',
            self::STATUS_INSPECTED   => 'purple',
            default                  => 'slate',
        };
    }

    public function getPriorityColor(): string
    {
        return match ($this->priority) {
            self::PRIORITY_URGENT => 'rose',
            self::PRIORITY_HIGH   => 'amber',
            default               => 'slate',
        };
    }

    // ── Status transitions ────────────────────────────────────────────────────

    public function markInProgress(User $user): void
    {
        $this->update([
            'status'      => self::STATUS_IN_PROGRESS,
            'assigned_to' => $user->id,
            'started_at'  => now(),
        ]);
    }

    public function markCompleted(?string $notes = null): void
    {
        $this->update([
            'status'       => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'notes'        => $notes ?? $this->notes,
        ]);
    }

    public function markInspected(User $inspector, ?string $notes = null): void
    {
        $this->update([
            'status'          => self::STATUS_INSPECTED,
            'inspected_by'    => $inspector->id,
            'inspected_at'    => now(),
            'inspector_notes' => $notes,
        ]);
    }
}
