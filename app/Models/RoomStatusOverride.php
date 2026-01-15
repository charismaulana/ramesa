<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomStatusOverride extends Model
{
    protected $fillable = [
        'employee_id',
        'location',
        'date',
        'status',
        'user_id',
        'reason',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
