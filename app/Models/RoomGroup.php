<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomGroup extends Model
{
    protected $fillable = [
        'name',
        'location',
        'order',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class)->orderBy('order');
    }
}
