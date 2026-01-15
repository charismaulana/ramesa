<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'room_code',
        'capacity',
        'is_fillable',
        'notes',
        'room_group_id',
        'location',
        'order',
    ];

    protected $casts = [
        'is_fillable' => 'boolean',
    ];

    public function roomGroup()
    {
        return $this->belongsTo(RoomGroup::class);
    }

    /**
     * Get the employee assigned to this room at this location.
     */
    public function getOccupant()
    {
        return Employee::where('active_status', 'active')
            ->whereNotNull('accommodation')
            ->get()
            ->first(function ($employee) {
                return ($employee->accommodation[$this->location] ?? null) === $this->room_code;
            });
    }
}
