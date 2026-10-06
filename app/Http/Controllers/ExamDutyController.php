<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\ExamDuty;
use App\Models\ExamDutyStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExamDutyController extends Controller
{
    /**
     * Display a listing of all recorded exams.
     */
    public function index(Request $request)
    {
        $allExams = ExamDuty::withCount([
            'staffs as staff_count',
            'dutyAssignments as secretary_count' => function($query) {
                $query->where('is_exam_secretary', true);
            },
            'dutyAssignments as oic_count' => function($query) {
                $query->where('is_oic', true);
            }
        ])
        ->with('dutyAssignments')
        ->orderBy('id', 'desc')
        ->get();

        foreach ($allExams as $exam) {
            $exam->total_hours = (float) $exam->dutyAssignments->sum('duty_hours');
        }

        return view('duty.index', compact('allExams'));
    }

    /**
     * Show the dedicated form for creating a new exam duty record.
     */
    public function create(Request $request)
    {
        // Active staff list: Teaching first (A-Z by clean name), then Non-teaching (A-Z by clean name)
        $staffs = Staff::active()
            ->get()
            ->sortBy(function($staff) {
                $categoryOrder = $staff->staff_type === 'Teaching' ? '1_' : '2_';
                return $categoryOrder . strtolower($staff->clean_name);
            }, SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return view('duty.create', compact('staffs'));
    }

    /**
     * Store a newly created exam duty in storage.
     * Supports saving and resuming data entry later.
     */
    public function store(Request $request)
    {
        $request->validate([
            'exam_name' => 'required|string|max:150',
            'remarks' => 'nullable|string',
            'duties' => 'nullable|array',
            'duties.*.duty_hours' => 'nullable|numeric|min:0|max:500',
            'duties.*.oic_count' => 'nullable|integer|min:0|max:100',
        ]);

        $examName = trim($request->exam_name);
        $remarks = $request->remarks;

        // Parse date range (e.g. "2026-11-10 to 2026-11-25" or "2026-11-10")
        $startDate = null;
        $endDate = null;
        if ($request->filled('exam_date_range')) {
            $rangeStr = trim($request->input('exam_date_range'));
            if (str_contains($rangeStr, ' to ')) {
                $parts = explode(' to ', $rangeStr);
                $startDate = trim($parts[0]);
                $endDate = trim($parts[1]);
            } else {
                $startDate = $rangeStr;
                $endDate = $rangeStr;
            }
        }

        // If exam with same name already exists, automatically resume it
        $existingExam = ExamDuty::where('exam_name', $examName)->first();
        if ($existingExam) {
            return redirect()->route('duty.edit', $existingExam->id)
                ->with('info', "An exam with the name '{$examName}' already exists. Resuming data entry for this exam.");
        }

        $allStaff = collect();
        if (!empty($request->duties)) {
            $staffIds = array_keys($request->duties);
            $allStaff = Staff::whereIn('id', $staffIds)->get()->keyBy('id');
        }

        // Parse duties to get staff details
        $selectedStaff = [];
        if (!empty($request->duties)) {
            foreach ($request->duties as $staffId => $dutyData) {
                $staff = $allStaff->get($staffId);
                if (!$staff) continue;

                $dutyHours = isset($dutyData['duty_hours']) && is_numeric($dutyData['duty_hours']) ? (float)$dutyData['duty_hours'] : 0.0;
                $oicCount = ($staff->staff_type === 'Teaching' && isset($dutyData['oic_count']) && is_numeric($dutyData['oic_count'])) ? (int)$dutyData['oic_count'] : 0;
                $isSecretary = ($staff->staff_type === 'Teaching' && !empty($dutyData['is_exam_secretary'])) ? 1 : 0;

                if ($dutyHours > 0 || $oicCount > 0 || $isSecretary === 1) {
                    $selectedStaff[$staffId] = [
                        'is_duty' => $dutyHours > 0 ? 1 : 0,
                        'is_exam_secretary' => $isSecretary,
                        'is_oic' => $oicCount > 0 ? 1 : 0,
                        'oic_count' => $oicCount,
                        'duty_hours' => $dutyHours,
                    ];
                }
            }
        }

        // Save everything inside a transaction
        $duty = DB::transaction(function() use ($examName, $startDate, $endDate, $remarks, $selectedStaff) {
            $duty = ExamDuty::create([
                'exam_name' => $examName,
                'exam_date' => $startDate ?: now()->toDateString(),
                'exam_end_date' => $endDate,
                'exam_time' => 'Regular',
                'default_hours' => 3.00,
                'remarks' => $remarks,
            ]);

            foreach ($selectedStaff as $staffId => $status) {
                ExamDutyStaff::create([
                    'exam_duty_id' => $duty->id,
                    'staff_id' => $staffId,
                    'is_duty' => $status['is_duty'],
                    'is_exam_secretary' => $status['is_exam_secretary'],
                    'is_oic' => $status['is_oic'],
                    'oic_count' => $status['oic_count'],
                    'duty_hours' => $status['duty_hours'],
                ]);
            }

            return $duty;
        });

        // Resume mode vs Exit mode
        if ($request->input('action') === 'save_and_resume') {
            return redirect()->route('duty.edit', $duty->id)
                ->with('success', "Exam '{$examName}' created! Progress saved. You can continue entering data or resume anytime.");
        }

        return redirect()->route('duty.index')
            ->with('success', "Exam duty hours for '{$examName}' saved successfully!");
    }

    /**
     * Show the form for editing an existing exam duty (full edit including staff assignments).
     */
    public function edit(ExamDuty $duty)
    {
        // Active staff list: Teaching first (A-Z by clean name), then Non-teaching (A-Z by clean name)
        $staffs = Staff::active()
            ->get()
            ->sortBy(function($staff) {
                $categoryOrder = $staff->staff_type === 'Teaching' ? '1_' : '2_';
                return $categoryOrder . strtolower($staff->clean_name);
            }, SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        // Current assignments keyed by staff_id for pre-filling the form
        $currentAssignments = $duty->dutyAssignments()->get()->keyBy('staff_id');

        return view('duty.edit', compact('duty', 'staffs', 'currentAssignments'));
    }

    /**
     * Update an existing exam duty.
     * Supports saving and resuming data entry later.
     */
    public function update(Request $request, ExamDuty $duty)
    {
        $request->validate([
            'exam_name' => 'required|string|max:150',
            'remarks' => 'nullable|string',
            'duties' => 'nullable|array',
            'duties.*.duty_hours' => 'nullable|numeric|min:0|max:500',
            'duties.*.oic_count' => 'nullable|integer|min:0|max:100',
        ]);

        $examName = trim($request->exam_name);
        $remarks = $request->remarks;

        // Parse date range (e.g. "2026-11-10 to 2026-11-25" or "2026-11-10")
        $startDate = null;
        $endDate = null;
        if ($request->filled('exam_date_range')) {
            $rangeStr = trim($request->input('exam_date_range'));
            if (str_contains($rangeStr, ' to ')) {
                $parts = explode(' to ', $rangeStr);
                $startDate = trim($parts[0]);
                $endDate = trim($parts[1]);
            } else {
                $startDate = $rangeStr;
                $endDate = $rangeStr;
            }
        }

        $allStaff = collect();
        if (!empty($request->duties)) {
            $staffIds = array_keys($request->duties);
            $allStaff = Staff::whereIn('id', $staffIds)->get()->keyBy('id');
        }

        // Parse duties to get staff details
        $selectedStaff = [];
        if (!empty($request->duties)) {
            foreach ($request->duties as $staffId => $dutyData) {
                $staff = $allStaff->get($staffId);
                if (!$staff) continue;

                $dutyHours = isset($dutyData['duty_hours']) && is_numeric($dutyData['duty_hours']) ? (float)$dutyData['duty_hours'] : 0.0;
                $oicCount = ($staff->staff_type === 'Teaching' && isset($dutyData['oic_count']) && is_numeric($dutyData['oic_count'])) ? (int)$dutyData['oic_count'] : 0;
                $isSecretary = ($staff->staff_type === 'Teaching' && !empty($dutyData['is_exam_secretary'])) ? 1 : 0;

                if ($dutyHours > 0 || $oicCount > 0 || $isSecretary === 1) {
                    $selectedStaff[$staffId] = [
                        'is_duty' => $dutyHours > 0 ? 1 : 0,
                        'is_exam_secretary' => $isSecretary,
                        'is_oic' => $oicCount > 0 ? 1 : 0,
                        'oic_count' => $oicCount,
                        'duty_hours' => $dutyHours,
                    ];
                }
            }
        }

        // Duplicate name check - exclude the current duty itself
        $duplicateNameExists = ExamDuty::where('exam_name', $examName)
            ->where('id', '!=', $duty->id)
            ->exists();

        if ($duplicateNameExists) {
            return redirect()->back()->withInput()->with('error', "Another exam duty entry with the name '{$examName}' already exists.");
        }

        // Save header + replace assignments inside a transaction
        DB::transaction(function() use ($duty, $examName, $startDate, $endDate, $remarks, $selectedStaff) {
            $duty->update([
                'exam_name' => $examName,
                'exam_date' => $startDate,
                'exam_end_date' => $endDate,
                'remarks' => $remarks,
            ]);

            // Remove all old assignments
            $duty->dutyAssignments()->delete();

            // Insert the new selection
            foreach ($selectedStaff as $staffId => $status) {
                ExamDutyStaff::create([
                    'exam_duty_id' => $duty->id,
                    'staff_id' => $staffId,
                    'is_duty' => $status['is_duty'],
                    'is_exam_secretary' => $status['is_exam_secretary'],
                    'is_oic' => $status['is_oic'],
                    'oic_count' => $status['oic_count'],
                    'duty_hours' => $status['duty_hours'],
                ]);
            }
        });

        // Resume mode vs Exit mode
        if ($request->input('action') === 'save_and_resume') {
            return redirect()->route('duty.edit', $duty->id)
                ->with('success', "Progress saved for '{$examName}'. You can continue entering data or resume anytime.");
        }

        return redirect()->route('duty.index')
            ->with('success', "Exam duty hours for '{$examName}' updated successfully!");
    }

    /**
     * Download individual exam duty report in PDF format.
     */
    public function exportPdf(ExamDuty $duty)
    {
        $duty->load(['dutyAssignments.staff' => function($q) {
            $q->orderByRaw("CASE WHEN staff_type = 'Teaching' THEN 1 ELSE 2 END")->orderBy('name');
        }]);

        $assignments = $duty->dutyAssignments;
        $totalHours = (float) $assignments->sum('duty_hours');
        $totalOic = (int) $assignments->sum('oic_count');
        $totalSec = (int) $assignments->where('is_exam_secretary', true)->count();
        $teachingCount = $assignments->filter(fn($a) => $a->staff && $a->staff->staff_type === 'Teaching')->count();
        $nonTeachingCount = $assignments->filter(fn($a) => $a->staff && $a->staff->staff_type === 'Non-teaching')->count();

        $pdf = Pdf::loadView('duty.single_exam_pdf', compact('duty', 'assignments', 'totalHours', 'totalOic', 'totalSec', 'teachingCount', 'nonTeachingCount'))
                  ->setPaper('a4', 'portrait');

        $cleanExamName = Str::slug($duty->exam_name, '_');
        return $pdf->download("Exam_Duty_Report_{$cleanExamName}.pdf");
    }

    /**
     * Download individual exam duty report in Excel (.xlsx) format.
     */
    public function exportExcel(ExamDuty $duty)
    {
        $duty->load(['dutyAssignments.staff' => function($q) {
            $q->orderByRaw("CASE WHEN staff_type = 'Teaching' THEN 1 ELSE 2 END")->orderBy('name');
        }]);

        $assignments = $duty->dutyAssignments;
        $totalHours = (float) $assignments->sum('duty_hours');
        $totalOic = (int) $assignments->sum('oic_count');
        $totalSec = (int) $assignments->where('is_exam_secretary', true)->count();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Exam Duty Report');

        // Document Title Block
        $sheet->setCellValue('A1', 'EXAMINATION DUTY MANAGEMENT PORTAL');
        $sheet->setCellValue('A2', 'Duty Report: ' . $duty->exam_name);
        $sheet->setCellValue('A3', 'Remarks: ' . ($duty->remarks ?: 'None') . ' | Generated on: ' . now()->format('d M Y, h:i A'));

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E293B'));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E3A8A'));
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));

        // Table Header
        $headerRow = 5;
        $sheet->setCellValue('A' . $headerRow, '#');
        $sheet->setCellValue('B' . $headerRow, 'Staff Name');
        $sheet->setCellValue('C' . $headerRow, 'Category');
        $sheet->setCellValue('D' . $headerRow, 'Duty Hours');
        $sheet->setCellValue('E' . $headerRow, 'OIC Duty Count');
        $sheet->setCellValue('F' . $headerRow, 'Exam Secretary');

        $headerRange = "A{$headerRow}:F{$headerRow}";
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('1E3A8A');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($headerRow)->setRowHeight(26);

        // Data Rows
        $currentRow = $headerRow + 1;
        $sl = 1;
        foreach ($assignments as $a) {
            $staff = $a->staff;
            if (!$staff) continue;

            $sheet->setCellValue('A' . $currentRow, $sl++);
            $sheet->setCellValue('B' . $currentRow, $staff->name);
            $sheet->setCellValue('C' . $currentRow, $staff->staff_type);
            $hours = (float)$a->duty_hours;
            $sheet->setCellValue('D' . $currentRow, $hours == (int)$hours ? (int)$hours : $hours);
            $sheet->setCellValue('E' . $currentRow, $staff->staff_type === 'Teaching' ? (int)$a->oic_count : 'N/A');
            $sheet->setCellValue('F' . $currentRow, $staff->staff_type === 'Teaching' ? ($a->is_exam_secretary ? 'YES (Secretary)' : 'No') : 'N/A');

            $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            if ($sl % 2 == 0) {
                $sheet->getStyle("A{$currentRow}:F{$currentRow}")
                      ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F8FAFC');
            }

            $currentRow++;
        }

        // Summary Total Row
        $sheet->setCellValue('A' . $currentRow, 'TOTAL');
        $sheet->mergeCells("A{$currentRow}:C{$currentRow}");
        $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->setCellValue('D' . $currentRow, $totalHours == (int)$totalHours ? (int)$totalHours : $totalHours);
        $sheet->setCellValue('E' . $currentRow, $totalOic);
        $sheet->setCellValue('F' . $currentRow, $totalSec . ' Secretaries');

        $sheet->getStyle("D{$currentRow}:F{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D{$currentRow}:F{$currentRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$currentRow}:F{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('E2E8F0');
        $sheet->getRowDimension($currentRow)->setRowHeight(24);

        // Border grid
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'CBD5E1'],
                ],
            ],
        ];
        $sheet->getStyle("A{$headerRow}:F{$currentRow}")->applyFromArray($borderStyle);

        // Auto size columns
        foreach (range(1, 6) as $c) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $cleanExamName = Str::slug($duty->exam_name, '_');
        $filename = "Exam_Duty_Report_{$cleanExamName}_" . date('Ymd_His') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Remove the specified duty entry and its staff assignments.
     */
    public function destroy(ExamDuty $duty)
    {
        DB::transaction(function () use ($duty) {
            // Delete related staff assignments first
            $duty->dutyAssignments()->delete();
            $duty->delete();
        });

        return redirect()->route('duty.index')->with('success', 'Exam duty entry and its assignments deleted successfully!');
    }
}
