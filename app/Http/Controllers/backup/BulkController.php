<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BulkController extends Controller
{
    public function index()
    {
        $employees = Employee::where('active_status', 'active')
            ->orderBy('name')
            ->get();

        return view('bulk.index', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'recorded_by' => 'required|string|max:255',
            'location' => 'nullable|string',
            'absence_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240', // 10MB max
            'entries' => 'required|array|min:1|max:200',
            'entries.*.employee_id' => 'required|exists:employees,id',
            'entries.*.meals' => 'required|array|min:1',
            'entries.*.meals.*' => 'in:breakfast,lunch,dinner,supper,snack',
        ]);

        $date = Carbon::parse($validated['date']);
        $recordedBy = $validated['recorded_by'];
        $overrideLocation = $validated['location'] ?? null;
        $successCount = 0;
        $skippedCount = 0;

        // Handle file upload if present
        $absenceProofPath = null;
        if ($request->hasFile('absence_proof')) {
            $file = $request->file('absence_proof');
            $extension = $file->getClientOriginalExtension();
            $baseFilename = $date->format('Y-m-d') . '_' . $overrideLocation;
            $filename = $baseFilename . '.' . $extension;

            // Check if file exists and add counter suffix if needed
            $counter = 1;
            while (\Storage::disk('public_direct')->exists('absence_proofs/' . $filename)) {
                $filename = $baseFilename . '(' . $counter . ').' . $extension;
                $counter++;
            }

            $absenceProofPath = $file->storeAs('absence_proofs', $filename, 'public_direct');
        }

        foreach ($validated['entries'] as $entry) {
            $employee = Employee::find($entry['employee_id']);

            if (!$employee || $employee->active_status !== 'active') {
                continue;
            }

            $hasDinner = in_array('dinner', $entry['meals']);

            foreach ($entry['meals'] as $mealType) {
                // Check for duplicate
                $exists = Attendance::where('employee_id', $employee->id)
                    ->where('meal_type', $mealType)
                    ->whereDate('scanned_at', $date->toDateString())
                    ->exists();

                if ($exists) {
                    $skippedCount++;
                    continue;
                }

                // Set time based on meal type
                $hour = match ($mealType) {
                    'breakfast' => 7,
                    'lunch' => 12,
                    'dinner' => 18,
                    'supper' => 22,
                    'snack' => 20,
                };

                $location = $overrideLocation ?? $employee->location; // Use override or employee homebase

                Attendance::create([
                    'employee_id' => $employee->id,
                    'meal_type' => $mealType,
                    'scan_method' => 'manual',
                    'recorded_by' => $recordedBy,
                    'scanned_at' => $date->copy()->setTime($hour, 0, 0),
                    'location' => $location,
                    'absence_proof' => $absenceProofPath,
                ]);

                $successCount++;
            }

            // Auto-add snack if dinner was selected but snack wasn't
            if ($hasDinner && !in_array('snack', $entry['meals'])) {
                $snackExists = Attendance::where('employee_id', $employee->id)
                    ->where('meal_type', 'snack')
                    ->whereDate('scanned_at', $date->toDateString())
                    ->exists();

                if (!$snackExists) {
                    Attendance::create([
                        'employee_id' => $employee->id,
                        'meal_type' => 'snack',
                        'scan_method' => 'manual',
                        'recorded_by' => $recordedBy,
                        'scanned_at' => $date->copy()->setTime(20, 0, 0),
                        'location' => $location,
                        'absence_proof' => $absenceProofPath,
                    ]);
                    $successCount++;
                }
            }
        }

        $message = "Bulk input completed: {$successCount} records created";
        if ($skippedCount > 0) {
            $message .= ", {$skippedCount} skipped (duplicates)";
        }

        return redirect()->route('bulk.index')->with('success', $message);
    }
}

