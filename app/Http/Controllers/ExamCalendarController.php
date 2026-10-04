<?php

namespace App\Http\Controllers;

use App\Models\ExamDuty;
use Illuminate\Http\Request;

class ExamCalendarController extends Controller
{
    /**
     * Show a dedicated exam calendar page listing all duty dates and assigned staff.
     */
    public function index()
    {
        $examCalendar = ExamDuty::with(['dutyAssignments.staff'])
            ->orderBy('exam_date', 'asc')
            ->orderBy('exam_time')
            ->get()
            ->map(function ($duty) {
                $assigned = $duty->dutyAssignments
                    ->map(function ($assignment) {
                        $staff = $assignment->staff;

                        $roles = [];
                        if ($assignment->is_duty) {
                            $roles[] = 'Duty';
                        }
                        if ($assignment->is_exam_secretary) {
                            $roles[] = 'Secretary';
                        }
                        if ($assignment->is_oic) {
                            $roles[] = 'OIC';
                        }

                        return [
                            'staff_name' => $staff ? $staff->name : 'Unknown',
                            'staff_code' => $staff ? $staff->staff_code : '',
                            'roles' => $roles,
                        ];
                    })
                    ->values();

                return [
                    'id' => $duty->id,
                    'exam_name' => $duty->exam_name,
                    'exam_date' => $duty->exam_date,
                    'exam_time' => $duty->exam_time,
                    'default_hours' => $duty->default_hours,
                    'remarks' => $duty->remarks,
                    'assigned_staff' => $assigned,
                ];
            });

        return view('exam-calendar.index', compact('examCalendar'));
    }
}
