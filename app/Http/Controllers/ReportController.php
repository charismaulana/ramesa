<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function form()
    {
        $locations = Employee::distinct()->pluck('location')->filter()->values();
        $employeeStatuses = ['Pekerja', 'TA', 'TKJP', 'Sub Contractor', 'Visitor'];

        return view('report.form', compact('locations', 'employeeStatuses'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $dateFrom = Carbon::parse($request->date_from);
        $dateTo = Carbon::parse($request->date_to);
        $location = $request->location;
        $employeeStatuses = $request->employee_statuses ?? [];
        $reportType = $request->report_type ?? 'summary';

        // Get data based on report type
        if ($reportType === 'detailed') {
            $data = $this->getDetailedData($dateFrom, $dateTo, $location, $employeeStatuses);
        } else {
            $data = $this->getSummaryData($dateFrom, $dateTo, $location, $employeeStatuses);
        }

        $pdf = Pdf::loadView('report.pdf', [
            'data' => $data,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'location' => $location,
            'reportType' => $reportType,
        ]);

        $pdf->setPaper('a4', 'landscape');

        $filename = 'Rekap_Tagihan_Catering_' . $dateFrom->format('Ymd') . '_' . $dateTo->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }

    private function getSummaryData($dateFrom, $dateTo, $location, $employeeStatuses)
    {
        $locations = $location
            ? collect([$location])
            : Employee::distinct()->pluck('location')->filter()->values();

        $statusMap = [
            'Pekerja' => ['Pekerja'],
            'TA' => ['TA'],
            'TKJP' => ['TKJP'],
            'Sub Contractor' => ['Sub Contractor'],
            'Visitor' => ['Visitor'],
        ];

        $data = [];

        foreach ($locations as $loc) {
            $data[$loc] = [];

            foreach ($statusMap as $statusLabel => $statusValues) {
                if (!empty($employeeStatuses) && !in_array($statusLabel, $employeeStatuses)) {
                    continue;
                }

                $query = Attendance::join('employees', 'attendances.employee_id', '=', 'employees.id')
                    ->where('attendances.location', $loc) // Use attendance location
                    ->whereIn('employees.employee_status', $statusValues)
                    ->whereDate('attendances.scanned_at', '>=', $dateFrom)
                    ->whereDate('attendances.scanned_at', '<=', $dateTo);

                $data[$loc][$statusLabel] = [
                    'breakfast' => (clone $query)->where('meal_type', 'breakfast')->count(),
                    'lunch' => (clone $query)->where('meal_type', 'lunch')->count(),
                    'dinner' => (clone $query)->where('meal_type', 'dinner')->count(),
                    'supper' => (clone $query)->where('meal_type', 'supper')->count(),
                    'snack' => (clone $query)->where('meal_type', 'snack')->count(),
                ];
                $data[$loc][$statusLabel]['total'] = array_sum($data[$loc][$statusLabel]);
            }
        }

        return $data;
    }

    private function getDetailedData($dateFrom, $dateTo, $location, $employeeStatuses)
    {
        $locations = $location
            ? collect([$location])
            : Employee::distinct()->pluck('location')->filter()->values();

        $statusMap = [
            'Pekerja' => ['Pekerja'],
            'TA' => ['TA'],
            'TKJP' => ['TKJP'],
            'Sub Contractor' => ['Sub Contractor'],
            'Visitor' => ['Visitor'],
        ];

        $data = [];
        $dates = [];

        // Generate date range
        $currentDate = $dateFrom->copy();
        while ($currentDate <= $dateTo) {
            $dates[] = $currentDate->copy();
            $currentDate->addDay();
        }

        foreach ($locations as $loc) {
            $data[$loc] = [];

            foreach ($statusMap as $statusLabel => $statusValues) {
                if (!empty($employeeStatuses) && !in_array($statusLabel, $employeeStatuses)) {
                    continue;
                }

                $data[$loc][$statusLabel] = [
                    'dates' => $dates,
                    'daily' => [],
                    'totals' => [
                        'breakfast' => 0,
                        'lunch' => 0,
                        'dinner' => 0,
                        'supper' => 0,
                        'snack' => 0,
                        'total' => 0
                    ],
                ];

                foreach ($dates as $date) {
                    $query = Attendance::join('employees', 'attendances.employee_id', '=', 'employees.id')
                        ->where('attendances.location', $loc) // Use attendance location
                        ->whereIn('employees.employee_status', $statusValues)
                        ->whereDate('attendances.scanned_at', $date);

                    $dayData = [
                        'date' => $date,
                        'breakfast' => (clone $query)->where('meal_type', 'breakfast')->count(),
                        'lunch' => (clone $query)->where('meal_type', 'lunch')->count(),
                        'dinner' => (clone $query)->where('meal_type', 'dinner')->count(),
                        'supper' => (clone $query)->where('meal_type', 'supper')->count(),
                        'snack' => (clone $query)->where('meal_type', 'snack')->count(),
                    ];

                    $data[$loc][$statusLabel]['daily'][] = $dayData;

                    // Add to totals
                    foreach (['breakfast', 'lunch', 'dinner', 'supper', 'snack'] as $meal) {
                        $data[$loc][$statusLabel]['totals'][$meal] += $dayData[$meal];
                    }
                }

                $data[$loc][$statusLabel]['totals']['total'] = array_sum(array_filter(
                    $data[$loc][$statusLabel]['totals'],
                    fn($k) => $k !== 'total',
                    ARRAY_FILTER_USE_KEY
                ));
            }
        }

        return $data;
    }
}
