<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\MealPrice;
use App\Models\LockedPeriod;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Calendar month filter - if set, use it to determine date range
        $calendarMonth = $request->get('calendar_month', Carbon::now()->format('Y-m'));
        $calendarMonthStart = Carbon::parse($calendarMonth . '-01')->startOfMonth();
        $calendarMonthEnd = $calendarMonthStart->copy()->endOfMonth();

        // Date filter - if calendar_month is explicitly set and date_from/date_to are not,
        // use the calendar month range for the date filter
        $hasExplicitDateFilter = $request->has('date_from') || $request->has('date_to');

        if (!$hasExplicitDateFilter && $request->has('calendar_month')) {
            // User explicitly selected a calendar month, sync date filter with it
            $dateFrom = $calendarMonthStart->toDateString();
            // If the selected month is the current month, only go up to today
            if ($calendarMonth === Carbon::now()->format('Y-m')) {
                $dateTo = Carbon::today()->toDateString();
            } else {
                $dateTo = $calendarMonthEnd->toDateString();
            }
        } else {
            // Use the default or explicit date range
            $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->toDateString());
            $dateTo = $request->get('date_to', Carbon::today()->toDateString());
        }

        // Get locations from employees
        // Stats by location
        $locations = ['Ramba', 'Bentayan', 'Mangunjaya', 'Keluang'];
        $statsByLocation = [];

        foreach ($locations as $location) {
            $locationQuery = Attendance::whereDate('scanned_at', '>=', $dateFrom)
                ->whereDate('scanned_at', '<=', $dateTo)
                ->where('location', $location); // Use attendance location

            $statsByLocation[$location] = [
                'breakfast' => (clone $locationQuery)->where('meal_type', 'breakfast')->count(),
                'lunch' => (clone $locationQuery)->where('meal_type', 'lunch')->count(),
                'dinner' => (clone $locationQuery)->where('meal_type', 'dinner')->count(),
                'supper' => (clone $locationQuery)->where('meal_type', 'supper')->count(),
                'snack' => (clone $locationQuery)->where('meal_type', 'snack')->count(),
            ];
            $statsByLocation[$location]['total'] = array_sum($statsByLocation[$location]);
        }

        // Total stats
        $totalStats = [
            'breakfast' => Attendance::whereDate('scanned_at', '>=', $dateFrom)
                ->whereDate('scanned_at', '<=', $dateTo)
                ->where('meal_type', 'breakfast')->count(),
            'lunch' => Attendance::whereDate('scanned_at', '>=', $dateFrom)
                ->whereDate('scanned_at', '<=', $dateTo)
                ->where('meal_type', 'lunch')->count(),
            'dinner' => Attendance::whereDate('scanned_at', '>=', $dateFrom)
                ->whereDate('scanned_at', '<=', $dateTo)
                ->where('meal_type', 'dinner')->count(),
            'supper' => Attendance::whereDate('scanned_at', '>=', $dateFrom)
                ->whereDate('scanned_at', '<=', $dateTo)
                ->where('meal_type', 'supper')->count(),
            'snack' => Attendance::whereDate('scanned_at', '>=', $dateFrom)
                ->whereDate('scanned_at', '<=', $dateTo)
                ->where('meal_type', 'snack')->count(),
        ];
        $totalStats['total'] = array_sum($totalStats);

        // Quick stats
        $todayTotal = Attendance::whereDate('scanned_at', Carbon::today())->count();
        $yesterdayTotal = Attendance::whereDate('scanned_at', Carbon::yesterday())->count();
        $thisWeekTotal = Attendance::whereBetween('scanned_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ])->count();
        $thisMonthTotal = Attendance::whereMonth('scanned_at', Carbon::now()->month)
            ->whereYear('scanned_at', Carbon::now()->year)->count();

        // Active employees count
        $activeEmployees = Employee::where('active_status', 'active')->count();

        // Get meal prices and calculate estimated invoice
        $mealPrices = MealPrice::current();
        $estimatedInvoice = [
            'breakfast' => $totalStats['breakfast'] * $mealPrices->breakfast_price,
            'lunch' => $totalStats['lunch'] * $mealPrices->lunch_price,
            'dinner' => $totalStats['dinner'] * $mealPrices->dinner_price,
            'supper' => $totalStats['supper'] * $mealPrices->supper_price,
            'snack' => $totalStats['snack'] * $mealPrices->snack_price,
        ];
        $estimatedInvoice['total'] = array_sum($estimatedInvoice);

        // Invoice per location
        $invoiceByLocation = [];
        foreach ($locations as $location) {
            $invoiceByLocation[$location] = [
                'breakfast' => $statsByLocation[$location]['breakfast'] * $mealPrices->breakfast_price,
                'lunch' => $statsByLocation[$location]['lunch'] * $mealPrices->lunch_price,
                'dinner' => $statsByLocation[$location]['dinner'] * $mealPrices->dinner_price,
                'supper' => $statsByLocation[$location]['supper'] * $mealPrices->supper_price,
                'snack' => $statsByLocation[$location]['snack'] * $mealPrices->snack_price,
            ];
            $invoiceByLocation[$location]['total'] = array_sum($invoiceByLocation[$location]);
        }

        // Calendar data - get dates with attendance per location for current month
        // Note: $calendarMonth, $calendarMonthStart, $calendarMonthEnd are already computed above
        $calendarStart = $calendarMonthStart->copy();
        $calendarEnd = $calendarMonthEnd->copy();

        $calendarData = [];
        foreach ($locations as $location) {
            $calendarData[$location] = Attendance::where('location', $location)
                ->whereBetween('scanned_at', [$calendarStart, $calendarEnd])
                ->select(DB::raw('DATE(scanned_at) as date'), DB::raw('COUNT(*) as count'))
                ->groupBy(DB::raw('DATE(scanned_at)'))
                ->pluck('count', 'date')
                ->toArray();
        }

        // Get locked dates per location for the calendar month
        $lockedDates = [];
        foreach ($locations as $location) {
            $lockedDates[$location] = [];
            $lockedPeriods = LockedPeriod::where(function ($q) use ($location) {
                $q->whereNull('location')->orWhere('location', $location);
            })
                ->where('start_date', '<=', $calendarEnd)
                ->where('end_date', '>=', $calendarStart)
                ->get();

            foreach ($lockedPeriods as $period) {
                $start = max($period->start_date, $calendarStart);
                $end = min($period->end_date, $calendarEnd);
                $current = Carbon::parse($start);
                while ($current <= $end) {
                    $lockedDates[$location][$current->format('Y-m-d')] = true;
                    $current->addDay();
                }
            }
        }

        // Breakdown by date and employee status (single location filter)
        $employeeStatuses = ['Pekerja', 'TA & TKJP', 'Contractor', 'Visitor'];
        $breakdownLocation = $request->get('breakdown_location', $locations[0]);
        $statsByDate = [];
        $dailyTotals = [];

        // Generate date range
        $currentDate = Carbon::parse($dateFrom);
        $endDate = Carbon::parse($dateTo);
        $dates = [];
        while ($currentDate <= $endDate) {
            $dates[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }

        foreach ($dates as $date) {
            $statsByDate[$date] = [];
            $dailyTotals[$date] = ['breakfast' => 0, 'lunch' => 0, 'dinner' => 0, 'supper' => 0, 'snack' => 0, 'total' => 0];

            foreach ($employeeStatuses as $status) {
                // Map status to actual employee_status values
                if ($status === 'TA & TKJP') {
                    $statusCondition = function ($q) {
                        $q->where('employees.employee_status', 'TA')
                            ->orWhere('employees.employee_status', 'TKJP');
                    };
                } else {
                    $statusCondition = function ($q) use ($status) {
                        $q->where('employees.employee_status', $status);
                    };
                }

                // Join with employees table to get employee_status
                $baseQuery = Attendance::join('employees', 'attendances.employee_id', '=', 'employees.id')
                    ->whereDate('attendances.scanned_at', $date)
                    ->where('attendances.location', $breakdownLocation)
                    ->where($statusCondition);

                $statsByDate[$date][$status] = [
                    'breakfast' => (clone $baseQuery)->where('attendances.meal_type', 'breakfast')->count(),
                    'lunch' => (clone $baseQuery)->where('attendances.meal_type', 'lunch')->count(),
                    'dinner' => (clone $baseQuery)->where('attendances.meal_type', 'dinner')->count(),
                    'supper' => (clone $baseQuery)->where('attendances.meal_type', 'supper')->count(),
                    'snack' => (clone $baseQuery)->where('attendances.meal_type', 'snack')->count(),
                ];
                $statsByDate[$date][$status]['total'] = array_sum($statsByDate[$date][$status]);

                // Add to daily totals
                foreach (['breakfast', 'lunch', 'dinner', 'supper', 'snack', 'total'] as $meal) {
                    $dailyTotals[$date][$meal] += $statsByDate[$date][$status][$meal];
                }
            }
        }




        return view('dashboard.index', compact(
            'statsByLocation',
            'totalStats',
            'locations',
            'dateFrom',
            'dateTo',
            'todayTotal',
            'yesterdayTotal',
            'thisWeekTotal',
            'thisMonthTotal',
            'activeEmployees',
            'mealPrices',
            'estimatedInvoice',
            'invoiceByLocation',
            'calendarData',
            'calendarMonth',
            'calendarStart',
            'calendarEnd',
            'lockedDates',
            'statsByDate',
            'dailyTotals',
            'dates',
            'employeeStatuses',
            'breakdownLocation'
        ));
    }

    public function updatePrices(Request $request)
    {
        $validated = $request->validate([
            'breakfast_price' => 'required|numeric|min:0',
            'lunch_price' => 'required|numeric|min:0',
            'dinner_price' => 'required|numeric|min:0',
            'supper_price' => 'required|numeric|min:0',
            'snack_price' => 'required|numeric|min:0',
        ]);

        $mealPrices = MealPrice::current();
        $mealPrices->update($validated);

        return back()->with('success', 'Meal prices updated successfully');
    }

    /**
     * Get attendance details for a specific date, location, status, and meal type
     */
    public function getAttendanceDetails(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'location' => 'required|string',
            'status' => 'required|string',
            'meal_type' => 'required|in:breakfast,lunch,dinner,supper,snack',
        ]);

        $date = $validated['date'];
        $location = $validated['location'];
        $status = $validated['status'];
        $mealType = $validated['meal_type'];

        // Map status to actual employee_status values
        if ($status === 'TA & TKJP') {
            $statusCondition = function ($q) {
                $q->where('employees.employee_status', 'TA')
                    ->orWhere('employees.employee_status', 'TKJP');
            };
        } else {
            $statusCondition = function ($q) use ($status) {
                $q->where('employees.employee_status', $status);
            };
        }

        // Query attendances with employee details
        $attendances = Attendance::join('employees', 'attendances.employee_id', '=', 'employees.id')
            ->whereDate('attendances.scanned_at', $date)
            ->where('attendances.location', $location)
            ->where('attendances.meal_type', $mealType)
            ->where($statusCondition)
            ->select(
                'employees.name',
                'employees.department',
                'attendances.recorded_by'
            )
            ->orderBy('employees.name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $attendances,
            'count' => $attendances->count(),
            'filters' => [
                'date' => Carbon::parse($date)->format('d M Y'),
                'location' => $location,
                'status' => $status,
                'meal_type' => ucfirst($mealType),
            ]
        ]);
    }
}

