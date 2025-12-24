<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'employee_id',
        'meal_type',
        'scan_method',
        'recorded_by',
        'scanned_at',
        'location',
        'edited_by',
        'deleted_by',
        'edited_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'edited_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeFilterByDate($query, $startDate = null, $endDate = null)
    {
        if ($startDate) {
            $query->whereDate('scanned_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('scanned_at', '<=', $endDate);
        }
        return $query;
    }

    public function scopeFilterByMealType($query, $mealType = null)
    {
        if ($mealType) {
            $query->where('meal_type', $mealType);
        }
        return $query;
    }

    public function scopeFilterByEmployee($query, $employeeId = null)
    {
        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }
        return $query;
    }
}
