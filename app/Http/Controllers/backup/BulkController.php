<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\EmployeeGroup;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BulkController extends Controller
{
    public function index()
    {
        $employees = Employee::where('active_status', 'active')
            ->orderBy('name')
            ->get();

        $groups = EmployeeGroup::with('employees:id,name,employee_number,department,employee_status')->get();

        // Get recent absence proof files from the last 30 days
        $recentProofs = Attendance::whereNotNull('absence_proof')
            ->where('scanned_at', '>=', now()->subDays(30))
            ->select('absence_proof')
            ->distinct()
            ->orderBy('scanned_at', 'desc')
            ->limit(50)
            ->pluck('absence_proof')
            ->map(function ($path) {
                return [
                    'path' => $path,
                    'filename' => basename($path),
                    'url' => \Storage::disk('public_direct')->url($path)
                ];
            });

        return view('bulk.index', compact('employees', 'groups', 'recentProofs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'recorded_by' => 'required|string|max:255',
            'location' => 'nullable|string',
            'absence_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240', // 10MB max
            'existing_proof' => 'nullable|string', // Path to existing file
            'entries' => 'required|array|min:1|max:200',
            'entries.*.employee_id' => 'required|exists:employees,id',
            'entries.*.meals' => 'nullable|array', // Allow empty meals - will be skipped
            'entries.*.meals.*' => 'in:breakfast,lunch,dinner,supper,snack',
        ]);

        $date = Carbon::parse($validated['date']);
        $recordedBy = $validated['recorded_by'];
        $overrideLocation = $validated['location'] ?? null;
        $successCount = 0;
        $skippedCount = 0;
        $skippedRecords = []; // Track details of skipped records
        $mealCounts = [
            'breakfast' => 0,
            'lunch' => 0,
            'dinner' => 0,
            'supper' => 0,
            'snack' => 0,
        ];

        // Handle file upload or existing file selection
        $absenceProofPath = null;

        // Priority: new file upload > existing file selection
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
        } elseif (!empty($validated['existing_proof'])) {
            // Use existing file path if provided
            $absenceProofPath = $validated['existing_proof'];
        }

        // REQUIRED: Ensure absence proof is provided
        if (empty($absenceProofPath)) {
            return redirect()->back()
                ->withErrors(['absence_proof' => 'Absence Proof is required. Please upload a file or select an existing one.'])
                ->withInput();
        }

        foreach ($validated['entries'] as $entry) {
            // Skip entries with no meals selected
            if (empty($entry['meals'])) {
                continue;
            }

            $employee = Employee::find($entry['employee_id']);

            if (!$employee || $employee->active_status !== 'active') {
                continue;
            }

            $hasDinner = in_array('dinner', $entry['meals']);

            foreach ($entry['meals'] as $mealType) {
                // Check for duplicate
                $existingAttendance = Attendance::where('employee_id', $employee->id)
                    ->where('meal_type', $mealType)
                    ->whereDate('scanned_at', $date->toDateString())
                    ->first();

                if ($existingAttendance) {
                    $skippedCount++;
                    $skippedRecords[] = [
                        'employee_name' => $employee->name,
                        'employee_number' => $employee->employee_number,
                        'location' => $existingAttendance->location, // Show location from existing record
                        'meal_type' => ucfirst($mealType),
                        'date' => $date->format('d/m/Y'),
                        'reason' => 'Duplicate'
                    ];
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
                $mealCounts[$mealType]++;
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
                    $mealCounts['snack']++;
                }
            }
        }

        // Build meal breakdown string
        $mealBreakdown = [];
        foreach ($mealCounts as $type => $count) {
            if ($count > 0) {
                $mealBreakdown[] = "{$count} " . ucfirst($type);
            }
        }

        $message = "Bulk input completed: {$successCount} records created";
        if (!empty($mealBreakdown)) {
            $message .= " (" . implode(', ', $mealBreakdown) . ")";
        }
        if ($skippedCount > 0) {
            $message .= ", {$skippedCount} skipped (duplicates)";
        }

        return redirect()->route('bulk.index')
            ->with('success', $message)
            ->with('skipped_records', $skippedRecords);
    }
}

