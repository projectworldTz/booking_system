<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomImage extends Model
{
    use HasFactory;

    protected $fillable = ['room_type_id', 'path', 'url', 'caption', 'sort_order', 'is_featured'];

    protected $casts = [
        'is_featured' => 'boolean',
        'sort_order'  => 'integer',
    ];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Always return a fully-qualified URL regardless of what is stored.
     * Priority: unsplash reconstruction → stored url column → empty string.
     *
     * Doesn't rebuild from `path` via the local `/storage/...` convention,
     * since uploads may be served from S3 or another disk in production —
     * the `url` column (set at upload time from the active disk) is canonical.
     */
    public function getUrlAttribute(): string
    {
        $stored = $this->attributes['url'] ?? '';
        $path   = $this->attributes['path'] ?? '';

        if (str_starts_with($path, 'unsplash/')) {
            return 'https://images.unsplash.com/photo-' . substr($path, strlen('unsplash/'));
        }

        if (str_starts_with($stored, 'http')) {
            return $stored;
        }

        return $stored ? asset($stored) : '';
    }
}
