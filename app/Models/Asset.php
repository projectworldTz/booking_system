<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    const CONDITION_EXCELLENT = 'excellent';
    const CONDITION_GOOD      = 'good';
    const CONDITION_FAIR      = 'fair';
    const CONDITION_POOR      = 'poor';
    const CONDITION_DAMAGED   = 'damaged';

    const STATUS_ACTIVE      = 'active';
    const STATUS_MAINTENANCE = 'under_maintenance';
    const STATUS_DISPOSED    = 'disposed';

    protected $fillable = [
        'hotel_id', 'asset_category_id',
        'name', 'asset_code', 'description', 'location',
        'quantity', 'condition', 'status',
        'purchase_date', 'purchase_price', 'current_value',
        'warranty_expires_at', 'last_serviced_at', 'notes',
    ];

    protected $casts = [
        'purchase_date'      => 'date',
        'warranty_expires_at'=> 'date',
        'last_serviced_at'   => 'date',
        'purchase_price'     => 'decimal:2',
        'current_value'      => 'decimal:2',
        'quantity'           => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function hotel(): BelongsTo    { return $this->belongsTo(Hotel::class); }
    public function category(): BelongsTo { return $this->belongsTo(AssetCategory::class, 'asset_category_id'); }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForHotel($query, int $hotelId) { return $query->where('hotel_id', $hotelId); }
    public function scopeActive($query)     { return $query->where('status', self::STATUS_ACTIVE); }
    public function scopeDamaged($query)    { return $query->where('condition', self::CONDITION_DAMAGED); }
    public function scopeMaintenance($query){ return $query->where('status', self::STATUS_MAINTENANCE); }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getConditionColorAttribute(): string
    {
        return match ($this->condition) {
            self::CONDITION_EXCELLENT => 'emerald',
            self::CONDITION_GOOD      => 'blue',
            self::CONDITION_FAIR      => 'amber',
            self::CONDITION_POOR      => 'orange',
            self::CONDITION_DAMAGED   => 'rose',
            default                   => 'slate',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE      => 'emerald',
            self::STATUS_MAINTENANCE => 'amber',
            self::STATUS_DISPOSED    => 'slate',
            default                  => 'slate',
        };
    }

    public function getWarrantyStatusAttribute(): string
    {
        if (! $this->warranty_expires_at) return 'none';
        if ($this->warranty_expires_at->isPast()) return 'expired';
        if ($this->warranty_expires_at->diffInDays(now()) <= 30) return 'expiring';
        return 'valid';
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public static function generateCode(int $hotelId, string $categoryName): string
    {
        $prefix  = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $categoryName), 0, 4));
        $lastSeq = static::forHotel($hotelId)
            ->where('asset_code', 'like', $prefix . '-%')
            ->lockForUpdate()
            ->max('asset_code');

        $next = $lastSeq ? (int) substr($lastSeq, strlen($prefix) + 1) + 1 : 1;

        return $prefix . '-' . str_pad($next, 3, '0', STR_PAD_LEFT);
    }
}
