<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class StaffController extends Controller
{
    /**
     * Display a listing of the staff.
     */
    public function index(Request $request)
    {
        $query = Staff::query();

        // Apply filters
        if ($request->filled('staff_type')) {
            $query->where('staff_type', $request->staff_type);
        }

        if ($request->filled('department')) {
            $query->where('department', 'like', '%' . $request->department . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('staff_code', 'like', '%' . $search . '%')
                  ->orWhere('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $staffs = $query
            ->withCount('dutyAssignments')
            ->orderByRaw("CASE WHEN staff_type = 'Teaching' THEN 1 ELSE 2 END")
            ->orderBy('name')
            ->get();
        
        // Get unique departments for filter dropdown
        $departments = Staff::whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->pluck('department')
            ->toArray();

        return view('staff.index', compact('staffs', 'departments'));
    }

    /**
     * Show the form for creating a new staff member.
     */
    public function create()
    {
        return view('staff.create_edit');
    }

    /**
     * Store a newly created staff member in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'staff_type' => 'required|in:Teaching,Non-teaching',
            'status' => 'required|in:Active,Inactive',
            // staff_code is now auto-generated if not provided (for manual entry or legacy)
            'staff_code' => 'nullable|string|max:50|unique:staffs,staff_code',
            'department' => 'nullable|string|max:100',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
        ]);

        // Auto-generate staff_code for manual creation if missing (e.g. T005 or NT012)
        if (empty($validated['staff_code'])) {
            $validated['staff_code'] = $this->generateNextStaffCode($validated['staff_type']);
        }

        Staff::create($validated);

        return redirect()->route('staff.index')->with('success', 'Staff created successfully!');
    }

    /**
     * Show the form for editing the specified staff member.
     */
    public function edit(Staff $staff)
    {
        return view('staff.create_edit', compact('staff'));
    }

    /**
     * Update the specified staff member in storage.
     */
    public function update(Request $request, Staff $staff)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'staff_type' => 'required|in:Teaching,Non-teaching',
            'status' => 'required|in:Active,Inactive',
            // staff_code can be provided (legacy/manual) but is auto-generated on create when missing
            'staff_code' => 'nullable|string|max:50|unique:staffs,staff_code,' . $staff->id,
            'department' => 'nullable|string|max:100',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
        ]);

        $staff->update($validated);

        return redirect()->route('staff.index')->with('success', 'Staff updated successfully!');
    }

    /**
     * Toggle status of the staff member (Active/Inactive).
     */
    public function toggleStatus(Staff $staff)
    {
        $staff->status = $staff->status === 'Active' ? 'Inactive' : 'Active';
        $staff->save();

        return redirect()->route('staff.index')->with('success', "Staff status changed to {$staff->status} successfully!");
    }

    /**
     * Remove the specified staff from storage.
     * Only allowed if the staff has no duty assignments (to preserve historical exam records).
     */
    public function destroy(Staff $staff)
    {
        $name = $staff->name;

        DB::transaction(function () use ($staff) {
            // Delete related duty assignments first if any exist
            $staff->dutyAssignments()->delete();
            $staff->delete();
        });

        return redirect()->route('staff.index')
            ->with('success', "Staff \"{$name}\" deleted successfully!");
    }

    /**
     * Show the staff upload form.
     */
    public function uploadForm()
    {
        return view('staff.upload');
    }

    /**
     * Process staff upload (CSV, XLSX, XLS) and display validation preview.
     * Supports both 1-column (Name only) with default category selection
     * and 2-column (Name, Type).
     */
    public function uploadPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls,txt|max:5120',
            'default_staff_type' => 'nullable|string|in:Teaching,Non-teaching,Auto-detect',
        ]);

        $defaultType = $request->input('default_staff_type', 'Auto-detect');
        $file = $request->file('file');
        
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error reading file: ' . $e->getMessage());
        }

        if (count($rows) === 0) {
            return redirect()->back()->with('error', 'Uploaded file is completely empty.');
        }

        // Check if first row is a header
        $firstRow = $rows[0];
        $headers = array_map(function($header) {
            return strtolower(trim(str_replace([' ', '_', '-'], '', (string)$header)));
        }, $firstRow);

        $hasNameHeader = in_array('staffname', $headers, true) || in_array('name', $headers, true);
        $hasTypeHeader = in_array('stafftype', $headers, true) || in_array('type', $headers, true) || in_array('category', $headers, true);
        $hasHeader = $hasNameHeader || $hasTypeHeader || in_array('staffcode', $headers, true);

        $startIdx = $hasHeader ? 1 : 0;
        $nameCol = 0;
        $typeCol = 1;

        if ($hasHeader) {
            $foundName = array_search('staffname', $headers);
            if ($foundName === false) $foundName = array_search('name', $headers);
            if ($foundName !== false) $nameCol = $foundName;

            $foundType = array_search('stafftype', $headers);
            if ($foundType === false) $foundType = array_search('type', $headers);
            if ($foundType === false) $foundType = array_search('category', $headers);
            if ($foundType !== false) $typeCol = $foundType;
            else $typeCol = null;
        } else {
            if (count(array_filter($firstRow)) <= 1) {
                $typeCol = null;
            }
        }

        $validRows = [];
        $invalidRows = [];
        $batchGeneratedCodes = [];
        $existingNamesInDb = Staff::pluck('name')->map(fn($n) => strtolower(trim($n)))->toArray();
        $seenInBatch = [];

        for ($i = $startIdx; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty(array_filter($row))) {
                continue;
            }

            $rawName = isset($row[$nameCol]) ? trim((string)$row[$nameCol]) : '';
            // Clean leading numbers, bullets
            $name = preg_replace('/^(\d+[\.\)\:\-]\s*|[\*\•\-\–\—\>]\s*|\[\d+\]\s*)/u', '', $rawName);
            $name = trim($name);

            $staffTypeRaw = ($typeCol !== null && isset($row[$typeCol])) ? trim((string)$row[$typeCol]) : '';
            $staffType = '';

            if ($staffTypeRaw) {
                if (stripos($staffTypeRaw, 'non') !== false) {
                    $staffType = 'Non-teaching';
                } elseif (stripos($staffTypeRaw, 'teach') !== false) {
                    $staffType = 'Teaching';
                }
            }

            // Fallback to defaultType if type column was not specified or empty
            if (empty($staffType)) {
                if ($defaultType !== 'Auto-detect' && !empty($defaultType)) {
                    $staffType = $defaultType;
                } else {
                    $staffType = 'Teaching';
                }
            }

            $errors = [];
            if (empty($name)) {
                $errors[] = 'Staff Name is required.';
            }

            $lowerName = strtolower($name);
            $isDuplicateInDb = in_array($lowerName, $existingNamesInDb, true);
            $isDuplicateInBatch = in_array($lowerName, $seenInBatch, true);

            if ($isDuplicateInBatch) {
                $errors[] = 'Duplicate name in this upload file.';
            }
            $seenInBatch[] = $lowerName;

            if (empty($errors)) {
                $staffCode = $this->generateNextStaffCode($staffType, $batchGeneratedCodes);
                $validRows[] = [
                    'row_num' => $i + 1,
                    'staff_code' => $staffCode,
                    'name' => $name,
                    'staff_type' => $staffType,
                    'status' => 'Active',
                    'is_db_duplicate' => $isDuplicateInDb,
                    'department' => null,
                    'mobile' => null,
                    'email' => null,
                ];
            } else {
                $invalidRows[] = [
                    'row_num' => $i + 1,
                    'staff_code' => 'N/A',
                    'name' => $name ?: 'N/A',
                    'staff_type' => $staffType ?: ($staffTypeRaw ?: 'Teaching'),
                    'status' => 'Active',
                    'errors' => $errors,
                ];
            }
        }

        if (empty($validRows) && empty($invalidRows)) {
            return redirect()->back()->with('error', 'No valid staff rows found in the uploaded file.');
        }

        return view('staff.preview', compact('validRows', 'invalidRows'));
    }

    /**
     * Process bulk staff intake via copy-pasted text in textarea.
     * Auto-cleans bullet points, line numbering, comma separation, and extra spaces.
     */
    public function pastePreview(Request $request)
    {
        $request->validate([
            'pasted_names' => 'required|string',
            'default_staff_type' => 'nullable|string|in:Teaching,Non-teaching,Auto-detect',
        ]);

        $defaultType = $request->input('default_staff_type', 'Teaching');
        if ($defaultType === 'Auto-detect') {
            $defaultType = 'Teaching';
        }

        $rawText = $request->input('pasted_names');
        // Split by lines or newlines
        $lines = preg_split('/\r\n|\r|\n/', $rawText);

        $validRows = [];
        $invalidRows = [];
        $batchGeneratedCodes = [];
        $existingNamesInDb = Staff::pluck('name')->map(fn($n) => strtolower(trim($n)))->toArray();
        $seenInBatch = [];

        $rowNum = 0;
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            // Check if user pasted a single comma-separated line (e.g. John, Mary, David)
            // without newlines:
            $subItems = count($lines) === 1 && str_contains($line, ',') && !preg_match('/(teaching|non)/i', $line)
                ? explode(',', $line)
                : [$line];

            foreach ($subItems as $subItem) {
                $subItem = trim($subItem);
                if ($subItem === '') continue;

                $rowNum++;

                // Strip leading numbers (e.g., "1.", "1)", "1 -", "[1]") and bullets (e.g., "•", "-", "*")
                $cleaned = preg_replace('/^(\d+[\.\)\:\-]\s*|[\*\•\-\–\—\>]\s*|\[\d+\]\s*)/u', '', $subItem);
                $cleaned = trim($cleaned);

                if ($cleaned === '') continue;

                $name = $cleaned;
                $staffType = $defaultType;

                // If line contains delimiter with Type (e.g. "Dr. Smith, Teaching" or "Ramesh | Non-teaching")
                if (preg_match('/^(.*?)[,\t\|;]+(.*)$/', $cleaned, $matches)) {
                    $possibleName = trim($matches[1]);
                    $possibleType = trim($matches[2]);

                    if (stripos($possibleType, 'non') !== false) {
                        $staffType = 'Non-teaching';
                        $name = $possibleName;
                    } elseif (stripos($possibleType, 'teach') !== false) {
                        $staffType = 'Teaching';
                        $name = $possibleName;
                    }
                }

                $errors = [];
                if (empty($name)) {
                    $errors[] = 'Staff Name cannot be empty.';
                }

                $lowerName = strtolower($name);
                $isDuplicateInDb = in_array($lowerName, $existingNamesInDb, true);
                $isDuplicateInBatch = in_array($lowerName, $seenInBatch, true);

                if ($isDuplicateInBatch) {
                    $errors[] = 'Duplicate name in current paste list.';
                }
                $seenInBatch[] = $lowerName;

                if (empty($errors)) {
                    $staffCode = $this->generateNextStaffCode($staffType, $batchGeneratedCodes);
                    $validRows[] = [
                        'row_num' => $rowNum,
                        'staff_code' => $staffCode,
                        'name' => $name,
                        'staff_type' => $staffType,
                        'status' => 'Active',
                        'is_db_duplicate' => $isDuplicateInDb,
                        'department' => null,
                        'mobile' => null,
                        'email' => null,
                    ];
                } else {
                    $invalidRows[] = [
                        'row_num' => $rowNum,
                        'staff_code' => 'N/A',
                        'name' => $name ?: 'N/A',
                        'staff_type' => $staffType,
                        'status' => 'Active',
                        'errors' => $errors,
                    ];
                }
            }
        }

        if (empty($validRows) && empty($invalidRows)) {
            return redirect()->back()->with('error', 'No valid names were detected in the pasted text.');
        }

        return view('staff.preview', compact('validRows', 'invalidRows'));
    }

    /**
     * Commit valid staff members from preview to database.
     */
    public function importCommit(Request $request)
    {
        $request->validate([
            'valid_data' => 'required|string',
        ]);

        $validRows = json_decode(base64_decode($request->valid_data), true);

        if (!is_array($validRows) || empty($validRows)) {
            return redirect()->route('staff.index')->with('error', 'No valid records to import.');
        }

        $importedCount = 0;
        foreach ($validRows as $row) {
            // Safety re-check in case of duplicate code
            if (Staff::where('staff_code', $row['staff_code'])->exists()) {
                $row['staff_code'] = $this->generateNextStaffCode($row['staff_type']);
            }

            Staff::create([
                'staff_code' => $row['staff_code'],
                'name' => $row['name'],
                'staff_type' => $row['staff_type'],
                'department' => $row['department'] ?? null,
                'mobile' => $row['mobile'] ?? null,
                'email' => $row['email'] ?? null,
                'status' => $row['status'] ?? 'Active',
            ]);
            $importedCount++;
        }

        return redirect()->route('staff.index')->with('success', "Successfully imported {$importedCount} staff records!");
    }

    /**
     * Download a sample staff import CSV file.
     */
    public function downloadSample()
    {
        $headers = ['Staff Name', 'Staff Type'];
        $data = [
            ['Dr. John Doe', 'Teaching'],
            ['Jane Smith', 'Non-teaching'],
            ['Dr. Priya Sharma', 'Teaching'],
            ['Ramesh Kumar', 'Non-teaching'],
        ];

        $callback = function() use ($headers, $data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->streamDownload($callback, 'sample_staff_import.csv', [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sample_staff_import.csv"',
        ]);
    }

    /**
     * Download a sample staff import Excel (.xlsx) file using PhpSpreadsheet.
     */
    public function downloadSampleExcel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Staff Template');

        // Headers
        $sheet->setCellValue('A1', 'Staff Name');
        $sheet->setCellValue('B1', 'Staff Type');

        // Sample Data
        $data = [
            ['Dr. John Doe', 'Teaching'],
            ['Jane Smith', 'Non-teaching'],
            ['Dr. Priya Sharma', 'Teaching'],
            ['Ramesh Kumar', 'Non-teaching'],
        ];

        $sheet->fromArray($data, null, 'A2');

        // Styling Header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'], // Indigo
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $sheet->getStyle('A1:B1')->applyFromArray($headerStyle);
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);

        $writer = new Xlsx($spreadsheet);
        $callback = function() use ($writer) {
            $writer->save('php://output');
        };

        return response()->streamDownload($callback, 'sample_staff_import.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="sample_staff_import.xlsx"',
        ]);
    }

    /**
     * Generate the next unique staff code for a given type.
     * Supports batch exclusion list for imports.
     */
    protected function generateNextStaffCode(string $staffType, array &$excludeCodes = []): string
    {
        $prefix = $staffType === 'Teaching' ? 'T' : 'NT';
        $length = 3;

        // Collect all codes that start with prefix from DB
        $dbCodes = Staff::where('staff_code', 'like', $prefix . '%')
            ->pluck('staff_code')
            ->toArray();

        $max = 0;

        // Check DB + previously generated in this batch (passed by reference)
        foreach (array_merge($dbCodes, $excludeCodes) as $code) {
            if (str_starts_with($code, $prefix)) {
                $numPart = substr($code, strlen($prefix));
                if (ctype_digit($numPart)) {
                    $max = max($max, (int) $numPart);
                }
            }
        }

        do {
            $max++;
            $candidate = $prefix . str_pad($max, $length, '0', STR_PAD_LEFT);
        } while (
            in_array($candidate, $dbCodes, true) ||
            in_array($candidate, $excludeCodes, true) ||
            Staff::where('staff_code', $candidate)->exists()
        );

        // Track this code so subsequent calls in the same batch get the next number
        $excludeCodes[] = $candidate;

        return $candidate;
    }
}
