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
     * Stored value may be a root-relative path (/storage/...) or a full URL.
     */
    public function getUrlAttribute(): string
    {
        $stored = $this->attributes['url'] ?? '';

        if (str_starts_with($stored, 'http')) {
            return $stored;
        }

        return asset($stored);
    }
}
