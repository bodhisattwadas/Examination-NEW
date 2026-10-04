<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\ExamDuty;
use App\Models\ExamDutyStaff;

class DashboardController extends Controller
{
    /**
     * Display the admin panel dashboard.
     */
    public function index()
    {
        $totalStaff = Staff::count();
        $teachingStaff = Staff::where('staff_type', 'Teaching')->count();
        $nonTeachingStaff = Staff::where('staff_type', 'Non-teaching')->count();
        
        $totalDuties = ExamDuty::count();
        $totalSecretaryDuties = ExamDutyStaff::where('is_exam_secretary', true)->count();
        $totalAllocations = ExamDutyStaff::count();

        // All Duty Entries (for dashboard full list with edit/delete)
        $allDuties = ExamDuty::withCount([
            'staffs as staff_count',
            'dutyAssignments as secretary_count' => function($query) {
                $query->where('is_exam_secretary', true);
            },
            'dutyAssignments as oic_count' => function($query) {
                $query->where('is_oic', true);
            }
        ])->orderBy('id', 'desc')
          ->get();

        return view('dashboard', compact(
            'totalStaff', 'teachingStaff', 'nonTeachingStaff',
            'totalDuties', 'totalSecretaryDuties', 'totalAllocations',
            'allDuties'
        ));
    }
}
