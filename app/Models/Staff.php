<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staffs';

    protected $fillable = [
        'staff_code',
        'name',
        'staff_type',
        'department',
        'mobile',
        'email',
        'status',
    ];

    /**
     * Scope a query to only include active staff.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * The exam duties that belong to the staff member.
     */
    public function examDuties()
    {
        return $table = $this->belongsToMany(ExamDuty::class, 'exam_duty_staffs', 'staff_id', 'exam_duty_id')
            ->withPivot('is_duty', 'is_exam_secretary', 'duty_hours')
            ->withTimestamps();
    }

    /**
     * The assignments recorded for the staff member.
     */
    public function dutyAssignments()
    {
        return $this->hasMany(ExamDutyStaff::class, 'staff_id');
    }
}
