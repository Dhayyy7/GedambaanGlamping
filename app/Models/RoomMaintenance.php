<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomMaintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'start_date',
        'end_date',
        'note',
    ];

    /**
     * Get the room that owns the maintenance schedule.
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
