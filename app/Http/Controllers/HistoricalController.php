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

        return view('historical.index', compact('attendances', 'sortBy', 'sortDir'));
    }

    public function exportForm()
    {
        $locations = Employee::distinct()->pluck('location')->filter()->values();
        $mealTypes = ['breakfast', 'lunch', 'dinner', 'supper', 'snack'];

        return view('historical.export', compact('locations', 'mealTypes'));
    }

    public function export(Request $request)
    {
        // If recap export, redirect to recapExport method
        if ($request->export_type === 'recap') {
            return $this->recapExport($request);
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

        return view('historical.edit', compact('attendance', 'employees'));
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
            'apply_to_all' => 'nullable|boolean',
        ]);

        $attendance = Attendance::findOrFail($id);
        $oldProofPath = $attendance->absence_proof;

        // Handle file upload if present
        $newProofPath = null;
        if ($request->hasFile('absence_proof')) {
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

        // Remove apply_to_all from validated before updating
        unset($validated['apply_to_all']);

        $attendance->update($validated);

        return redirect()->route('historical.index')
            ->with('success', 'Attendance record updated successfully');
    }

    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);

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

        $row = 1;
        $row++; // Empty row at top

        // Header: TOTAL MEAL (no background, center aligned)
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL MEAL');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $row++;
        $row++; // Empty row

        // Provider header (no background, left aligned)
        $sheet->setCellValue("A{$row}", "Provider : " . $companyHeader);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $row++;

        // Location header (no background, left aligned)
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("A{$row}", "Location : " . $location);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $row++;

        // Date header (no background, left aligned)
        $sheet->mergeCells("A{$row}:F{$row}");
        $dateLabel = ($startDate == $endDate)
            ? "Tanggal: " . date('d F Y', strtotime($startDate))
            : "Periode: " . date('d F Y', strtotime($startDate)) . " - " . date('d F Y', strtotime($endDate));
        $sheet->setCellValue("A{$row}", $dateLabel);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $row++;

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

        // Iterate over sorted employee statuses
        foreach ($sortedGrouped as $status => $statusDepartments) {

            $row++; // Empty row before section

            // Status header (Light grey background)
            $sheet->mergeCells("A{$row}:F{$row}");
            $sheet->setCellValue("A{$row}", strtoupper($status));
            $sheet->getStyle("A{$row}")->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFD3D3D3');
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row++;

            // Column headers - Row 1: Meal names
            $headerRow = $row;
            $sheet->setCellValue("A{$row}", 'Department');
            $sheet->setCellValue("B{$row}", 'Breakfast');
            $sheet->setCellValue("C{$row}", 'Lunch');
            $sheet->setCellValue("D{$row}", 'Dinner');
            $sheet->setCellValue("E{$row}", 'Supper');
            $sheet->setCellValue("F{$row}", 'Snack');
            $sheet->getStyle("A{$row}:F{$row}")->getFont()->setBold(true);
            $row++;

            // Column headers - Row 2: Actual prices (numeric format for summing)
            $priceRow = $row;
            $sheet->setCellValue("B{$row}", $prices['breakfast']);
            $sheet->setCellValue("C{$row}", $prices['lunch']);
            $sheet->setCellValue("D{$row}", $prices['dinner']);
            $sheet->setCellValue("E{$row}", $prices['supper']);
            $sheet->setCellValue("F{$row}", $prices['snack']);
            $sheet->getStyle("B{$row}:F{$row}")->getFont()->setItalic(true);
            $sheet->getStyle("B{$row}:F{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');

            // Merge Department cell vertically across both header rows
            $sheet->mergeCells("A{$headerRow}:A{$priceRow}");
            $sheet->getStyle("A{$headerRow}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $row++;

            // Track where this section's data starts (for borders)
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

                // Department row - counts only (0 shown as -)
                $sheet->setCellValue("A{$row}", $dept);
                $sheet->setCellValue("B{$row}", $counts['breakfast'] ?: '-');
                $sheet->setCellValue("C{$row}", $counts['lunch'] ?: '-');
                $sheet->setCellValue("D{$row}", $counts['dinner'] ?: '-');
                $sheet->setCellValue("E{$row}", $counts['supper'] ?: '-');
                $sheet->setCellValue("F{$row}", $counts['snack'] ?: '-');
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

            // Status totals row (0 shown as -)
            $sheet->setCellValue("A{$row}", 'Total Person');
            $sheet->setCellValue("B{$row}", $statusTotals['breakfast_count'] ?: '-');
            $sheet->setCellValue("C{$row}", $statusTotals['lunch_count'] ?: '-');
            $sheet->setCellValue("D{$row}", $statusTotals['dinner_count'] ?: '-');
            $sheet->setCellValue("E{$row}", $statusTotals['supper_count'] ?: '-');
            $sheet->setCellValue("F{$row}", $statusTotals['snack_count'] ?: '-');
            $sheet->getStyle("A{$row}:F{$row}")->getFont()->setBold(true);
            $row++;

            // Total price row - use numbers so Excel can sum them
            $sheet->setCellValue("A{$row}", 'Total Price');
            $sheet->setCellValue("B{$row}", $statusTotals['breakfast_price'] ?: '-');
            $sheet->setCellValue("C{$row}", $statusTotals['lunch_price'] ?: '-');
            $sheet->setCellValue("D{$row}", $statusTotals['dinner_price'] ?: '-');
            $sheet->setCellValue("E{$row}", $statusTotals['supper_price'] ?: '-');
            $sheet->setCellValue("F{$row}", $statusTotals['snack_price'] ?: '-');
            $sheet->getStyle("A{$row}:F{$row}")->getFont()->setBold(true);
            // Format as currency with Rp prefix (allows Excel to sum)
            $sheet->getStyle("B{$row}:F{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');

            // Apply borders only to this status section's data area
            $sectionEndRow = $row;
            $sheet->getStyle("A{$sectionStartRow}:F{$sectionEndRow}")->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            $row++;
        }


        // Center align all content
        $sheet->getStyle("A1:F" . ($row - 1))->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Re-apply left alignment to provider (row 4), location (row 5), and date (row 6)
        $sheet->getStyle("A4")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("A5")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("A6")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Set fixed column width to 15 for all columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }

        // Page setup for printing
        $sheet->getPageSetup()
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT)
            ->setFitToWidth(1)
            ->setFitToHeight(1); // Fit on one page

        // Set print area to columns A-F
        $lastRow = $row + 10; // Add buffer for footer
        $sheet->getPageSetup()->setPrintArea("A1:F{$lastRow}");

        // Set print area margins
        $sheet->getPageMargins()
            ->setTop(0.5)
            ->setRight(0.5)
            ->setBottom(0.5)
            ->setLeft(0.5);

        // Footer - Prepared By and Checked By
        $row += 2; // Add some space

        $sheet->setCellValue("A{$row}", 'Prepared By:');
        $sheet->setCellValue("E{$row}", 'Checked By:');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $sheet->getStyle("E{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $row++;
        $row++; // Empty row
        $row++; // Empty row (for signature space)

        // Names
        $sheet->setCellValue("A{$row}", $preparedBy);
        $sheet->setCellValue("E{$row}", $checkedBy);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setUnderline(\PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE);
        $sheet->getStyle("E{$row}")->getFont()->setBold(true)->setUnderline(\PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $row++;

        // Positions
        $sheet->setCellValue("A{$row}", $preparedPosition);
        $sheet->setCellValue("E{$row}", $checkedPosition);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Download
        $filename = "Meal_Recap_{$location}_" . date('Ymd', strtotime($startDate)) . ".xlsx";
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
}
