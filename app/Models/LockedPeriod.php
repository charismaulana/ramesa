<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LockedPeriod extends Model
{
    protected $fillable = [
        'start_date',
        'end_date',
        'location',
        'locked_by',
        'reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the user who locked this period.
     */
    public function lockedByUser()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    /**
     * Check if a specific date is locked for a given location.
     * 
     * @param string|Carbon $date The date to check
     * @param string|null $location The location to check (optional)
     * @return bool
     */
    public static function isDateLocked($date, $location = null): bool
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);

        $query = self::where('start_date', '<=', $date->toDateString())
            ->where('end_date', '>=', $date->toDateString());

        // Check for global locks (location is null) or location-specific locks
        if ($location) {
            $query->where(function ($q) use ($location) {
                $q->whereNull('location')
                    ->orWhere('location', $location);
            });
        }

        return $query->exists();
    }

    /**
     * Get the locked period that blocks a specific date.
     * 
     * @param string|Carbon $date The date to check
     * @param string|null $location The location to check (optional)
     * @return LockedPeriod|null
     */
    public static function getLockedPeriod($date, $location = null): ?LockedPeriod
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);

        $query = self::where('start_date', '<=', $date->toDateString())
            ->where('end_date', '>=', $date->toDateString());

        if ($location) {
            $query->where(function ($q) use ($location) {
                $q->whereNull('location')
                    ->orWhere('location', $location);
            });
        }

        return $query->first();
    }
}
