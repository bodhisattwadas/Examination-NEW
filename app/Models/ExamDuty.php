<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamDuty extends Model
{
    use HasFactory;

    protected $table = 'exam_duties';

    protected $fillable = [
        'exam_name',
        'exam_date',
        'exam_end_date',
        'exam_time',
        'default_hours',
        'remarks',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'exam_end_date' => 'date',
        'default_hours' => 'decimal:2',
    ];

    /**
     * Get human-friendly date or date range string.
     */
    public function getFormattedDateRangeAttribute(): ?string
    {
        if (!$this->exam_date) {
            return null;
        }

        if (!$this->exam_end_date || $this->exam_date->isSameDay($this->exam_end_date)) {
            return $this->exam_date->format('d M Y');
        }

        return $this->exam_date->format('d M Y') . ' — ' . $this->exam_end_date->format('d M Y');
    }

    /**
     * Get string value formatted for Flatpickr range input.
     */
    public function getDateRangePickerValueAttribute(): ?string
    {
        if (!$this->exam_date) {
            return null;
        }

        if (!$this->exam_end_date || $this->exam_date->isSameDay($this->exam_end_date)) {
            return $this->exam_date->format('Y-m-d');
        }

        return $this->exam_date->format('Y-m-d') . ' to ' . $this->exam_end_date->format('Y-m-d');
    }

    /**
     * The staff members assigned to this exam duty.
     */
    public function staffs()
    {
        return $this->belongsToMany(Staff::class, 'exam_duty_staffs', 'exam_duty_id', 'staff_id')
            ->withPivot('is_duty', 'is_exam_secretary', 'is_oic', 'duty_hours')
            ->withTimestamps();
    }

    /**
     * The assignments recorded for this exam duty.
     */
    public function dutyAssignments()
    {
        return $this->hasMany(ExamDutyStaff::class, 'exam_duty_id');
    }
}
