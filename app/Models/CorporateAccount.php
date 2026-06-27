<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CorporateAccount extends Model
{
    protected $fillable = [
        'hotel_id', 'company_name', 'contact_name', 'contact_email', 'contact_phone',
        'access_code', 'discount_type', 'discount_value', 'credit_limit',
        'billing_terms', 'notes', 'contract_start', 'contract_end',
        'is_active', 'created_by',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'credit_limit'   => 'decimal:2',
        'contract_start' => 'date',
        'contract_end'   => 'date',
        'is_active'      => 'boolean',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeForHotel(Builder $q, int $hotelId): Builder
    {
        return $q->where('hotel_id', $hotelId);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public static function generateCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (static::where('access_code', $code)->exists());

        return $code;
    }

    /** Apply discount to a base price and return the discounted price. */
    public function applyDiscount(float $basePrice): float
    {
        if ($this->discount_type === 'percentage') {
            return max(0, $basePrice * (1 - $this->discount_value / 100));
        }
        return max(0, $basePrice - $this->discount_value);
    }

    /** Formatted discount label for display. */
    public function discountLabel(): string
    {
        if ($this->discount_type === 'percentage') {
            return number_format($this->discount_value, 0) . '% off';
        }
        return 'TZS ' . number_format($this->discount_value, 0) . ' off / night';
    }

    public function totalSpend(): float
    {
        return (float) $this->bookings()->whereNotIn('status', ['cancelled'])->sum('grand_total');
    }

    public function isContractActive(): bool
    {
        if (! $this->contract_start && ! $this->contract_end) {
            return true;
        }
        $now = now()->toDateString();
        if ($this->contract_start && $this->contract_start->toDateString() > $now) return false;
        if ($this->contract_end  && $this->contract_end->toDateString()  < $now) return false;
        return true;
    }
}
