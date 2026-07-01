<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelVisit extends Model
{
    use HasFactory;

    protected $fillable = ['hotel_id', 'visitor_key', 'visited_on'];

    protected $casts = [
        'visited_on' => 'date',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
