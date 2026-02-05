<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_number', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%");
            });
        }

        if ($request->filled('employee_status')) {
            $query->where('employee_status', $request->employee_status);
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDir = $request->get('sort_dir', 'asc');
        $allowedSorts = ['employee_number', 'name', 'department', 'location', 'accommodation', 'employee_status', 'active_status', 'group'];

        if ($sortBy === 'group') {
            // Sort by first group name using a subquery
            $query->addSelect([
                'first_group_name' => \App\Models\EmployeeGroup::select('name')
                    ->join('employee_group_members', 'employee_groups.id', '=', 'employee_group_members.employee_group_id')
                    ->whereColumn('employee_group_members.employee_id', 'employees.id')
                    ->orderBy('employee_group_members.order')
                    ->limit(1)
            ])->orderBy('first_group_name', $sortDir === 'desc' ? 'desc' : 'asc');
        } elseif (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('name', 'asc');
        }

        $employees = $query->with('groups')->paginate(15)->withQueryString();


        $departments = Employee::distinct()->pluck('department')->filter();
        $locations = Employee::distinct()->pluck('location')->filter();

        // Attendance stats for selected month (default to current month)
        $selectedMonth = $request->input('stats_month', now()->format('Y-m'));
        $currentMonthStart = \Carbon\Carbon::parse($selectedMonth . '-01')->startOfMonth();
        $currentMonthEnd = \Carbon\Carbon::parse($selectedMonth . '-01')->endOfMonth();

        // Employees with more than 20 attendance days this month
        $highAttendanceEmployees = Employee::where('active_status', 'active')
            ->whereHas('attendances', function ($q) use ($currentMonthStart, $currentMonthEnd) {
                $q->whereDate('scanned_at', '>=', $currentMonthStart)
                    ->whereDate('scanned_at', '<=', $currentMonthEnd);
            }, '>=', 1)
            ->withCount([
                'attendances as attendance_days' => function ($q) use ($currentMonthStart, $currentMonthEnd) {
                    $q->whereDate('scanned_at', '>=', $currentMonthStart)
                        ->whereDate('scanned_at', '<=', $currentMonthEnd)
                        ->select(\DB::raw('COUNT(DISTINCT DATE(scanned_at))'));
                }
            ])
            ->get()
            ->filter(fn($e) => $e->attendance_days > 20)
            ->sortByDesc('attendance_days');

        // Employees with no attendance this month
        $noAttendanceEmployees = Employee::where('active_status', 'active')
            ->whereDoesntHave('attendances', function ($q) use ($currentMonthStart, $currentMonthEnd) {
                $q->whereDate('scanned_at', '>=', $currentMonthStart)
                    ->whereDate('scanned_at', '<=', $currentMonthEnd);
            })
            ->orderBy('name')
            ->get();


        return view('employees.index', compact(
            'employees',
            'departments',
            'locations',
            'sortBy',
            'sortDir',
            'highAttendanceEmployees',
            'noAttendanceEmployees',
            'currentMonthStart',
            'currentMonthEnd'
        ));
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        // Check if this is a quick add request (AJAX)
        $isQuickAdd = $request->input('quick_add') || $request->expectsJson();

        $validated = $request->validate([
            'employee_number' => 'nullable|string|max:50|unique:employees',
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'accommodation' => 'nullable|array',
            'accommodation.*' => 'nullable|string|max:255',
            'active_status' => 'required|in:active,inactive',
            'employee_status' => 'nullable|string|max:255',
        ]);

        // Filter out empty accommodation entries
        if (isset($validated['accommodation'])) {
            $validated['accommodation'] = array_filter($validated['accommodation'], fn($value) => !empty($value));
        }

        // Auto-generate employee number if not provided (for visitors/subcontractors)
        if (empty($validated['employee_number'])) {
            $validated['employee_number'] = Employee::generateEmployeeNumber($validated['company'] ?? null);
        }

        $employee = Employee::create($validated);

        // Return JSON for quick add requests
        if ($isQuickAdd) {
            return response()->json([
                'success' => true,
                'message' => 'Employee created successfully',
                'employee' => $employee
            ]);
        }

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully. QR code generated automatically.');
    }

    public function show(Employee $employee)
    {
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'employee_number' => 'required|string|max:50|unique:employees,employee_number,' . $employee->id,
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'accommodation' => 'nullable|array',
            'accommodation.*' => 'nullable|string|max:255',
            'active_status' => 'required|in:active,inactive',
            'employee_status' => 'nullable|string|max:255',
        ]);

        // Filter out empty accommodation entries
        if (isset($validated['accommodation'])) {
            $validated['accommodation'] = array_filter($validated['accommodation'], fn($value) => !empty($value));
        }

        $employee->update($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        // Delete QR code file
        if ($employee->qr_code_path) {
            Storage::disk('public')->delete($employee->qr_code_path);
        }

        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully.');
    }

    public function printCard(Employee $employee)
    {
        return view('employees.card', compact('employee'));
    }

    public function downloadCard(Employee $employee)
    {
        // Generate meal card image using GD
        $width = 400;
        $height = 600;

        // Create image with GD directly
        $image = imagecreatetruecolor($width, $height);

        // Define colors
        $darkBg = imagecolorallocate($image, 26, 10, 10);
        $gold = imagecolorallocate($image, 255, 215, 0);
        $white = imagecolorallocate($image, 255, 255, 255);
        $orange = imagecolorallocate($image, 255, 107, 53);
        $gray = imagecolorallocate($image, 136, 136, 136);
        $lightGray = imagecolorallocate($image, 204, 204, 204);
        $red = imagecolorallocate($image, 255, 69, 0);
        $greenBg = imagecolorallocate($image, 0, 50, 25);
        $green = imagecolorallocate($image, 0, 255, 136);
        $redBg = imagecolorallocate($image, 50, 15, 15);
        $redText = imagecolorallocate($image, 255, 68, 68);

        // Fill background
        imagefill($image, 0, 0, $darkBg);

        // Draw gradient-like accent line at bottom
        imagefilledrectangle($image, 30, $height - 20, 130, $height - 16, $red);
        imagefilledrectangle($image, 140, $height - 20, 240, $height - 16, $orange);
        imagefilledrectangle($image, 250, $height - 20, 370, $height - 16, $gold);

        // Title
        $title = "MEAL CARD";
        imagestring($image, 5, ($width - strlen($title) * 9) / 2, 30, $title, $gold);

        // Subtitle
        $subtitle = "Ramesa - Ramba Meal System";
        imagestring($image, 2, ($width - strlen($subtitle) * 6) / 2, 55, $subtitle, $gray);

        // Employee name
        $name = strtoupper($employee->name);
        if (strlen($name) > 30)
            $name = substr($name, 0, 27) . '...';
        imagestring($image, 5, ($width - strlen($name) * 9) / 2, 100, $name, $white);

        // Employee number
        $empNum = "ID: " . $employee->employee_number;
        imagestring($image, 4, ($width - strlen($empNum) * 8) / 2, 130, $empNum, $orange);

        // Department
        if ($employee->department) {
            $dept = $employee->department;
            imagestring($image, 3, ($width - strlen($dept) * 7) / 2, 160, $dept, $lightGray);
        }

        // Position
        if ($employee->position) {
            $pos = $employee->position;
            imagestring($image, 3, ($width - strlen($pos) * 7) / 2, 180, $pos, $lightGray);
        }

        // QR Code placeholder box
        $qrSize = 200;
        $qrX = ($width - $qrSize) / 2;
        $qrY = 220;

        // White background for QR
        $qrWhite = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, $qrX - 10, $qrY - 10, $qrX + $qrSize + 10, $qrY + $qrSize + 10, $qrWhite);

        // Try to read QR code if it exists (PNG version)
        if ($employee->qr_code_path && Storage::disk('public')->exists($employee->qr_code_path)) {
            $qrFullPath = Storage::disk('public')->path($employee->qr_code_path);

            // If SVG, render QR code text instead
            if (str_ends_with($employee->qr_code_path, '.svg')) {
                // Draw QR code representation text
                $qrText = $employee->employee_number;
                $black = imagecolorallocate($image, 0, 0, 0);
                imagestring($image, 5, $qrX + 50, $qrY + 80, "QR CODE", $black);
                imagestring($image, 4, $qrX + 30, $qrY + 110, $qrText, $black);
            } else {
                // Try to load PNG QR
                $qrImg = @imagecreatefrompng($qrFullPath);
                if ($qrImg) {
                    imagecopyresampled($image, $qrImg, $qrX, $qrY, 0, 0, $qrSize, $qrSize, imagesx($qrImg), imagesy($qrImg));
                    imagedestroy($qrImg);
                }
            }
        } else {
            // No QR - show placeholder
            $black = imagecolorallocate($image, 0, 0, 0);
            imagestring($image, 4, $qrX + 60, $qrY + 90, "NO QR CODE", $black);
        }

        // Status badge
        $statusY = $qrY + $qrSize + 30;
        $statusText = strtoupper($employee->active_status);
        $statusWidth = strlen($statusText) * 8 + 40;
        $statusX = ($width - $statusWidth) / 2;

        if ($employee->active_status === 'active') {
            imagefilledrectangle($image, $statusX, $statusY, $statusX + $statusWidth, $statusY + 25, $greenBg);
            imagerectangle($image, $statusX, $statusY, $statusX + $statusWidth, $statusY + 25, $green);
            imagestring($image, 3, $statusX + 20, $statusY + 5, $statusText, $green);
        } else {
            imagefilledrectangle($image, $statusX, $statusY, $statusX + $statusWidth, $statusY + 25, $redBg);
            imagerectangle($image, $statusX, $statusY, $statusX + $statusWidth, $statusY + 25, $redText);
            imagestring($image, 3, $statusX + 20, $statusY + 5, $statusText, $redText);
        }

        // Footer
        $footer = "Scan QR code for meal attendance";
        imagestring($image, 2, ($width - strlen($footer) * 6) / 2, $height - 50, $footer, $gray);

        // Output as PNG
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);

        $filename = 'meal_card_' . $employee->employee_number . '.png';

        return response($imageData)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Check for duplicate records before merging
     */
    public function checkMergeDuplicates(Request $request)
    {
        $validated = $request->validate([
            'source_id' => 'required|exists:employees,id',
            'target_id' => 'required|exists:employees,id|different:source_id',
        ]);

        $sourceEmployee = Employee::findOrFail($validated['source_id']);
        $targetEmployee = Employee::findOrFail($validated['target_id']);

        // Get source attendance records
        $sourceRecords = \App\Models\Attendance::where('employee_id', $sourceEmployee->id)
            ->select('id', 'scanned_at', 'meal_type', 'location')
            ->get();

        // Get target attendance records
        $targetRecords = \App\Models\Attendance::where('employee_id', $targetEmployee->id)
            ->select('id', 'scanned_at', 'meal_type', 'location')
            ->get();

        // Find duplicates (same date + same meal_type)
        $duplicates = [];
        foreach ($sourceRecords as $source) {
            $sourceDate = \Carbon\Carbon::parse($source->scanned_at)->format('Y-m-d');
            foreach ($targetRecords as $target) {
                $targetDate = \Carbon\Carbon::parse($target->scanned_at)->format('Y-m-d');
                if ($sourceDate === $targetDate && $source->meal_type === $target->meal_type) {
                    $duplicates[] = [
                        'date' => $sourceDate,
                        'meal_type' => $source->meal_type,
                        'source_location' => $source->location,
                        'target_location' => $target->location,
                        'source_id' => $source->id,
                        'target_id' => $target->id,
                    ];
                }
            }
        }

        return response()->json([
            'source_name' => $sourceEmployee->name,
            'source_number' => $sourceEmployee->employee_number,
            'target_name' => $targetEmployee->name,
            'target_number' => $targetEmployee->employee_number,
            'source_count' => $sourceRecords->count(),
            'duplicates' => $duplicates,
            'duplicate_count' => count($duplicates),
        ]);
    }

    /**
     * Merge records from source employee to target employee
     */
    public function mergeRecords(Request $request)
    {
        $validated = $request->validate([
            'source_id' => 'required|exists:employees,id',
            'target_id' => 'required|exists:employees,id|different:source_id',
            'delete_source' => 'nullable|boolean',
            'duplicate_action' => 'nullable|in:skip,overwrite',
        ]);

        $sourceEmployee = Employee::findOrFail($validated['source_id']);
        $targetEmployee = Employee::findOrFail($validated['target_id']);

        // Count records before transfer
        $sourceRecords = \App\Models\Attendance::where('employee_id', $sourceEmployee->id)->get();
        $recordCount = $sourceRecords->count();

        if ($recordCount === 0) {
            return back()->with('error', 'Source employee has no meal records to transfer.');
        }

        // Get target attendance for duplicate checking
        $targetRecords = \App\Models\Attendance::where('employee_id', $targetEmployee->id)->get();

        $skippedCount = 0;
        $overwrittenCount = 0;
        $transferredCount = 0;

        foreach ($sourceRecords as $source) {
            $sourceDate = \Carbon\Carbon::parse($source->scanned_at)->format('Y-m-d');

            // Check if duplicate exists in target
            $duplicate = $targetRecords->first(function ($target) use ($sourceDate, $source) {
                $targetDate = \Carbon\Carbon::parse($target->scanned_at)->format('Y-m-d');
                return $sourceDate === $targetDate && $source->meal_type === $target->meal_type;
            });

            if ($duplicate) {
                $action = $request->input('duplicate_action', 'skip');
                if ($action === 'overwrite') {
                    // Delete target duplicate, then transfer source
                    \App\Models\Attendance::where('id', $duplicate->id)->delete();
                    $source->update(['employee_id' => $targetEmployee->id]);
                    $overwrittenCount++;
                } else {
                    // Skip - delete source record
                    $source->delete();
                    $skippedCount++;
                }
            } else {
                // No duplicate, just transfer
                $source->update(['employee_id' => $targetEmployee->id]);
                $transferredCount++;
            }
        }

        $message = "Transfer complete! ";
        if ($transferredCount > 0) {
            $message .= "{$transferredCount} records transferred. ";
        }
        if ($overwrittenCount > 0) {
            $message .= "{$overwrittenCount} duplicates overwritten. ";
        }
        if ($skippedCount > 0) {
            $message .= "{$skippedCount} duplicates skipped. ";
        }

        // Optionally delete source employee
        if ($request->input('delete_source')) {
            $sourceEmployee->delete();
            $message .= "Source employee deleted.";
        }

        return redirect()->route('employees.index')->with('success', $message);
    }

    /**
     * Get employees for merge modal (AJAX)
     */
    public function getMergeOptions(Request $request)
    {
        $search = $request->get('search', '');
        $excludeId = $request->get('exclude_id');

        $employees = Employee::where('id', '!=', $excludeId)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_number', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'employee_number', 'name', 'department', 'employee_status']);

        return response()->json($employees);
    }

    // Show export POB form
    public function exportPobForm()
    {
        $locations = ['Ramba', 'Bentayan', 'Mangunjaya', 'Keluang'];
        return view('employees.export-pob', compact('locations'));
    }

    // Export POB Schedule to Excel
    public function exportPob(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'location' => 'nullable|string',
        ]);

        $month = \Carbon\Carbon::parse($request->month . '-01');
        $daysInMonth = $month->daysInMonth;
        $location = $request->location;

        // Get attendance data for the month (filtered by eating location)
        $attendanceQuery = \App\Models\Attendance::whereBetween('scanned_at', [
            $month->copy()->startOfMonth(),
            $month->copy()->endOfMonth()
        ]);

        if ($location) {
            $attendanceQuery->where('location', $location);
        }

        $attendances = $attendanceQuery->get()
            ->groupBy('employee_id')
            ->map(function ($records) {
                return $records->groupBy(function ($item) {
                    return $item->scanned_at->format('Y-m-d');
                })->keys()->toArray();
            });

        // Get only employees who have attendance in this period (filtered by eating location)
        $employeeIds = $attendances->keys()->toArray();
        $employees = Employee::whereIn('id', $employeeIds)
            ->orderBy('department')
            ->orderBy('name')
            ->get();

        // Create spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Title
        $title = 'POB Schedule - ' . $month->format('F Y');
        if ($location)
            $title .= ' (' . $location . ')';
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(7 + $daysInMonth) . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Headers - added Accommodation column
        $headers = ['No', 'Employee ID', 'Name', 'Department', 'Location', 'Accommodation', 'Status'];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $headers[] = sprintf('%02d', $d);
        }

        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, 2, $header);
            $col++;
        }

        // Style headers
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '008080']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A2:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(7 + $daysInMonth) . '2')->applyFromArray($headerStyle);

        // Data rows
        $row = 3;
        $totals = array_fill(1, $daysInMonth, 0);

        foreach ($employees as $index => $employee) {
            $empAttendances = $attendances->get($employee->id, []);

            $sheet->setCellValueByColumnAndRow(1, $row, $index + 1);
            $sheet->setCellValueByColumnAndRow(2, $row, $employee->employee_number);
            $sheet->setCellValueByColumnAndRow(3, $row, $employee->name);
            $sheet->setCellValueByColumnAndRow(4, $row, $employee->department);
            $sheet->setCellValueByColumnAndRow(5, $row, $employee->location);

            // Handle accommodation - can be null or array
            $accommodation = $employee->accommodation;
            if (is_array($accommodation)) {
                $accommodation = implode(', ', $accommodation);
            }
            $sheet->setCellValueByColumnAndRow(6, $row, $accommodation ?? '-');

            $sheet->setCellValueByColumnAndRow(7, $row, $employee->employee_status);

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateStr = $month->copy()->day($d)->format('Y-m-d');
                $hasAttendance = in_array($dateStr, $empAttendances);

                $col = 7 + $d;
                if ($hasAttendance) {
                    $sheet->setCellValueByColumnAndRow($col, $row, 1);
                    $sheet->getStyleByColumnAndRow($col, $row)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('00FF00');
                    $totals[$d]++;
                } else {
                    $sheet->setCellValueByColumnAndRow($col, $row, '');
                }
            }

            $row++;
        }

        // Grand Total row
        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, '');
        $sheet->setCellValue('C' . $row, 'GRAND TOTAL');
        $sheet->getStyle('C' . $row)->getFont()->setBold(true);
        $sheet->mergeCells('C' . $row . ':G' . $row);
        $sheet->getStyle('A' . $row . ':' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(7 + $daysInMonth) . $row)
            ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('000000');
        $sheet->getStyle('A' . $row . ':' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(7 + $daysInMonth) . $row)
            ->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $sheet->setCellValueByColumnAndRow(7 + $d, $row, $totals[$d]);
        }

        // Auto-size columns
        for ($i = 1; $i <= 7; $i++) {
            $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
        }
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $sheet->getColumnDimensionByColumn(7 + $d)->setWidth(4);
        }

        // Download
        $filename = 'POB_Schedule_' . $month->format('Y-m') . ($location ? '_' . $location : '') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Export Absensi Makan (Daily Meal Sheet) to Excel
     * Separate sheets for each employee type: Pekerja, TA & TKJP, Contractor, Visitor
     * Max 30 employees per sheet, with pagination
     */
    public function exportAbsensiMakan(Request $request)
    {
        $request->validate([
            'location' => 'nullable|string',
        ]);

        $location = $request->location;
        $maxPerSheet = 30;

        // Define employee type groups
        $employeeGroups = [
            'Pekerja' => ['Pekerja'],
            'TA & TKJP' => ['TA', 'TKJP'],
            'Contractor' => ['Contractor'],
            'Visitor' => ['Visitor'],
        ];

        // Create spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        $isFirstSheet = true;

        foreach ($employeeGroups as $groupName => $statuses) {
            // Get employees for this group
            $query = Employee::whereIn('employee_status', $statuses)
                ->where('active_status', 'active')
                ->orderBy('name');

            if ($location) {
                $query->where('location', $location);
            }

            $employees = $query->get();

            // Skip if no employees
            if ($employees->count() === 0) {
                continue;
            }

            // Chunk employees into groups of 30
            $chunks = $employees->chunk($maxPerSheet);
            $pageNumber = 1;

            foreach ($chunks as $chunk) {
                // Create or get sheet
                if ($isFirstSheet) {
                    $sheet = $spreadsheet->getActiveSheet();
                    $isFirstSheet = false;
                } else {
                    $sheet = $spreadsheet->createSheet();
                }

                // Sheet name with page number if multiple pages
                $sheetName = $groupName;
                if ($chunks->count() > 1) {
                    $sheetName .= ' ' . $pageNumber;
                }
                $sheet->setTitle($sheetName);

                // Title row
                $title = 'Absensi Makan - ' . $groupName;
                if ($location) {
                    $title .= ' (' . $location . ')';
                }
                if ($chunks->count() > 1) {
                    $title .= ' - Halaman ' . $pageNumber;
                }
                $sheet->setCellValue('A1', $title);
                $sheet->mergeCells('A1:H1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Date row
                $sheet->setCellValue('A2', 'Tanggal: _______________');
                $sheet->mergeCells('A2:H2');

                // Headers - with separate meal columns
                $headers = ['No', 'Nama', 'Departemen', 'Breakfast', 'Lunch', 'Dinner', 'Supper', 'Snack', 'Akomodasi'];
                $col = 1;
                foreach ($headers as $header) {
                    $sheet->setCellValueByColumnAndRow($col, 4, $header);
                    $col++;
                }

                // Style headers
                $headerStyle = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FF4500']],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ];
                $sheet->getStyle('A4:I4')->applyFromArray($headerStyle);

                // Data rows
                $row = 5;
                $startIndex = ($pageNumber - 1) * $maxPerSheet;

                foreach ($chunk as $index => $employee) {
                    $sheet->setCellValueByColumnAndRow(1, $row, $startIndex + $index + 1);
                    $sheet->setCellValueByColumnAndRow(2, $row, $employee->name);
                    $sheet->setCellValueByColumnAndRow(3, $row, $employee->department ?? '-');
                    $sheet->setCellValueByColumnAndRow(4, $row, ''); // Breakfast - empty
                    $sheet->setCellValueByColumnAndRow(5, $row, ''); // Lunch - empty
                    $sheet->setCellValueByColumnAndRow(6, $row, ''); // Dinner - empty
                    $sheet->setCellValueByColumnAndRow(7, $row, ''); // Supper - empty
                    $sheet->setCellValueByColumnAndRow(8, $row, ''); // Snack - empty

                    // Handle accommodation
                    $accommodation = $employee->accommodation;
                    if (is_array($accommodation) && !empty($accommodation)) {
                        $accParts = [];
                        foreach ($accommodation as $loc => $room) {
                            if (!empty($room)) {
                                $accParts[] = strtoupper(substr($loc, 0, 1)) . ': ' . $room;
                            }
                        }
                        $accommodation = implode(', ', $accParts);
                    } else {
                        $accommodation = '-';
                    }
                    $sheet->setCellValueByColumnAndRow(9, $row, $accommodation);

                    // Apply border to data row
                    $sheet->getStyle('A' . $row . ':I' . $row)->getBorders()->getAllBorders()
                        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                    $row++;
                }

                // Total row
                $sheet->setCellValue('A' . $row, '');
                $sheet->setCellValue('B' . $row, 'Total: ' . $chunk->count() . ' orang');
                $sheet->mergeCells('B' . $row . ':I' . $row);
                $sheet->getStyle('B' . $row)->getFont()->setBold(true);

                // Auto-size columns
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $sheet->getColumnDimension('C')->setAutoSize(true);
                $sheet->getColumnDimension('D')->setWidth(10); // Breakfast
                $sheet->getColumnDimension('E')->setWidth(10); // Lunch
                $sheet->getColumnDimension('F')->setWidth(10); // Dinner
                $sheet->getColumnDimension('G')->setWidth(10); // Supper
                $sheet->getColumnDimension('H')->setWidth(10); // Snack
                $sheet->getColumnDimension('I')->setAutoSize(true); // Akomodasi

                // Set column alignment - center for No and meal columns
                $sheet->getStyle('A5:A' . ($row - 1))->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D5:H' . ($row - 1))->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $pageNumber++;
            }
        }

        // Set first sheet as active
        $spreadsheet->setActiveSheetIndex(0);

        // Download
        $filename = 'Absensi_Makan_' . now()->format('Y-m-d') . ($location ? '_' . $location : '') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
