<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealPrice extends Model
{
    protected $fillable = [
        'breakfast_price',
        'lunch_price',
        'dinner_price',
        'supper_price',
        'snack_price',
    ];

    protected $casts = [
        'breakfast_price' => 'decimal:2',
        'lunch_price' => 'decimal:2',
        'dinner_price' => 'decimal:2',
        'supper_price' => 'decimal:2',
        'snack_price' => 'decimal:2',
    ];

    /**
     * Get the current meal prices (singleton pattern - only one record)
     */
    public static function current()
    {
        return static::first() ?? static::create([
            'breakfast_price' => 0,
            'lunch_price' => 0,
            'dinner_price' => 0,
            'supper_price' => 0,
            'snack_price' => 0,
        ]);
    }
}
