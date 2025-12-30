<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeGroup extends Model
{
    protected $fillable = ['name'];

    /**
     * Get the employees that belong to this group
     */
    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_group_members');
    }

    /**
     * Get array of employee IDs in this group
     */
    public function getMembersAttribute()
    {
        return $this->employees()->pluck('employees.id')->toArray();
    }
}
