<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\MealPrice;
use App\Exports\AttendanceExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class HistoricalController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with('employee');

        // Date filters
        if ($request->filled('start_date')) {
            $query->whereDate('scanned_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('scanned_at', '<=', $request->end_date);
        }

        // Meal type filter
        if ($request->filled('meal_type')) {
            $query->where('meal_type', $request->meal_type);
        }

        // Department filter
        if ($request->filled('department')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department', $request->department);
            });
        }

        // Location filter (where they ate, not homebase)
        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_number', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'scanned_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $allowedSorts = ['scanned_at', 'meal_type', 'location', 'scan_method'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('scanned_at', 'desc');
        }

        $attendances = $query->paginate(20)->withQueryString();

        // Get employee groups for bulk delete feature
        $groups = \App\Models\EmployeeGroup::orderBy('name')->get();

        return view('historical.index', compact('attendances', 'sortBy', 'sortDir', 'groups'));
    }

    public function exportForm()
    {
        $locations = Employee::distinct()->pluck('location')->filter()->values();
        $mealTypes = ['breakfast', 'lunch', 'dinner', 'supper', 'snack'];

        // Get previously uploaded logos
        $logoPath = storage_path('app/public/logos');
        $savedLogos = [];
        if (is_dir($logoPath)) {
            $files = scandir($logoPath);
            foreach ($files as $file) {
                if (in_array(pathinfo($file, PATHINFO_EXTENSION), ['png', 'jpg', 'jpeg'])) {
                    $savedLogos[] = [
                        'filename' => $file,
                        'path' => 'logos/' . $file,
                        'url' => asset('storage/logos/' . $file),
                    ];
                }
            }
        }

        return view('historical.export', compact('locations', 'mealTypes', 'savedLogos'));
    }

    public function export(Request $request)
    {
        // If recap export, redirect to recapExport method
        if ($request->export_type === 'recap') {
            return $this->recapExport($request);
        }

        // If daily export, redirect to dailyExport method
        if ($request->export_type === 'daily') {
            return $this->dailyExport($request);
        }

        $filters = [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'location' => $request->location,
            'meal_type' => $request->meal_type,
            'export_type' => $request->export_type ?? 'detailed', // detailed or summary
        ];

        $filename = 'meal_attendance_' . date('Y-m-d_His');

        if ($filters['export_type'] === 'summary') {
            $filename .= '_summary';
        }

        $filename .= '.xlsx';

        return Excel::download(new AttendanceExport($filters), $filename);
    }

    public function edit($id)
    {
        $attendance = Attendance::with('employee')->findOrFail($id);
        $employees = Employee::where('active_status', 'active')->orderBy('name')->get();

        // Get recent absence proof files from the last 60 days
        $recentProofs = Attendance::whereNotNull('absence_proof')
            ->where('scanned_at', '>=', now()->subDays(60))
            ->select('absence_proof')
            ->distinct()
            ->orderBy('scanned_at', 'desc')
            ->limit(100)
            ->pluck('absence_proof')
            ->map(function ($path) {
                return [
                    'path' => $path,
                    'filename' => basename($path),
                    'url' => \Storage::disk('public_direct')->url($path)
                ];
            });

        return view('historical.edit', compact('attendance', 'employees', 'recentProofs'));
    }

    /**
     * Show bulk edit form for a specific employee's meals on a specific date.
     */
    public function bulkEditForm(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $date = \Carbon\Carbon::parse($validated['date']);

        // Get all attendance records for this employee on this date
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereDate('scanned_at', $date->toDateString())
            ->orderByRaw("FIELD(meal_type, 'breakfast', 'lunch', 'dinner', 'supper', 'snack')")
            ->get();

        if ($attendances->isEmpty()) {
            return redirect()->route('historical.index')
                ->with('error', 'No attendance records found for this employee on this date.');
        }

        $employees = Employee::where('active_status', 'active')->orderBy('name')->get();

        return view('historical.bulk-edit', compact('employee', 'date', 'attendances', 'employees'));
    }

    /**
     * Process bulk edit for an employee's meals on a specific date.
     */
    public function bulkEdit(Request $request)
    {
        $validated = $request->validate([
            'original_employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'new_employee_id' => 'required|exists:employees,id',
            'meals' => 'nullable|array',
            'meals.*' => 'in:breakfast,lunch,dinner,supper,snack',
            'location' => 'required|string',
        ]);

        $date = \Carbon\Carbon::parse($validated['date']);

        // Check if date is locked (only applies to non-super_admin users)
        if (!auth()->user()->isSuperAdmin()) {
            if (\App\Models\LockedPeriod::isDateLocked($date, $validated['location'])) {
                return back()->with('error', 'Cannot edit: This date period is locked by administrator.');
            }
        }

        // Get all existing attendance records for this employee on this date
        $existingAttendances = Attendance::where('employee_id', $validated['original_employee_id'])
            ->whereDate('scanned_at', $date->toDateString())
            ->get();

        $selectedMeals = $validated['meals'] ?? [];
        $updatedCount = 0;
        $deletedCount = 0;

        foreach ($existingAttendances as $attendance) {
            if (in_array($attendance->meal_type, $selectedMeals)) {
                // Update the employee if changed
                if ($attendance->employee_id != $validated['new_employee_id']) {
                    $attendance->employee_id = $validated['new_employee_id'];
                }
                $attendance->location = $validated['location'];
                $attendance->edited_by = auth()->user()->name;
                $attendance->edited_at = now();
                $attendance->save();
                $updatedCount++;
            } else {
                // Delete unchecked meals
                $attendance->deleted_by = auth()->user()->name;
                $attendance->save();
                $attendance->delete();
                $deletedCount++;
            }
        }

        $message = "Bulk edit completed: {$updatedCount} records updated";
        if ($deletedCount > 0) {
            $message .= ", {$deletedCount} records deleted";
        }

        return redirect()->route('historical.index')
            ->with('success', $message);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'meal_type' => 'required|in:breakfast,lunch,dinner,supper,snack',
            'scanned_at' => 'required|date',
            'location' => 'required|string',
            'recorded_by' => 'nullable|string|max:255',
            'absence_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'existing_proof' => 'nullable|string',
            'apply_to_all' => 'nullable|boolean',
        ]);

        $attendance = Attendance::findOrFail($id);

        // Check if date is locked (only applies to non-super_admin users)
        if (!auth()->user()->isSuperAdmin()) {
            $attendanceDate = \Carbon\Carbon::parse($attendance->scanned_at);
            if (\App\Models\LockedPeriod::isDateLocked($attendanceDate, $attendance->location)) {
                return back()->with('error', 'Cannot edit: This date period is locked by administrator.');
            }
        }

        $oldProofPath = $attendance->absence_proof;

        // Handle proof file: either select existing or upload new
        $newProofPath = null;

        // First check if an existing proof file was selected from dropdown
        if ($request->filled('existing_proof') && $request->input('existing_proof') !== '') {
            $newProofPath = $request->input('existing_proof');
            $validated['absence_proof'] = $newProofPath;

            // Apply to all attendances with same proof if checkbox checked
            if ($request->input('apply_to_all') && $oldProofPath) {
                Attendance::where('absence_proof', $oldProofPath)
                    ->where('id', '!=', $id)
                    ->update(['absence_proof' => $newProofPath]);
            }
        }
        // Otherwise check for new file upload
        elseif ($request->hasFile('absence_proof')) {
            $file = $request->file('absence_proof');
            $extension = $file->getClientOriginalExtension();
            $date = \Carbon\Carbon::parse($validated['scanned_at']);
            $baseFilename = $date->format('Y-m-d') . '_' . $validated['location'];
            $filename = $baseFilename . '.' . $extension;

            // Check if file exists and add counter suffix if needed
            $counter = 1;
            while (\Storage::disk('public_direct')->exists('absence_proofs/' . $filename)) {
                $filename = $baseFilename . '(' . $counter . ').' . $extension;
                $counter++;
            }

            $newProofPath = $file->storeAs('absence_proofs', $filename, 'public_direct');
            $validated['absence_proof'] = $newProofPath;

            // Apply to all attendances with same proof if checkbox checked
            if ($request->input('apply_to_all') && $oldProofPath) {
                Attendance::where('absence_proof', $oldProofPath)
                    ->where('id', '!=', $id)
                    ->update(['absence_proof' => $newProofPath]);
            }
        }

        // Track who edited and when
        $validated['edited_by'] = auth()->user()->name;
        $validated['edited_at'] = now();

        // Remove non-database fields from validated before updating
        unset($validated['apply_to_all']);
        unset($validated['existing_proof']);

        $attendance->update($validated);

        return redirect()->route('historical.index')
            ->with('success', 'Attendance record updated successfully');
    }

    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);

        // Check if date is locked (only applies to non-super_admin users)
        if (!auth()->user()->isSuperAdmin()) {
            $attendanceDate = \Carbon\Carbon::parse($attendance->scanned_at);
            if (\App\Models\LockedPeriod::isDateLocked($attendanceDate, $attendance->location)) {
                return back()->with('error', 'Cannot delete: This date period is locked by administrator.');
            }
        }

        // Track who deleted before soft deleting
        $attendance->deleted_by = auth()->user()->name;
        $attendance->save();

        $attendance->delete(); // Soft delete

        return back()->with('success', 'Attendance record deleted successfully');
    }

    public function recapExport(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-d'));
        $endDate = $request->input('end_date', date('Y-m-d'));
        $location = $request->input('location', 'Ramba');
        $companyHeader = $request->input('company_header', 'PT. Brylian Indah');
        $preparedBy = $request->input('prepared_by', '');
        $preparedPosition = $request->input('prepared_position', '');
        $checkedBy = $request->input('checked_by', '');
        $checkedPosition = $request->input('checked_position', '');

        // Get meal prices from MealPrice model
        $mealPrices = MealPrice::current();
        $prices = [
            'breakfast' => (float) $mealPrices->breakfast_price,
            'lunch' => (float) $mealPrices->lunch_price,
            'dinner' => (float) $mealPrices->dinner_price,
            'supper' => (float) $mealPrices->supper_price,
            'snack' => (float) $mealPrices->snack_price,
        ];

        // Query attendance data
        $attendances = Attendance::with('employee')
            ->whereDate('scanned_at', '>=', $startDate)
            ->whereDate('scanned_at', '<=', $endDate)
            ->where('location', $location)
            ->get();

        // Group by employee status and department
        $grouped = $attendances->groupBy(function ($item) {
            return $item->employee->employee_status ?? 'UNKNOWN';
        })->map(function ($statusGroup) {
            return $statusGroup->groupBy(function ($item) {
                return $item->employee->department ?? 'N/A';
            });
        });

        // Create Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Handle logo - use selected logo or save new upload
        $logoPath = null;
        $selectedLogo = $request->input('selected_logo');

        // Ensure logos directory exists
        $logosDir = storage_path('app/public/logos');
        if (!is_dir($logosDir)) {
            mkdir($logosDir, 0755, true);
        }

        if ($request->hasFile('logo')) {
            // New logo uploaded - save it first
            $uploadedLogo = $request->file('logo');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $uploadedLogo->getClientOriginalName());
            $uploadedLogo->move($logosDir, $filename);
            $logoPath = $logosDir . '/' . $filename;
        } elseif ($selectedLogo && $selectedLogo !== 'new') {
            // Use selected logo from gallery
            $logoPath = storage_path('app/public/' . $selectedLogo);
        }

        // Add logo in top right corner (H3) if exists
        if ($logoPath && file_exists($logoPath)) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Logo');
            $drawing->setDescription('Company Logo');
            $drawing->setPath($logoPath);
            $drawing->setCoordinates('H3'); // Top right inside border
            $drawing->setHeight(60);
            $drawing->setOffsetX(10);
            $drawing->setWorksheet($sheet);
        }

        // Row 4: Header: TOTAL MEAL in C4:H4 (centered)
        $sheet->mergeCells("C4:H4");
        $sheet->setCellValue("C4", 'TOTAL MEAL');
        $sheet->getStyle("C4")->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle("C4")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Row 6: Provider header in C6 (left aligned)
        $sheet->setCellValue("C6", "Provider : " . $companyHeader);
        $sheet->getStyle("C6")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("C6")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Row 7: Location header (left aligned)
        $sheet->setCellValue("C7", "Location : " . $location);
        $sheet->getStyle("C7")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("C7")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Row 8: Date header (left aligned)
        $dateLabel = ($startDate == $endDate)
            ? "Tanggal: " . date('d F Y', strtotime($startDate))
            : "Periode: " . date('d F Y', strtotime($startDate)) . " - " . date('d F Y', strtotime($endDate));
        $sheet->setCellValue("C8", $dateLabel);
        $sheet->getStyle("C8")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("C8")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Row 9: Total Invoice (left aligned)
        // Calculate total invoice from attendances
        $totalInvoice = 0;
        foreach ($attendances as $att) {
            $mealType = strtolower($att->meal_type);
            if (isset($prices[$mealType])) {
                $totalInvoice += $prices[$mealType];
            }
        }
        $sheet->setCellValue("C9", "Total Invoice: Rp " . number_format($totalInvoice, 0, ',', '.'));
        $sheet->getStyle("C9")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("C9")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Start data from row 11
        $row = 11;

        // Department list (kept for reference, but we iterate actual departments)
        $departments = ['FM', 'PO', 'ICT', 'SCM', 'GS', 'RAM', 'HSSE'];


        // Define status order mapping (handle variations)
        $statusOrderMap = [
            'Pekerja' => 1,
            'PEKERJA' => 1,
            'TA' => 2,
            'TA/TKJP' => 2,
            'TKJP' => 2,
            'TA & TKJP' => 2,
            'Contractor' => 3,
            'CONTRACTOR' => 3,
            'Visitor' => 4,
            'VISITOR' => 4,
        ];

        // Merge TA and TKJP data together
        $mergedGrouped = collect();
        $taAndTkjpData = collect();

        foreach ($grouped as $status => $departments) {
            if (in_array($status, ['TA', 'TKJP', 'TA/TKJP'])) {
                // Collect TA and TKJP data together
                foreach ($departments as $dept => $attendances) {
                    if (!isset($taAndTkjpData[$dept])) {
                        $taAndTkjpData[$dept] = collect();
                    }
                    $taAndTkjpData[$dept] = $taAndTkjpData[$dept]->merge($attendances);
                }
            } else {
                $mergedGrouped[$status] = $departments;
            }
        }

        // Add merged TA & TKJP if we found any
        if ($taAndTkjpData->isNotEmpty()) {
            $mergedGrouped['TA & TKJP'] = $taAndTkjpData;
        }

        // Sort grouped data by status order
        $sortedGrouped = $mergedGrouped->sort(function ($a, $b) use ($statusOrderMap, $mergedGrouped) {
            $statusA = $mergedGrouped->search($a);
            $statusB = $mergedGrouped->search($b);

            $orderA = $statusOrderMap[$statusA] ?? 999;
            $orderB = $statusOrderMap[$statusB] ?? 999;

            return $orderA <=> $orderB;
        });

        // Track the first data row for thick outside border
        $dataStartRow = $row;

        // Iterate over sorted employee statuses
        foreach ($sortedGrouped as $status => $statusDepartments) {

            $row++; // Empty row before section

            // Status header (Light grey background) - shifted to C:H
            $sheet->mergeCells("C{$row}:H{$row}");
            $sheet->setCellValue("C{$row}", strtoupper($status));
            $sheet->getStyle("C{$row}")->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFD3D3D3');
            $sheet->getStyle("C{$row}")->getFont()->setBold(true);
            $row++;

            // Column headers - Row 1: Meal names (shifted to C:H)
            $headerRow = $row;
            $sheet->setCellValue("C{$row}", 'Department');
            $sheet->setCellValue("D{$row}", 'Breakfast');
            $sheet->setCellValue("E{$row}", 'Lunch');
            $sheet->setCellValue("F{$row}", 'Dinner');
            $sheet->setCellValue("G{$row}", 'Supper');
            $sheet->setCellValue("H{$row}", 'Snack');
            $sheet->getStyle("C{$row}:H{$row}")->getFont()->setBold(true);
            $row++;

            // Column headers - Row 2: Actual prices (shifted to D:H)
            $priceRow = $row;
            $sheet->setCellValue("D{$row}", $prices['breakfast']);
            $sheet->setCellValue("E{$row}", $prices['lunch']);
            $sheet->setCellValue("F{$row}", $prices['dinner']);
            $sheet->setCellValue("G{$row}", $prices['supper']);
            $sheet->setCellValue("H{$row}", $prices['snack']);
            $sheet->getStyle("D{$row}:H{$row}")->getFont()->setItalic(true);
            $sheet->getStyle("D{$row}:H{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');

            // Merge Department cell vertically across both header rows
            $sheet->mergeCells("C{$headerRow}:C{$priceRow}");
            $sheet->getStyle("C{$headerRow}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $row++;

            // Track where this section's data starts (for thin borders)
            $sectionStartRow = $headerRow;

            // Track totals for this status
            $statusTotals = [
                'breakfast_count' => 0,
                'lunch_count' => 0,
                'dinner_count' => 0,
                'supper_count' => 0,
                'snack_count' => 0,
                'breakfast_price' => 0,
                'lunch_price' => 0,
                'dinner_price' => 0,
                'supper_price' => 0,
                'snack_price' => 0,
            ];


            // Department rows - iterate over actual departments that have data
            foreach ($statusDepartments as $dept => $deptAttendances) {

                $counts = [
                    'breakfast' => $deptAttendances->where('meal_type', 'breakfast')->count(),
                    'lunch' => $deptAttendances->where('meal_type', 'lunch')->count(),
                    'dinner' => $deptAttendances->where('meal_type', 'dinner')->count(),
                    'supper' => $deptAttendances->where('meal_type', 'supper')->count(),
                    'snack' => $deptAttendances->where('meal_type', 'snack')->count(),
                ];

                // Skip department if no meals
                if (array_sum($counts) === 0) {
                    continue;
                }

                // Department row - counts only (0 shown as -) - shifted to C:H
                $sheet->setCellValue("C{$row}", $dept);
                $sheet->setCellValue("D{$row}", $counts['breakfast'] ?: '-');
                $sheet->setCellValue("E{$row}", $counts['lunch'] ?: '-');
                $sheet->setCellValue("F{$row}", $counts['dinner'] ?: '-');
                $sheet->setCellValue("G{$row}", $counts['supper'] ?: '-');
                $sheet->setCellValue("H{$row}", $counts['snack'] ?: '-');
                $row++;

                // Add to totals
                $statusTotals['breakfast_count'] += $counts['breakfast'];
                $statusTotals['lunch_count'] += $counts['lunch'];
                $statusTotals['dinner_count'] += $counts['dinner'];
                $statusTotals['supper_count'] += $counts['supper'];
                $statusTotals['snack_count'] += $counts['snack'];
                $statusTotals['breakfast_price'] += $counts['breakfast'] * $prices['breakfast'];
                $statusTotals['lunch_price'] += $counts['lunch'] * $prices['lunch'];
                $statusTotals['dinner_price'] += $counts['dinner'] * $prices['dinner'];
                $statusTotals['supper_price'] += $counts['supper'] * $prices['supper'];
                $statusTotals['snack_price'] += $counts['snack'] * $prices['snack'];
            }

            // Status totals row (0 shown as -) - shifted to C:H
            $sheet->setCellValue("C{$row}", 'Total Person');
            $sheet->setCellValue("D{$row}", $statusTotals['breakfast_count'] ?: '-');
            $sheet->setCellValue("E{$row}", $statusTotals['lunch_count'] ?: '-');
            $sheet->setCellValue("F{$row}", $statusTotals['dinner_count'] ?: '-');
            $sheet->setCellValue("G{$row}", $statusTotals['supper_count'] ?: '-');
            $sheet->setCellValue("H{$row}", $statusTotals['snack_count'] ?: '-');
            $sheet->getStyle("C{$row}:H{$row}")->getFont()->setBold(true);
            $row++;

            // Total price row - shifted to C:H
            $sheet->setCellValue("C{$row}", 'Total Price');
            $sheet->setCellValue("D{$row}", $statusTotals['breakfast_price'] ?: '-');
            $sheet->setCellValue("E{$row}", $statusTotals['lunch_price'] ?: '-');
            $sheet->setCellValue("F{$row}", $statusTotals['dinner_price'] ?: '-');
            $sheet->setCellValue("G{$row}", $statusTotals['supper_price'] ?: '-');
            $sheet->setCellValue("H{$row}", $statusTotals['snack_price'] ?: '-');
            $sheet->getStyle("C{$row}:H{$row}")->getFont()->setBold(true);
            // Format as currency with Rp prefix
            $sheet->getStyle("D{$row}:H{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');

            // Apply thin borders to this status section's data area
            $sectionEndRow = $row;
            $sheet->getStyle("C{$sectionStartRow}:H{$sectionEndRow}")->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            $row++;
        }

        // Footer - Prepared By and Checked By
        $row += 1; // Add 1 row space

        // Footer - Prepared By in C, Checked By in G (shifted)
        $sheet->setCellValue("C{$row}", 'Prepared By:');
        $sheet->setCellValue("G{$row}", 'Checked By:');
        $sheet->getStyle("C{$row}")->getFont()->setBold(true);
        $sheet->getStyle("G{$row}")->getFont()->setBold(true);
        $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $row++;
        $row++; // Empty row 1 (for signature space)
        $row++; // Empty row 2 (for signature space)

        // Names (shifted to C and G)
        $sheet->setCellValue("C{$row}", $preparedBy);
        $sheet->setCellValue("G{$row}", $checkedBy);
        $sheet->getStyle("C{$row}")->getFont()->setBold(true)->setUnderline(\PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE);
        $sheet->getStyle("G{$row}")->getFont()->setBold(true)->setUnderline(\PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE);
        $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $row++;

        // Positions (shifted to C and G)
        $positionRow = $row;
        $sheet->setCellValue("C{$row}", $preparedPosition);
        $sheet->setCellValue("G{$row}", $checkedPosition);
        $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Thick outside border end row (1 row below position, reduced from 2)
        $borderEndRow = $positionRow + 1;

        // Apply thick outside border for B:I from row 2
        $sheet->getStyle("B2:I{$borderEndRow}")->getBorders()->getOutline()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

        // Center align all content in C:H
        $sheet->getStyle("C1:H" . ($row))->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Re-apply left alignment to provider, location, date, and total invoice
        $sheet->getStyle("C6")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("C7")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("C8")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("C9")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Re-apply left alignment to Prepared By / Checked By footer area
        for ($footerRow = $positionRow - 4; $footerRow <= $positionRow; $footerRow++) {
            $sheet->getStyle("C{$footerRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("G{$footerRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        }

        // Set column widths: A,B,I,J,K = 4, C:H = 15
        $sheet->getColumnDimension('A')->setWidth(4);
        $sheet->getColumnDimension('B')->setWidth(4);
        foreach (range('C', 'H') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('I')->setWidth(4);
        $sheet->getColumnDimension('J')->setWidth(4);
        $sheet->getColumnDimension('K')->setWidth(4);

        // Page setup for printing with centering
        $sheet->getPageSetup()
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT)
            ->setFitToWidth(1)
            ->setFitToHeight(1) // Fit on one page
            ->setHorizontalCentered(true)  // Center horizontally on page
            ->setVerticalCentered(true);   // Center vertically on page

        // Set print area to match thick border area (B2:I)
        $sheet->getPageSetup()->setPrintArea("B2:I{$borderEndRow}");

        // Set narrow margins (in inches): Top/Bottom 0.35 (0.9cm), Left/Right 0.25, Header/Footer 0.3
        $sheet->getPageMargins()
            ->setTop(0.35)
            ->setRight(0.25)
            ->setBottom(0.35)
            ->setLeft(0.25)
            ->setHeader(0.3)
            ->setFooter(0.3);

        // Download
        $filename = "Meal_Recap_{$location}_" . date('Ymd', strtotime($startDate)) . ".xlsx";
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function dailyExport(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-d'));
        $endDate = $request->input('end_date', date('Y-m-d'));
        $location = $request->input('location', '') ?: 'All';

        // Query attendance data for the date range - include employee's groups
        $query = Attendance::with(['employee', 'employee.groups'])
            ->whereDate('scanned_at', '>=', $startDate)
            ->whereDate('scanned_at', '<=', $endDate);

        if ($location !== 'All') {
            $query->where('location', $location);
        }

        $attendances = $query->orderBy('scanned_at')->get();

        // Group by employee AND date
        $employeeAttendance = [];
        foreach ($attendances as $attendance) {
            $empId = $attendance->employee_id;
            $attendanceDate = date('Y-m-d', strtotime($attendance->scanned_at));
            $key = $empId . '_' . $attendanceDate;

            if (!isset($employeeAttendance[$key])) {
                // Get the first group name and order for this employee
                $groupName = '';
                $groupMemberOrder = 999; // Default high order for employees not in any group
                if ($attendance->employee && $attendance->employee->groups->count() > 0) {
                    $firstGroup = $attendance->employee->groups->first();
                    $groupName = $firstGroup->name ?? '';
                    $groupMemberOrder = $firstGroup->pivot->order ?? 999;
                }

                $employeeAttendance[$key] = [
                    'employee' => $attendance->employee,
                    'date' => $attendanceDate,
                    'group_name' => $groupName,
                    'group_member_order' => $groupMemberOrder,
                    'breakfast' => false,
                    'lunch' => false,
                    'dinner' => false,
                    'supper' => false,
                    'snack' => false,
                ];
            }
            $mealType = $attendance->meal_type;
            if (isset($employeeAttendance[$key][$mealType])) {
                $employeeAttendance[$key][$mealType] = true;
            }
        }

        // Custom group order: Pekerja, TA, TKJP, Contractor, Visitor
        $groupOrder = ['Pekerja' => 1, 'TA' => 2, 'TKJP' => 3, 'Contractor' => 4, 'Visitor' => 5];

        // Helper function to extract group type from group name like "Ramba-Pekerja-1"
        $getGroupOrder = function ($groupName) use ($groupOrder) {
            if (empty($groupName))
                return 999;
            foreach ($groupOrder as $group => $order) {
                if (stripos($groupName, $group) !== false) {
                    return $order;
                }
            }
            return 999; // Unknown groups at the end
        };

        // Sort by date, then by custom group type order, then by group name, then by member order within group
        usort($employeeAttendance, function ($a, $b) use ($getGroupOrder) {
            $dateCompare = strcmp($a['date'], $b['date']);
            if ($dateCompare !== 0)
                return $dateCompare;
            // Sort by custom group type order (Pekerja, TA, TKJP, etc)
            $groupOrderA = $getGroupOrder($a['group_name'] ?? '');
            $groupOrderB = $getGroupOrder($b['group_name'] ?? '');
            if ($groupOrderA !== $groupOrderB)
                return $groupOrderA - $groupOrderB;
            // Sort by full group_name for number ordering (Ramba-Pekerja-1, Ramba-Pekerja-2, etc)
            $groupCompare = strcmp($a['group_name'] ?? '', $b['group_name'] ?? '');
            if ($groupCompare !== 0)
                return $groupCompare;
            // Sort by member order within the group (the pivot order)
            return ($a['group_member_order'] ?? 999) - ($b['group_member_order'] ?? 999);
        });

        // Create Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Get form inputs for footer
        $companyHeader = $request->input('company_header', 'PT. Brylian Indah');
        $preparedBy = $request->input('prepared_by', '');
        $preparedPosition = $request->input('prepared_position', '');
        $checkedBy = $request->input('checked_by', '');
        $checkedPosition = $request->input('checked_position', '');

        // Handle logo - same as Recap export
        $logoPath = null;
        $selectedLogo = $request->input('selected_logo');

        $logosDir = storage_path('app/public/logos');
        if (!is_dir($logosDir)) {
            mkdir($logosDir, 0755, true);
        }

        if ($request->hasFile('logo')) {
            $uploadedLogo = $request->file('logo');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $uploadedLogo->getClientOriginalName());
            $uploadedLogo->move($logosDir, $filename);
            $logoPath = $logosDir . '/' . $filename;
        } elseif ($selectedLogo && $selectedLogo !== 'new') {
            $logoPath = storage_path('app/public/' . $selectedLogo);
        }

        // Add logo in top right corner (K3) if exists
        if ($logoPath && file_exists($logoPath)) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Logo');
            $drawing->setDescription('Company Logo');
            $drawing->setPath($logoPath);
            $drawing->setCoordinates('K3');
            $drawing->setHeight(60);
            $drawing->setOffsetX(10);
            $drawing->setWorksheet($sheet);
        }

        // Row 3: Header: DAILY MEAL SHEET in C3:L3 (centered)
        $sheet->mergeCells("C3:L3");
        $sheet->setCellValue("C3", 'DAILY MEAL SHEET');
        $sheet->getStyle("C3")->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle("C3")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Row 5: Location header
        $sheet->setCellValue("C5", "Location : " . $location);
        $sheet->getStyle("C5")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("C5")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Start table from row 7
        $row = 7;

        // Table headers with Date column
        $sheet->setCellValue("C{$row}", 'No');
        $sheet->setCellValue("D{$row}", 'Date');
        $sheet->setCellValue("E{$row}", 'Name');
        $sheet->setCellValue("F{$row}", 'Department');
        $sheet->setCellValue("G{$row}", 'Status');
        $sheet->setCellValue("H{$row}", 'Breakfast');
        $sheet->setCellValue("I{$row}", 'Lunch');
        $sheet->setCellValue("J{$row}", 'Dinner');
        $sheet->setCellValue("K{$row}", 'Supper');
        $sheet->setCellValue("L{$row}", 'Snack');
        $sheet->getStyle("C{$row}:L{$row}")->getFont()->setBold(true);
        $sheet->getStyle("C{$row}:L{$row}")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD3D3D3');
        // Center alignment for headers
        $sheet->getStyle("C{$row}:L{$row}")->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $headerRow = $row;
        $row++;

        // Data rows
        $no = 1;
        foreach ($employeeAttendance as $data) {
            $employee = $data['employee'];
            $formattedDate = date('d/m/Y', strtotime($data['date']));
            $sheet->setCellValue("C{$row}", $no);
            $sheet->setCellValue("D{$row}", $formattedDate);
            $sheet->setCellValue("E{$row}", $employee->name ?? '-');
            $sheet->setCellValue("F{$row}", $employee->department ?? '-');
            $sheet->setCellValue("G{$row}", $employee->employee_status ?? '-');
            $sheet->setCellValue("H{$row}", $data['breakfast'] ? '✓' : '');
            $sheet->setCellValue("I{$row}", $data['lunch'] ? '✓' : '');
            $sheet->setCellValue("J{$row}", $data['dinner'] ? '✓' : '');
            $sheet->setCellValue("K{$row}", $data['supper'] ? '✓' : '');
            $sheet->setCellValue("L{$row}", $data['snack'] ? '✓' : '');

            // Center alignment for all data columns except Name (E)
            $sheet->getStyle("C{$row}:D{$row}")->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$row}")->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("F{$row}:L{$row}")->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $no++;
            $row++;
        }

        $lastDataRow = $row - 1;

        // Apply thin borders to table
        $sheet->getStyle("C{$headerRow}:L{$lastDataRow}")->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Footer - Prepared By and Checked By (1 row gap)
        $row += 1;

        $sheet->setCellValue("C{$row}", 'Prepared By:');
        $sheet->setCellValue("J{$row}", 'Checked By:');
        $sheet->getStyle("C{$row}")->getFont()->setBold(true);
        $sheet->getStyle("J{$row}")->getFont()->setBold(true);
        $row++;
        $row++; // Empty row for signature
        $row++;

        // Names
        $sheet->setCellValue("C{$row}", $preparedBy);
        $sheet->setCellValue("J{$row}", $checkedBy);
        $sheet->getStyle("C{$row}")->getFont()->setBold(true)->setUnderline(\PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE);
        $sheet->getStyle("J{$row}")->getFont()->setBold(true)->setUnderline(\PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE);
        $row++;

        // Positions
        $positionRow = $row;
        $sheet->setCellValue("C{$row}", $preparedPosition);
        $sheet->setCellValue("J{$row}", $checkedPosition);

        // Calculate end row for print area (1 row below position)
        $printEndRow = $positionRow + 1;

        // Set column widths: A,B = 4, C:L = 12, M = 4
        $sheet->getColumnDimension('A')->setWidth(4);
        $sheet->getColumnDimension('B')->setWidth(4);
        $sheet->getColumnDimension('C')->setWidth(6);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(12);
        foreach (range('H', 'L') as $col) {
            $sheet->getColumnDimension($col)->setWidth(10);
        }
        $sheet->getColumnDimension('M')->setWidth(4);

        // Page setup
        $sheet->getPageSetup()
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT)
            ->setFitToWidth(1)
            ->setFitToHeight(0)
            ->setHorizontalCentered(true)
            ->setVerticalCentered(false);

        // Set print area
        $sheet->getPageSetup()->setPrintArea("B2:M{$printEndRow}");

        // Set print titles - repeat rows 2:7 at top of each page
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(2, 7);

        // Set narrow margins
        $sheet->getPageMargins()
            ->setTop(0.35)
            ->setRight(0.25)
            ->setBottom(0.35)
            ->setLeft(0.25)
            ->setHeader(0.3)
            ->setFooter(0.3);

        // Download - Format: Location_Daily_Meal Sheet_YYYYMMDD.xlsx
        $locationName = str_replace(' ', '_', $location);
        $filename = "{$locationName}_Daily_Meal_Sheet_" . date('Ymd', strtotime($startDate)) . ".xlsx";
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function recapPDF(Request $request)
    {
        // Get  parameters (same as recapExport)
        $startDate = $request->input('start_date', date('Y-m-d'));
        $endDate = $request->input('end_date', date('Y-m-d'));
        $location = $request->input('location', 'Ramba');
        $companyHeader = $request->input('company_header', 'PT. Brylian Indah');
        $preparedBy = $request->input('prepared_by', '');
        $preparedPosition = $request->input('prepared_position', 'Camp Boss');
        $checkedBy = $request->input('checked_by', 'Dedy B. / Rai A. / Marnita');
        $checkedPosition = $request->input('checked_position', 'GS Ramba');

        // Get meal prices
        $mealPrices = MealPrice::current();
        $prices = [
            'breakfast' => (float) $mealPrices->breakfast_price,
            'lunch' => (float) $mealPrices->lunch_price,
            'dinner' => (float) $mealPrices->dinner_price,
            'supper' => (float) $mealPrices->supper_price,
            'snack' => (float) $mealPrices->snack_price,
        ];

        // Query data
        $attendances = Attendance::with('employee')
            ->whereDate('scanned_at', '>=', $startDate)
            ->whereDate('scanned_at', '<=', $endDate)
            ->where('location', $location)
            ->get();

        // Group by employee status then department
        $grouped = $attendances->groupBy(function ($attendance) {
            return $attendance->employee->employee_status ?? 'Unknown';
        })->map(function ($statusGroup) {
            return $statusGroup->groupBy(function ($attendance) {
                return $attendance->employee->department ?? 'Unknown';
            });
        });

        // Merge TA and TKJP
        $mergedGrouped = collect();
        $taAndTkjpData = collect();

        foreach ($grouped as $status => $departments) {
            if (in_array($status, ['TA', 'TKJP', 'TA/TKJP'])) {
                foreach ($departments as $dept => $attendances) {
                    if (!isset($taAndTkjpData[$dept])) {
                        $taAndTkjpData[$dept] = collect();
                    }
                    $taAndTkjpData[$dept] = $taAndTkjpData[$dept]->merge($attendances);
                }
            } else {
                $mergedGrouped[$status] = $departments;
            }
        }

        if ($taAndTkjpData->isNotEmpty()) {
            $mergedGrouped['TA & TKJP'] = $taAndTkjpData;
        }

        // Sort by status order
        $statusOrderMap = ['Pekerja' => 1, 'PEKERJA' => 1, 'TA & TKJP' => 2, 'TA' => 2, 'TKJP' => 2, 'Contractor' => 3, 'CONTRACTOR' => 3, 'Visitor' => 4, 'VISITOR' => 4];
        $sortedGrouped = $mergedGrouped->sort(function ($a, $b) use ($statusOrderMap, $mergedGrouped) {
            $statusA = $mergedGrouped->search($a);
            $statusB = $mergedGrouped->search($b);
            $orderA = $statusOrderMap[$statusA] ?? 999;
            $orderB = $statusOrderMap[$statusB] ?? 999;
            return $orderA <=> $orderB;
        });

        $dateLabel = ($startDate == $endDate)
            ? "Tanggal: " . date('d F Y', strtotime($startDate))
            : "Periode: " . date('d F Y', strtotime($startDate)) . " - " . date('d F Y', strtotime($endDate));

        // Generate PDF
        $pdf = Pdf::loadView('historical.recap-pdf', compact(
            'sortedGrouped',
            'prices',
            'location',
            'dateLabel',
            'companyHeader',
            'preparedBy',
            'preparedPosition',
            'checkedBy',
            'checkedPosition'
        ));

        $pdf->setPaper('a4', 'portrait');

        $filename = "Meal_Recap_{$location}_" . date('Ymd', strtotime($startDate)) . ".pdf";
        return $pdf->download($filename);
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'delete_date' => 'required|date',
            'delete_location' => 'nullable|string',
            'delete_group_id' => 'nullable|exists:employee_groups,id',
        ]);

        // Check if date is locked (only applies to non-super_admin users)
        if (!auth()->user()->isSuperAdmin()) {
            $deleteDate = \Carbon\Carbon::parse($validated['delete_date']);
            $location = $validated['delete_location'] ?? null;
            if (\App\Models\LockedPeriod::isDateLocked($deleteDate, $location)) {
                return back()->with('error', 'Cannot bulk delete: This date period is locked by administrator.');
            }
        }

        $query = Attendance::whereDate('scanned_at', $validated['delete_date']);

        // Filter by location if specified
        if (!empty($validated['delete_location'])) {
            $query->where('location', $validated['delete_location']);
        }

        // Filter by employee group if specified
        if (!empty($validated['delete_group_id'])) {
            $group = \App\Models\EmployeeGroup::with('employees')->find($validated['delete_group_id']);
            if ($group) {
                $employeeIds = $group->employees->pluck('id')->toArray();
                $query->whereIn('employee_id', $employeeIds);
            }
        }

        // Get count before delete for feedback
        $count = $query->count();

        // Soft delete the records
        $deleted = $query->delete();

        $message = "Successfully deleted {$count} attendance records";
        if (!empty($validated['delete_location'])) {
            $message .= " from {$validated['delete_location']}";
        }
        $message .= " on {$validated['delete_date']}";

        return back()->with('success', $message);
    }

    public function getAbsenceProofs(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));
        $location = $request->input('location', '');

        $query = Attendance::with('employee')
            ->whereDate('scanned_at', '>=', $startDate)
            ->whereDate('scanned_at', '<=', $endDate)
            ->whereNotNull('absence_proof')
            ->where('absence_proof', '!=', '');

        if ($location) {
            $query->where('location', $location);
        }

        // Get unique absence_proof values
        $attendances = $query->orderBy('scanned_at', 'desc')->get();

        // Filter to get only unique absence_proof paths
        $uniqueProofs = $attendances->unique('absence_proof')->values();

        return response()->json($uniqueProofs);
    }

    public function downloadAbsenceProofs(Request $request)
    {
        $ids = $request->input('attendance_ids', []);

        if (empty($ids)) {
            return back()->with('error', 'No attendance records selected.');
        }

        // Get attendances with absence_proof
        $attendances = Attendance::with('employee')
            ->whereIn('id', $ids)
            ->whereNotNull('absence_proof')
            ->where('absence_proof', '!=', '')
            ->get();

        if ($attendances->isEmpty()) {
            return back()->with('error', 'No absence proofs found for selected records.');
        }

        // Create zip file
        $zipFileName = 'absence_proofs_' . date('Ymd_His') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        // Ensure temp directory exists
        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Failed to create zip file.');
        }

        $filesAdded = 0;
        foreach ($attendances as $attendance) {
            $proofPath = storage_path('app/public/' . $attendance->absence_proof);

            if (file_exists($proofPath)) {
                // Use original filename from the path
                $zipEntryName = basename($attendance->absence_proof);

                // Handle duplicate filenames
                $counter = 1;
                $originalName = $zipEntryName;
                $extension = pathinfo($proofPath, PATHINFO_EXTENSION);
                $baseName = pathinfo($originalName, PATHINFO_FILENAME);
                while ($zip->locateName($zipEntryName) !== false) {
                    $zipEntryName = "{$baseName}_{$counter}.{$extension}";
                    $counter++;
                }

                $zip->addFile($proofPath, $zipEntryName);
                $filesAdded++;
            }
        }

        $zip->close();

        if ($filesAdded === 0) {
            unlink($zipPath);
            return back()->with('error', 'No valid absence proof files found.');
        }

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }
}
