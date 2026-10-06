<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\ExamDuty;
use App\Models\ExamDutyStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportController extends Controller
{
    /**
     * Display the Annual Duty Hours Matrix & Calculation Report.
     */
    public function index(Request $request)
    {
        $reportData = $this->buildMatrixData($request);

        return view('reports.index', $reportData);
    }

    /**
     * Export the Annual Duty Hours Matrix to Excel (.xlsx).
     */
    public function exportExcel(Request $request)
    {
        $data = $this->buildMatrixData($request);
        $exams = $data['exams'];
        $matrix = $data['matrix'];
        $examTotals = $data['examTotals'];
        $grandTotals = $data['grandTotals'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Annual Duty Calculation');

        // Document Title Block
        $sheet->setCellValue('A1', 'EXAMINATION DUTY MANAGEMENT PORTAL');
        $sheet->setCellValue('A2', 'Annual Cumulative Exam Duty Hours Calculation Report');
        $sheet->setCellValue('A3', 'Generated on: ' . now()->format('d M Y, h:i A'));

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E293B'));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('475569'));
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));

        // Table Header
        $headerRow = 5;
        $sheet->setCellValue('A' . $headerRow, '#');
        $sheet->setCellValue('B' . $headerRow, 'Staff Name');
        $sheet->setCellValue('C' . $headerRow, 'Staff Type');
        $sheet->setCellValue('D' . $headerRow, 'Total Regular Duty Hours');
        $sheet->setCellValue('E' . $headerRow, 'Total OIC Duties');
        $sheet->setCellValue('F' . $headerRow, 'Total Secretary Duties');

        $lastColLetter = 'F';

        // Header Styling
        $headerRange = "A{$headerRow}:{$lastColLetter}{$headerRow}";
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('1E3A8A');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($headerRow)->setRowHeight(28);

        // Data Rows
        $currentRow = $headerRow + 1;
        $sl = 1;

        foreach ($matrix as $row) {
            $sheet->setCellValue('A' . $currentRow, $sl++);
            $sheet->setCellValue('B' . $currentRow, $row['staff']->name);
            $sheet->setCellValue('C' . $currentRow, $row['staff']->staff_type);

            $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Totals
            $hours = (float)$row['total_hours'];
            $sheet->setCellValue('D' . $currentRow, $hours == (int)$hours ? (int)$hours : $hours);
            $sheet->setCellValue('E' . $currentRow, $row['staff']->staff_type === 'Teaching' ? $row['total_oic'] : 'N/A');
            $sheet->setCellValue('F' . $currentRow, $row['staff']->staff_type === 'Teaching' ? $row['total_sec'] : 'N/A');

            $sheet->getStyle("D{$currentRow}:F{$currentRow}")
                  ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$currentRow}")->getFont()->setBold(true);

            // Light alternate row stripe
            if ($sl % 2 == 0) {
                $sheet->getStyle("A{$currentRow}:{$lastColLetter}{$currentRow}")
                      ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F8FAFC');
            }

            $currentRow++;
        }

        // Bottom Total Row
        $sheet->setCellValue('A' . $currentRow, 'TOTAL');
        $sheet->mergeCells("A{$currentRow}:C{$currentRow}");
        $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $totalH = (float)$grandTotals['hours'];
        $sheet->setCellValue('D' . $currentRow, $totalH == (int)$totalH ? (int)$totalH : $totalH);
        $sheet->setCellValue('E' . $currentRow, $grandTotals['oic']);
        $sheet->setCellValue('F' . $currentRow, $grandTotals['sec']);

        $sheet->getStyle("D{$currentRow}:F{$currentRow}")
              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D{$currentRow}:F{$currentRow}")
              ->getFont()->setBold(true);

        $sheet->getStyle("A{$currentRow}:{$lastColLetter}{$currentRow}")
              ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('E2E8F0');
        $sheet->getRowDimension($currentRow)->setRowHeight(24);

        // Apply grid borders
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'CBD5E1'],
                ],
            ],
        ];
        $sheet->getStyle("A{$headerRow}:{$lastColLetter}{$currentRow}")->applyFromArray($borderStyle);

        // Auto size columns
        foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $colLetter) {
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $filename = 'Annual_Exam_Duty_Hours_Report_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Export the Annual Duty Hours Matrix to PDF.
     */
    public function exportPdf(Request $request)
    {
        $data = $this->buildMatrixData($request);

        $pdf = Pdf::loadView('reports.pdf_template', $data)
                  ->setPaper('a4', 'portrait');

        $filename = 'Annual_Exam_Duty_Hours_' . date('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Helper to prepare matrix calculation data.
     */
    private function buildMatrixData(Request $request): array
    {
        // Fetch all exams ordered by creation date
        $allExamsQuery = ExamDuty::orderBy('id', 'asc');

        // Optional filter: specific exams selected
        $selectedExamIds = $request->input('exam_ids', []);
        if (!is_array($selectedExamIds)) {
            $selectedExamIds = $selectedExamIds ? [$selectedExamIds] : [];
        }

        if (!empty($selectedExamIds)) {
            $exams = ExamDuty::whereIn('id', $selectedExamIds)->orderBy('id', 'asc')->get();
        } else {
            $exams = $allExamsQuery->get();
        }

        // Fetch staff with optional filters
        $staffQuery = Staff::query();

        if ($request->filled('staff_type')) {
            $staffQuery->where('staff_type', $request->staff_type);
        }

        if ($request->filled('staff_name')) {
            $staffQuery->where('name', 'like', '%' . trim($request->staff_name) . '%');
        }

        // Active staff: Teachers first alphabetically, then Non-teaching alphabetically (preserving full Dr. name)
        $staffs = $staffQuery->get()
            ->sortBy(function($staff) {
                $categoryOrder = $staff->staff_type === 'Teaching' ? '1_' : '2_';
                return $categoryOrder . strtolower($staff->clean_name);
            }, SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        // Load all duty assignments for the relevant exams and staff
        $examIds = $exams->pluck('id');
        $staffIds = $staffs->pluck('id');

        $assignments = ExamDutyStaff::whereIn('exam_duty_id', $examIds)
            ->whereIn('staff_id', $staffIds)
            ->get();

        // Index assignments by "staffId_examId" for instant O(1) lookup
        $assignmentMap = [];
        foreach ($assignments as $a) {
            $assignmentMap["{$a->staff_id}_{$a->exam_duty_id}"] = $a;
        }

        // Build the staff-by-exam matrix and compute totals
        $matrix = [];
        $examTotals = [];
        foreach ($exams as $exam) {
            $examTotals[$exam->id] = [
                'hours' => 0.0,
                'staff_count' => 0,
            ];
        }

        $grandTotals = [
            'hours' => 0.0,
            'oic' => 0,
            'sec' => 0,
            'teaching_staff' => 0,
            'non_teaching_staff' => 0,
        ];

        foreach ($staffs as $staff) {
            $row = [
                'staff' => $staff,
                'exams' => [],
                'total_hours' => 0.0,
                'total_oic' => 0,
                'total_sec' => 0,
            ];

            if ($staff->staff_type === 'Teaching') {
                $grandTotals['teaching_staff']++;
            } else {
                $grandTotals['non_teaching_staff']++;
            }

            foreach ($exams as $exam) {
                $key = "{$staff->id}_{$exam->id}";
                if (isset($assignmentMap[$key])) {
                    $a = $assignmentMap[$key];
                    $h = (float) $a->duty_hours;
                    $oic = (int) $a->oic_count;
                    $sec = (bool) $a->is_exam_secretary;

                    $row['exams'][$exam->id] = [
                        'hours' => $h,
                        'oic' => $oic,
                        'sec' => $sec,
                    ];

                    $row['total_hours'] += $h;
                    $row['total_oic'] += $oic;
                    if ($sec) {
                        $row['total_sec']++;
                    }

                    // Column totals
                    $examTotals[$exam->id]['hours'] += $h;
                    $examTotals[$exam->id]['staff_count']++;
                } else {
                    $row['exams'][$exam->id] = null;
                }
            }

            $grandTotals['hours'] += $row['total_hours'];
            $grandTotals['oic'] += $row['total_oic'];
            $grandTotals['sec'] += $row['total_sec'];

            $matrix[] = $row;
        }

        // Also fetch all exams for the dropdown filter and exam breakdown tab
        $allExamsList = ExamDuty::withCount('dutyAssignments')->orderBy('id', 'desc')->get();

        return [
            'exams' => $exams,
            'allExamsList' => $allExamsList,
            'staffs' => $staffs,
            'matrix' => $matrix,
            'examTotals' => $examTotals,
            'grandTotals' => $grandTotals,
            'filters' => $request->all(),
        ];
    }
}
