<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CancellationApproval extends Model
{
    protected $fillable = [
        'hotel_id', 'booking_id', 'requested_by', 'approved_by',
        'status', 'reason', 'denial_reason',
        'total_paid', 'deduction_percentage', 'refund_percentage',
        'deduction_amount', 'refund_amount',
        'approved_at', 'executed_at',
    ];

    protected $casts = [
        'total_paid'           => 'decimal:2',
        'deduction_percentage' => 'decimal:2',
        'refund_percentage'    => 'decimal:2',
        'deduction_amount'     => 'decimal:2',
        'refund_amount'        => 'decimal:2',
        'approved_at'          => 'datetime',
        'executed_at'          => 'datetime',
    ];

    const STATUS_PENDING  = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_DENIED   = 'denied';
    const STATUS_EXECUTED = 'executed';

    // ── Relationships ─────────────────────────────────────────────────────────

    public function hotel()      { return $this->belongsTo(Hotel::class); }
    public function booking()    { return $this->belongsTo(Booking::class); }
    public function requestedBy(){ return $this->belongsTo(User::class, 'requested_by'); }
    public function approvedBy() { return $this->belongsTo(User::class, 'approved_by'); }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePending($q)  { return $q->where('status', self::STATUS_PENDING); }
    public function scopeApproved($q) { return $q->where('status', self::STATUS_APPROVED); }
    public function scopeForHotel($q, int $hotelId) { return $q->where('hotel_id', $hotelId); }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isPending()  { return $this->status === self::STATUS_PENDING; }
    public function isApproved() { return $this->status === self::STATUS_APPROVED; }
    public function isDenied()   { return $this->status === self::STATUS_DENIED; }
    public function isExecuted() { return $this->status === self::STATUS_EXECUTED; }

    public function statusColor(): string
    {
        return match($this->status) {
            self::STATUS_PENDING  => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
            self::STATUS_APPROVED => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
            self::STATUS_DENIED   => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
            self::STATUS_EXECUTED => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
            default               => '',
        };
    }
}
