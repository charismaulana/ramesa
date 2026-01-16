<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Room;
use App\Models\RoomGroup;
use App\Models\RoomStatusOverride;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class RoomDashboardController extends Controller
{
    /**
     * Display the room dashboard for a specific location.
     */
    public function index(Request $request)
    {
        $location = $request->get('location', 'Ramba');
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $dateCarbon = Carbon::parse($date);

        // Get room groups for this location
        $roomGroups = RoomGroup::where('location', $location)
            ->orderBy('order')
            ->with([
                'rooms' => function ($q) {
                    $q->orderBy('order');
                }
            ])
            ->get();

        // If no groups exist, fall back to employee-derived data
        if ($roomGroups->isEmpty()) {
            return $this->indexLegacy($request);
        }

        // Get all active employees with accommodation at this location
        $employees = Employee::where('active_status', 'active')
            ->whereNotNull('accommodation')
            ->with([
                'attendances' => function ($q) use ($dateCarbon, $location) {
                    $q->whereDate('scanned_at', $dateCarbon->toDateString())
                        ->where('location', $location);
                }
            ])
            ->get();

        // Group employees by their accommodation at this location
        $employeesByRoom = [];
        foreach ($employees as $emp) {
            $roomKey = $emp->accommodation[$location] ?? null;
            if ($roomKey) {
                if (!isset($employeesByRoom[$roomKey])) {
                    $employeesByRoom[$roomKey] = [];
                }
                $employeesByRoom[$roomKey][] = $emp;
            }
        }

        // Build room data per group
        $columns = [];
        $totals = [];

        foreach ($roomGroups as $group) {
            $groupData = [];
            $on = 0;
            $off = 0;
            $vacant = 0;

            foreach ($group->rooms as $room) {
                // Build the full room key: "GROUP_NAME ROOM_CODE" (e.g., "GRU A1")
                $fullRoomKey = $group->name . ' ' . $room->room_code;
                $roomOccupants = $employeesByRoom[$fullRoomKey] ?? [];
                $capacity = $room->capacity ?? 1;
                $isFillable = $room->is_fillable ?? true;

                // For non-fillable rooms (warehouse, storage), show just one row with notes
                if (!$isFillable || $capacity == 0) {
                    $groupData[] = [
                        'room' => $room->room_code,
                        'name' => strtoupper($room->notes ?? 'N/A'),
                        'status' => '',
                        'is_vacant' => false,
                        'is_continuation' => false,
                        'is_non_fillable' => true,
                    ];
                    continue;
                }

                // Create row for each capacity slot
                for ($slot = 0; $slot < $capacity; $slot++) {
                    $employee = $roomOccupants[$slot] ?? null;
                    $hasAttendance = false;
                    $name = 'VACANT';
                    $department = '';
                    $empStatus = '';
                    $isOverride = false;
                    $employeeId = null;

                    if ($employee) {
                        $name = strtoupper($employee->name);
                        $department = $employee->department ?? '-';
                        $empStatus = $employee->employee_status ?? '-';
                        $employeeId = $employee->id;
                        $hasAttendance = $employee->attendances->isNotEmpty();

                        // Check for override
                        $override = RoomStatusOverride::where('employee_id', $employee->id)
                            ->where('location', $location)
                            ->where('date', $dateCarbon->toDateString())
                            ->first();

                        if ($override) {
                            $hasAttendance = ($override->status === 'ON');
                            $isOverride = true;
                        }

                        if ($hasAttendance)
                            $on++;
                        else
                            $off++;
                    } else {
                        $vacant++;
                    }

                    $groupData[] = [
                        'room' => $room->room_code,
                        'name' => $name,
                        'department' => $department,
                        'emp_status' => $empStatus,
                        'status' => $name === 'VACANT' ? '' : ($hasAttendance ? 'ON' : 'OFF'),
                        'is_vacant' => $name === 'VACANT',
                        'is_continuation' => $slot > 0,  // Mark continuation rows
                        'is_non_fillable' => false,
                        'is_override' => $isOverride,
                        'employee_id' => $employeeId,
                        'capacity' => $capacity,
                        'slot' => $slot,
                    ];
                }
            }

            $columns[] = $groupData;
            $totals[] = ['on' => $on, 'off' => $off, 'vacant' => $vacant];
        }

        $locations = ['Ramba', 'Mangunjaya', 'Keluang', 'Bentayan'];
        $groupNames = $roomGroups->pluck('name')->toArray();

        return view('rooms.dashboard', compact('location', 'date', 'columns', 'totals', 'locations', 'groupNames'));
    }

    /**
     * Legacy method using employee accommodation data directly.
     */
    private function indexLegacy(Request $request)
    {
        $location = $request->get('location', 'Ramba');
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $dateCarbon = Carbon::parse($date);

        $employees = Employee::where('active_status', 'active')
            ->whereNotNull('accommodation')
            ->get()
            ->filter(function ($employee) use ($location) {
                return !empty($employee->accommodation[$location] ?? null);
            });

        $roomData = [];
        foreach ($employees as $employee) {
            $room = $employee->accommodation[$location];
            if (!isset($roomData[$room])) {
                $roomData[$room] = [];
            }

            $hasAttendance = $employee->attendances()
                ->whereDate('scanned_at', $dateCarbon->toDateString())
                ->where('location', $location)
                ->exists();

            $roomData[$room][] = [
                'room' => $room,
                'name' => strtoupper($employee->name),
                'status' => $hasAttendance ? 'ON' : 'OFF',
                'is_vacant' => false,
            ];
        }

        uksort($roomData, 'strnatcmp');

        $flatData = [];
        foreach ($roomData as $room => $occupants) {
            foreach ($occupants as $occupant) {
                $flatData[] = $occupant;
            }
        }

        $columns = [array_slice($flatData, 0, ceil(count($flatData) / 4))];
        for ($i = 1; $i < 4; $i++) {
            $columns[] = array_slice($flatData, $i * ceil(count($flatData) / 4), ceil(count($flatData) / 4));
        }

        $totals = array_map(function ($column) {
            $on = 0;
            $off = 0;
            foreach ($column as $cell) {
                if ($cell['status'] === 'ON')
                    $on++;
                else
                    $off++;
            }
            return ['on' => $on, 'off' => $off];
        }, $columns);

        $locations = ['Ramba', 'Mangunjaya', 'Keluang', 'Bentayan'];
        $groupNames = [];

        return view('rooms.dashboard', compact('location', 'date', 'columns', 'totals', 'locations', 'groupNames'));
    }

    /**
     * Manage rooms and room groups.
     */
    public function manage(Request $request)
    {
        $location = $request->get('location', 'Ramba');

        $roomGroups = RoomGroup::where('location', $location)
            ->orderBy('order')
            ->with([
                'rooms' => function ($q) {
                    $q->orderBy('order');
                }
            ])
            ->get();

        $locations = ['Ramba', 'Mangunjaya', 'Keluang', 'Bentayan'];

        return view('rooms.manage', compact('location', 'roomGroups', 'locations'));
    }

    /**
     * Store a new room group.
     */
    public function storeGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|in:Ramba,Mangunjaya,Keluang,Bentayan',
            'rooms' => 'required|string', // comma-separated room codes
            'capacity' => 'nullable|integer|min:1|max:10',
        ]);

        $capacity = $validated['capacity'] ?? 1;
        $maxOrder = RoomGroup::where('location', $validated['location'])->max('order') ?? 0;

        $group = RoomGroup::create([
            'name' => $validated['name'],
            'location' => $validated['location'],
            'order' => $maxOrder + 1,
        ]);

        // Parse rooms
        $roomCodes = array_map('trim', explode(',', $validated['rooms']));
        foreach ($roomCodes as $index => $code) {
            if (!empty($code)) {
                Room::create([
                    'room_code' => $code,
                    'capacity' => $capacity,
                    'room_group_id' => $group->id,
                    'location' => $validated['location'],
                    'order' => $index,
                ]);
            }
        }

        return redirect()->route('rooms.manage', ['location' => $validated['location']])
            ->with('success', "Room group '{$validated['name']}' created with " . count($roomCodes) . " rooms.");
    }

    /**
     * Delete a room group.
     */
    public function destroyGroup($id)
    {
        $group = RoomGroup::findOrFail($id);
        $location = $group->location;
        $group->delete();

        return redirect()->route('rooms.manage', ['location' => $location])
            ->with('success', 'Room group deleted successfully.');
    }

    /**
     * Show edit form for a room group.
     */
    public function editGroup($id)
    {
        $group = RoomGroup::with('rooms')->findOrFail($id);
        $locations = ['Ramba', 'Mangunjaya', 'Keluang', 'Bentayan'];

        return view('rooms.edit-group', compact('group', 'locations'));
    }

    /**
     * Update a room group.
     */
    public function updateGroup(Request $request, $id)
    {
        $group = RoomGroup::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $group->update($validated);

        return redirect()->route('rooms.manage', ['location' => $group->location])
            ->with('success', "Room group '{$group->name}' updated.");
    }

    /**
     * Show edit form for a room.
     */
    public function editRoom($id)
    {
        $room = Room::with('roomGroup')->findOrFail($id);

        return view('rooms.edit-room', compact('room'));
    }

    /**
     * Update a room.
     */
    public function updateRoom(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        $validated = $request->validate([
            'room_code' => 'required|string|max:50',
            'capacity' => 'required|integer|min:0|max:10',
            'is_fillable' => 'boolean',
            'notes' => 'nullable|string|max:255',
        ]);

        $validated['is_fillable'] = $request->has('is_fillable');

        $room->update($validated);

        return redirect()->route('rooms.manage', ['location' => $room->location])
            ->with('success', "Room '{$room->room_code}' updated.");
    }

    /**
     * Delete a single room.
     */
    public function destroyRoom($id)
    {
        $room = Room::findOrFail($id);
        $location = $room->location;
        $room->delete();

        return redirect()->route('rooms.manage', ['location' => $location])
            ->with('success', 'Room deleted successfully.');
    }

    /**
     * Get room suggestions for autocomplete (API endpoint).
     */
    public function getRoomSuggestions(Request $request)
    {
        $location = $request->get('location');
        $query = $request->get('q', '');

        $roomGroups = RoomGroup::with('rooms')
            ->when($location, function ($q) use ($location) {
                $q->where('location', $location);
            })
            ->get();

        $suggestions = [];
        foreach ($roomGroups as $group) {
            foreach ($group->rooms as $room) {
                if ($room->is_fillable && $room->capacity > 0) {
                    $fullKey = $group->name . ' ' . $room->room_code;
                    if (empty($query) || stripos($fullKey, $query) !== false) {
                        $suggestions[] = [
                            'value' => $fullKey,
                            'label' => $fullKey . ' (' . $group->location . ')',
                            'location' => $group->location,
                        ];
                    }
                }
            }
        }

        return response()->json($suggestions);
    }

    /**
     * Toggle status override for an employee on a specific date.
     */
    public function toggleOverride(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required',
            'location' => 'required|string',
            'date' => 'required|date',
            'status' => 'required|in:ON,OFF',
        ]);

        $existing = RoomStatusOverride::where('employee_id', $validated['employee_id'])
            ->where('location', $validated['location'])
            ->where('date', $validated['date'])
            ->first();

        if ($existing) {
            // If override already exists, clicking again removes it (reverts to original)
            $existing->delete();
            return response()->json(['success' => true, 'action' => 'removed']);
        }

        // Create new override
        RoomStatusOverride::create([
            'employee_id' => $validated['employee_id'],
            'location' => $validated['location'],
            'date' => $validated['date'],
            'status' => $validated['status'],
            'user_id' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'action' => 'created']);
    }

    /**
     * Export room dashboard to Excel.
     */
    public function exportExcel(Request $request)
    {
        $location = $request->get('location', 'Ramba');
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $dateCarbon = Carbon::parse($date);

        // Get room groups for this location
        $roomGroups = RoomGroup::where('location', $location)
            ->orderBy('order')
            ->with([
                'rooms' => function ($q) {
                    $q->orderBy('order');
                }
            ])
            ->get();

        if ($roomGroups->isEmpty()) {
            return redirect()->back()->with('error', 'No room groups found for export.');
        }

        // Get all active employees with accommodation at this location
        $employees = Employee::where('active_status', 'active')
            ->whereNotNull('accommodation')
            ->with([
                'attendances' => function ($q) use ($dateCarbon, $location) {
                    $q->whereDate('scanned_at', $dateCarbon->toDateString())
                        ->where('location', $location);
                }
            ])
            ->get();

        // Group employees by their accommodation at this location
        $employeesByRoom = [];
        foreach ($employees as $emp) {
            $roomKey = $emp->accommodation[$location] ?? null;
            if ($roomKey) {
                if (!isset($employeesByRoom[$roomKey])) {
                    $employeesByRoom[$roomKey] = [];
                }
                $employeesByRoom[$roomKey][] = $emp;
            }
        }

        // Build export data
        $exportData = [];
        foreach ($roomGroups as $group) {
            foreach ($group->rooms as $room) {
                $fullRoomKey = $group->name . ' ' . $room->room_code;
                $roomOccupants = $employeesByRoom[$fullRoomKey] ?? [];
                $capacity = $room->capacity ?? 1;
                $isFillable = $room->is_fillable ?? true;

                if (!$isFillable || $capacity == 0) {
                    $exportData[] = [
                        'group' => $group->name,
                        'room' => $room->room_code,
                        'name' => $room->notes ?? 'N/A',
                        'department' => '-',
                        'employee_status' => '-',
                        'status' => '-',
                    ];
                    continue;
                }

                for ($slot = 0; $slot < $capacity; $slot++) {
                    $employee = $roomOccupants[$slot] ?? null;
                    $name = 'VACANT';
                    $department = '';
                    $employeeStatus = '';
                    $status = '';

                    if ($employee) {
                        $name = $employee->name;
                        $department = $employee->department ?? '-';
                        $employeeStatus = $employee->employee_status ?? '-';
                        $hasAttendance = $employee->attendances->isNotEmpty();

                        // Check for override
                        $override = RoomStatusOverride::where('employee_id', $employee->id)
                            ->where('location', $location)
                            ->where('date', $dateCarbon->toDateString())
                            ->first();

                        if ($override) {
                            $hasAttendance = ($override->status === 'ON');
                        }

                        $status = $hasAttendance ? 'ON' : 'OFF';
                    }

                    $exportData[] = [
                        'group' => $group->name,
                        'room' => $room->room_code,
                        'name' => $name,
                        'department' => $department,
                        'employee_status' => $employeeStatus,
                        'status' => $status,
                        'capacity' => $capacity,
                        'slot' => $slot,
                    ];
                }
            }
        }

        // Generate Excel file using PhpSpreadsheet - grouped by room group
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('POB Kamar');

        // Row 1 - empty spacing row (merged)
        $sheet->mergeCells('A1:F1');

        // Title Row (Row 2)
        $sheet->setCellValue('A2', "POB KAMAR $location - " . Carbon::parse($date)->format('d F Y'));
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Group data by room group
        $groupedData = [];
        foreach ($exportData as $item) {
            $groupedData[$item['group']][] = $item;
        }

        $row = 4;
        $totalOn = 0;
        $totalOff = 0;

        // Header style (no color, just bold with borders)
        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        // Section title style (gray background)
        $sectionStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BFBFBF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        // Vacant style (bold red text, no background)
        $vacantFontStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FF0000']],
        ];

        foreach ($groupedData as $groupName => $items) {
            // Group section title - starts at column B
            $sheet->setCellValue("B{$row}", $groupName);
            $sheet->mergeCells("B{$row}:F{$row}");
            $sheet->getStyle("B{$row}:F{$row}")->applyFromArray($sectionStyle);
            $row++;

            // Column headers for this group - starts at column B (no color)
            $sheet->setCellValue("B{$row}", 'Room');
            $sheet->setCellValue("C{$row}", 'Name');
            $sheet->setCellValue("D{$row}", 'Department');
            $sheet->setCellValue("E{$row}", 'Emp. Status');
            $sheet->setCellValue("F{$row}", 'Attendance');
            $sheet->getStyle("B{$row}:F{$row}")->applyFromArray($headerStyle);
            $row++;

            // Track per-group totals
            $groupOn = 0;
            $groupOff = 0;
            $groupVacant = 0;

            // Data rows for this group
            foreach ($items as $data) {
                $capacity = $data['capacity'] ?? 1;
                $slot = $data['slot'] ?? 0;

                // Only write room code on first slot
                if ($slot === 0) {
                    $sheet->setCellValue("B{$row}", $data['room']);

                    // Merge room cell if capacity > 1
                    if ($capacity > 1) {
                        $mergeEndRow = $row + $capacity - 1;
                        $sheet->mergeCells("B{$row}:B{$mergeEndRow}");
                        $sheet->getStyle("B{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    }
                }

                $sheet->setCellValue("C{$row}", $data['name']);
                $sheet->setCellValue("D{$row}", $data['department']);
                $sheet->setCellValue("E{$row}", $data['employee_status']);
                $sheet->setCellValue("F{$row}", $data['status']);

                // Style based on status
                if ($data['name'] === 'VACANT') {
                    // Bold red text for Room and VACANT cells
                    if ($slot === 0) {
                        $sheet->getStyle("B{$row}")->applyFromArray($vacantFontStyle);
                    }
                    $sheet->getStyle("C{$row}")->applyFromArray($vacantFontStyle);
                    $groupVacant++;
                } else {
                    if ($data['status'] === 'ON') {
                        $sheet->getStyle("F{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('00CC66');
                        $groupOn++;
                        $totalOn++;
                    } elseif ($data['status'] === 'OFF') {
                        $sheet->getStyle("F{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('FF6666');
                        $groupOff++;
                        $totalOff++;
                    }
                }

                $sheet->getStyle("B{$row}:F{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $row++;
            }

            // Total ON row
            $sheet->setCellValue("B{$row}", "Total ON: {$groupOn}");
            $sheet->getStyle("B{$row}")->getFont()->setBold(true);
            $row++;

            // Total OFF row
            $sheet->setCellValue("B{$row}", "Total OFF: {$groupOff}");
            $sheet->getStyle("B{$row}")->getFont()->setBold(true);
            $row++;

            // Total Vacant row
            $sheet->setCellValue("B{$row}", "Total Vacant: {$groupVacant}");
            $sheet->getStyle("B{$row}")->getFont()->setBold(true);
            $row++;

            // Empty row after each group
            $row++;
        }

        // Auto-size columns
        foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Generate file
        $filename = "POB_{$location}_{$date}.xlsx";
        $tempFile = tempnam(sys_get_temp_dir(), 'pob_');

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
