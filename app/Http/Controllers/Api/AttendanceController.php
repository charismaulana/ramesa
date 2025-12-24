<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AttendanceController extends Controller
{
    /**
     * List attendances with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Attendance::with('employee');

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('scanned_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scanned_at', '<=', $request->date_to);
        }

        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('meal_type')) {
            $query->where('meal_type', $request->meal_type);
        }

        // Support for getting all (no pagination) or paginated
        if ($request->boolean('all')) {
            $attendances = $query->orderBy('scanned_at', 'desc')->get();
            return response()->json([
                'data' => $attendances,
                'total' => $attendances->count(),
            ]);
        }

        $perPage = $request->integer('per_page', 15);
        $attendances = $query->orderBy('scanned_at', 'desc')->paginate($perPage);

        return response()->json($attendances);
    }

    /**
     * Get distinct employee IDs with attendance in date range and location.
     * Useful for POB comparison feature.
     */
    public function distinctEmployeeIds(Request $request): JsonResponse
    {
        $query = Attendance::query();

        if ($request->filled('date_from')) {
            $query->whereDate('scanned_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scanned_at', '<=', $request->date_to);
        }

        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        $employeeIds = $query->distinct()->pluck('employee_id');

        return response()->json([
            'data' => $employeeIds,
        ]);
    }
}
