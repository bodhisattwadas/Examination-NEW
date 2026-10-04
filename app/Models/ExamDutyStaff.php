<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamDutyStaff extends Model
{
    use HasFactory;

    protected $table = 'exam_duty_staffs';

    protected $fillable = [
        'exam_duty_id',
        'staff_id',
        'is_duty',
        'is_exam_secretary',
        'is_oic',
        'oic_count',
        'duty_hours',
    ];

    protected $casts = [
        'is_duty' => 'boolean',
        'is_exam_secretary' => 'boolean',
        'is_oic' => 'boolean',
        'oic_count' => 'integer',
        'duty_hours' => 'decimal:2',
    ];

    /**
     * Get the staff member associated with this assignment.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    /**
     * Get the exam duty associated with this assignment.
     */
    public function examDuty()
    {
        return $this->belongsTo(ExamDuty::class, 'exam_duty_id');
    }
}
