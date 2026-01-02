<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Exports\AttendanceExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
}
