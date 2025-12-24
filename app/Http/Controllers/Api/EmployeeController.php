<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmployeeController extends Controller
{
    /**
     * List all employees with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Employee::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_number', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('active_status', $request->status);
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        // Support for getting all employees (no pagination) or paginated
        if ($request->boolean('all')) {
            $employees = $query->orderBy('name')->get();
            return response()->json([
                'data' => $employees,
                'total' => $employees->count(),
            ]);
        }

        $perPage = $request->integer('per_page', 15);
        $employees = $query->orderBy('name')->paginate($perPage);

        return response()->json($employees);
    }

    /**
     * Get a single employee by ID.
     */
    public function show(Employee $employee): JsonResponse
    {
        return response()->json([
            'data' => $employee,
        ]);
    }

    /**
     * Get distinct departments.
     */
    public function departments(): JsonResponse
    {
        $departments = Employee::whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->pluck('department')
            ->sort()
            ->values();

        return response()->json([
            'data' => $departments,
        ]);
    }

    /**
     * Get distinct locations.
     */
    public function locations(): JsonResponse
    {
        $locations = Employee::whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->pluck('location')
            ->sort()
            ->values();

        return response()->json([
            'data' => $locations,
        ]);
    }
}
