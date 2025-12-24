<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return ($this->filters['export_type'] ?? 'detailed') === 'summary' ? 'Summary' : 'Detailed';
    }

    public function collection()
    {
        $exportType = $this->filters['export_type'] ?? 'detailed';

        if ($exportType === 'summary') {
            return $this->getSummaryData();
        }

        return $this->getDetailedData();
    }

    protected function getDetailedData()
    {
        $query = Attendance::with('employee');

        if (!empty($this->filters['start_date'])) {
            $query->whereDate('scanned_at', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereDate('scanned_at', '<=', $this->filters['end_date']);
        }

        if (!empty($this->filters['meal_type'])) {
            $query->where('meal_type', $this->filters['meal_type']);
        }

        if (!empty($this->filters['location'])) {
            $query->whereHas('employee', function ($q) {
                $q->where('location', $this->filters['location']);
            });
        }

        return $query->orderBy('scanned_at', 'desc')
            ->get()
            ->map(function ($attendance) {
                return [
                    'DateTime' => $attendance->scanned_at->format('Y-m-d H:i:s'),
                    'Employee Number' => $attendance->employee->employee_number ?? '',
                    'Employee Name' => $attendance->employee->name ?? '',
                    'Company' => $attendance->employee->company ?? '',
                    'Department' => $attendance->employee->department ?? '',
                    'Location' => $attendance->location ?? $attendance->employee->location ?? '', // Use attendance location
                    'Employee Status' => $attendance->employee->employee_status ?? '',
                    'Meal Type' => ucfirst($attendance->meal_type),
                    'Scan Method' => $attendance->scan_method === 'qr_scan' ? 'QR Scan' : 'Manual',
                    'Recorded By' => $attendance->recorded_by ?? '-',
                ];
            });
    }

    protected function getSummaryData()
    {
        // Create status category mapping using CASE (without numbers for cleaner display)
        $statusCategoryCase = "CASE 
            WHEN employees.employee_status = 'Pekerja' THEN 'Pekerja'
            WHEN employees.employee_status IN ('TA', 'TKJP') THEN 'TA/TKJP'
            WHEN employees.employee_status IN ('Sub Contractor', 'Contractor') THEN 'Contractor'
            WHEN employees.employee_status = 'Visitor' THEN 'Visitor'
            ELSE 'Other'
        END";

        $query = Attendance::query()
            ->join('employees', 'attendances.employee_id', '=', 'employees.id')
            ->select(
                DB::raw('DATE(attendances.scanned_at) as date'),
                'attendances.location',
                DB::raw("$statusCategoryCase as status_category"),
                DB::raw("SUM(CASE WHEN meal_type = 'breakfast' THEN 1 ELSE 0 END) as breakfast"),
                DB::raw("SUM(CASE WHEN meal_type = 'lunch' THEN 1 ELSE 0 END) as lunch"),
                DB::raw("SUM(CASE WHEN meal_type = 'dinner' THEN 1 ELSE 0 END) as dinner"),
                DB::raw("SUM(CASE WHEN meal_type = 'supper' THEN 1 ELSE 0 END) as supper"),
                DB::raw("SUM(CASE WHEN meal_type = 'snack' THEN 1 ELSE 0 END) as snack"),
                DB::raw('COUNT(*) as total')
            )
            ->whereNull('attendances.deleted_at')
            ->groupBy('date', 'attendances.location', DB::raw($statusCategoryCase))
            ->orderBy('date', 'desc')
            ->orderBy('attendances.location')
            ->orderBy('status_category');

        if (!empty($this->filters['start_date'])) {
            $query->whereDate('scanned_at', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereDate('scanned_at', '<=', $this->filters['end_date']);
        }

        if (!empty($this->filters['location'])) {
            $query->where('attendances.location', $this->filters['location']);
        }

        $results = $query->get();

        // Calculate grand totals
        $grandTotals = [
            'breakfast' => $results->sum('breakfast'),
            'lunch' => $results->sum('lunch'),
            'dinner' => $results->sum('dinner'),
            'supper' => $results->sum('supper'),
            'snack' => $results->sum('snack'),
            'total' => $results->sum('total'),
        ];

        // Map data rows
        $data = $results->map(function ($row) {
            return [
                'Date' => $row->date,
                'Location' => $row->location ?? 'Unknown',
                'Status Category' => $row->status_category,
                'Breakfast' => $row->breakfast,
                'Lunch' => $row->lunch,
                'Dinner' => $row->dinner,
                'Supper' => $row->supper,
                'Snack' => $row->snack,
                'Total' => $row->total,
            ];
        });

        // Add grand total row
        $data->push([
            'Date' => 'GRAND TOTAL',
            'Location' => '',
            'Status Category' => '',
            'Breakfast' => $grandTotals['breakfast'],
            'Lunch' => $grandTotals['lunch'],
            'Dinner' => $grandTotals['dinner'],
            'Supper' => $grandTotals['supper'],
            'Snack' => $grandTotals['snack'],
            'Total' => $grandTotals['total'],
        ]);

        return $data;
    }

    public function headings(): array
    {
        $exportType = $this->filters['export_type'] ?? 'detailed';

        if ($exportType === 'summary') {
            return ['Date', 'Location', 'Status Category', 'Breakfast', 'Lunch', 'Dinner', 'Supper', 'Snack', 'Total'];
        }

        return [
            'DateTime',
            'Employee Number',
            'Employee Name',
            'Company',
            'Department',
            'Location',
            'Employee Status',
            'Meal Type',
            'Scan Method',
            'Recorded By',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFF4500'],
                ],
            ],
        ];
    }
}
