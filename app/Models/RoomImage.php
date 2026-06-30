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
     * Priority: url column (if set) → reconstruct from path → empty string.
     */
    public function getUrlAttribute(): string
    {
        $stored = $this->attributes['url'] ?? '';
        $path   = $this->attributes['path'] ?? '';

        // Unsplash images — reconstruct from path first (path is canonical)
        if (str_starts_with($path, 'unsplash/')) {
            return 'https://images.unsplash.com/photo-' . substr($path, strlen('unsplash/'));
        }

        // Local uploaded file — always regenerate from path so the URL is
        // correct regardless of what APP_URL was set to at upload time.
        if ($path && !str_starts_with($path, 'http')) {
            return asset('storage/' . ltrim($path, '/'));
        }

        // External URL stored directly in url column
        if (str_starts_with($stored, 'http')) {
            return $stored;
        }

        return '';
    }
}
