<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ScanController extends Controller
{
    public function index()
    {
        return view('scan.index');
    }

    public function process(Request $request)
    {
        $validated = $request->validate([
            'employee_number' => 'required|string',
            'meal_type' => 'required|in:breakfast,lunch,dinner,supper,snack',
            'location' => 'nullable|string',
        ]);

        $employee = Employee::where('employee_number', $validated['employee_number'])->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found',
            ], 404);
        }

        if ($employee->active_status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Employee is inactive',
            ], 400);
        }

        // Check for duplicate scan within the same meal period
        $recentScan = Attendance::where('employee_id', $employee->id)
            ->where('meal_type', $validated['meal_type'])
            ->whereDate('scanned_at', Carbon::today())
            ->first();

        if ($recentScan) {
            return response()->json([
                'success' => false,
                'message' => 'Already scanned for ' . $validated['meal_type'] . ' today',
                'employee_name' => $employee->name,
            ], 400);
        }

        // Record the attendance
        $location = $validated['location'] ?? $employee->location; // Use manual location or employee's home base

        Attendance::create([
            'employee_id' => $employee->id,
            'meal_type' => $validated['meal_type'],
            'scan_method' => 'qr_scan',
            'scanned_at' => Carbon::now(),
            'location' => $location,
        ]);

        // Auto-add snack when recording dinner
        if ($validated['meal_type'] === 'dinner') {
            $this->autoAddSnack($employee->id, Carbon::now(), null, $location);
        }

        return response()->json([
            'success' => true,
            'message' => 'Thank you!',
            'employee_name' => $employee->name,
            'meal_type' => $validated['meal_type'],
        ]);
    }

    public function manual()
    {
        $employees = Employee::where('active_status', 'active')->orderBy('name')->get();
        return view('scan.manual', compact('employees'));
    }

    public function storeManual(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'meal_type' => 'required|in:breakfast,lunch,dinner,supper,snack',
            'recorded_by' => 'required|string|max:255',
            'scanned_at' => 'nullable|date',
            'location' => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        if ($employee->active_status !== 'active') {
            return back()->with('error', 'Employee is inactive');
        }

        $scannedAt = $validated['scanned_at'] ?? Carbon::now();

        // Check for duplicate
        $existingScan = Attendance::where('employee_id', $employee->id)
            ->where('meal_type', $validated['meal_type'])
            ->whereDate('scanned_at', Carbon::parse($scannedAt)->toDateString())
            ->first();

        if ($existingScan) {
            return back()->with('error', 'Already recorded for ' . $validated['meal_type'] . ' on this date');
        }

        $location = $validated['location'] ?? $employee->location; // Use manual location or employee's home base

        Attendance::create([
            'employee_id' => $validated['employee_id'],
            'meal_type' => $validated['meal_type'],
            'scan_method' => 'manual',
            'recorded_by' => $validated['recorded_by'],
            'scanned_at' => $scannedAt,
            'location' => $location,
        ]);

        // Auto-add snack when recording dinner
        if ($validated['meal_type'] === 'dinner') {
            $this->autoAddSnack($validated['employee_id'], $scannedAt, $validated['recorded_by'], $location);
        }

        return back()->with('success', 'Attendance recorded for ' . $employee->name);
    }

    /**
     * Auto-add snack when dinner is recorded
     */
    private function autoAddSnack($employeeId, $dateTime, $recordedBy = null, $location = null)
    {
        $date = Carbon::parse($dateTime)->toDateString();

        // Check if snack already exists
        $snackExists = Attendance::where('employee_id', $employeeId)
            ->where('meal_type', 'snack')
            ->whereDate('scanned_at', $date)
            ->exists();

        if (!$snackExists) {
            Attendance::create([
                'employee_id' => $employeeId,
                'meal_type' => 'snack',
                'scan_method' => $recordedBy ? 'manual' : 'qr_scan',
                'recorded_by' => $recordedBy,
                'scanned_at' => Carbon::parse($dateTime)->setTime(20, 0, 0), // Snack at 8pm
                'location' => $location, // Same location as dinner
            ]);
        }
    }
}

