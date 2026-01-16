@extends('layouts.app')

@section('content')
    <div class="page-header d-flex justify-between align-items-center">
        <div>
            <h1 class="page-title">EMPLOYEE DETAILS</h1>
            <p class="page-subtitle">{{ $employee->employee_number }} - {{ $employee->name }}</p>
        </div>
        <div class="d-flex gap-1">
            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-secondary">
                <i class="bi bi-pencil"></i>
                Edit
            </a>
            <a href="{{ route('employees.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Personal Information</h2>
                </div>

                <div style="display: grid; gap: 1rem;">
                    <div
                        style="display: flex; justify-content: space-between; padding: 1rem; background: rgba(255, 255, 255, 0.03); border-radius: 8px;">
                        <span style="color: var(--text-muted);">Employee Number</span>
                        <span
                            style="font-family: 'Orbitron', monospace; color: var(--accent);">{{ $employee->employee_number }}</span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; padding: 1rem; background: rgba(255, 255, 255, 0.03); border-radius: 8px;">
                        <span style="color: var(--text-muted);">Full Name</span>
                        <span>{{ $employee->name }}</span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; padding: 1rem; background: rgba(255, 255, 255, 0.03); border-radius: 8px;">
                        <span style="color: var(--text-muted);">Company</span>
                        <span>{{ $employee->company ?? '-' }}</span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; padding: 1rem; background: rgba(255, 255, 255, 0.03); border-radius: 8px;">
                        <span style="color: var(--text-muted);">Position</span>
                        <span>{{ $employee->position ?? '-' }}</span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; padding: 1rem; background: rgba(255, 255, 255, 0.03); border-radius: 8px;">
                        <span style="color: var(--text-muted);">Department</span>
                        <span>{{ $employee->department ?? '-' }}</span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; padding: 1rem; background: rgba(255, 255, 255, 0.03); border-radius: 8px;">
                        <span style="color: var(--text-muted);">Location</span>
                        <span>{{ $employee->location ?? '-' }}</span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; padding: 1rem; background: rgba(255, 255, 255, 0.03); border-radius: 8px;">
                        <span style="color: var(--text-muted);">Accommodation</span>
                        <span>
                            @if($employee->accommodation && is_array($employee->accommodation))
                                @foreach($employee->accommodation as $loc => $room)
                                    {{ $loc }}: {{ $room }}@if(!$loop->last), @endif
                                @endforeach
                            @else
                                {{ $employee->accommodation ?? '-' }}
                            @endif
                        </span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; padding: 1rem; background: rgba(255, 255, 255, 0.03); border-radius: 8px;">
                        <span style="color: var(--text-muted);">Employee Status</span>
                        <span>{{ $employee->employee_status ?? '-' }}</span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; padding: 1rem; background: rgba(255, 255, 255, 0.03); border-radius: 8px;">
                        <span style="color: var(--text-muted);">Active Status</span>
                        <span class="badge {{ $employee->active_status == 'active' ? 'badge-success' : 'badge-danger' }}">
                            {{ ucfirst($employee->active_status) }}
                        </span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; padding: 1rem; background: rgba(255, 255, 255, 0.03); border-radius: 8px;">
                        <span style="color: var(--text-muted);">Created</span>
                        <span>{{ $employee->created_at->format('d M Y, H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6">
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2 class="card-title">Meal Attendance Calendar</h2>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <select id="monthSelector" class="form-control"
                            style="width: auto; padding: 0.25rem 0.5rem; font-size: 0.875rem;" onchange="loadCalendar()">
                            @php
                                $currentMonth = request('month', date('Y-m'));
                                for ($i = 0; $i < 12; $i++) {
                                    $monthDate = now()->subMonths($i);
                                    $monthValue = $monthDate->format('Y-m');
                                    $monthLabel = $monthDate->format('F Y');
                                    $selected = $monthValue == $currentMonth ? 'selected' : '';
                                    echo "<option value='{$monthValue}' {$selected}>{$monthLabel}</option>";
                                }
                            @endphp
                        </select>
                    </div>
                </div>

                @php
                    $selectedMonth = request('month', date('Y-m'));
                    $monthStart = \Carbon\Carbon::parse($selectedMonth . '-01')->startOfMonth();
                    $monthEnd = $monthStart->copy()->endOfMonth();

                    // Get attendance dates for this employee in selected month
                    $attendanceDates = $employee->attendances()
                        ->whereBetween('scanned_at', [$monthStart, $monthEnd])
                        ->get()
                        ->groupBy(function ($item) {
                            return $item->scanned_at->format('Y-m-d');
                        });

                    $daysInMonth = $monthStart->daysInMonth;
                    $firstDayOfWeek = $monthStart->dayOfWeek; // 0=Sunday, 1=Monday, etc.
                    $today = now()->format('Y-m-d');
                @endphp

                <!-- Calendar Grid -->
                <div class="calendar-container" style="padding: 0;">
                    <!-- Day Headers -->
                    <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; margin-bottom: 0.5rem;">
                        @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                            <div style="text-align: center; font-size: 0.7rem; color: var(--text-muted); padding: 0.25rem;">
                                {{ $day }}</div>
                        @endforeach
                    </div>

                    <!-- Calendar Days -->
                    <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px;">
                        {{-- Empty cells for days before the 1st --}}
                        @for($i = 0; $i < $firstDayOfWeek; $i++)
                            <div style="aspect-ratio: 1; background: rgba(0,0,0,0.2); border-radius: 4px;"></div>
                        @endfor

                        {{-- Actual days --}}
                        @for($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $dateStr = $monthStart->copy()->addDays($day - 1)->format('Y-m-d');
                                $hasAttendance = isset($attendanceDates[$dateStr]);
                                $isPast = $dateStr < $today;
                                $isToday = $dateStr == $today;
                                $isFuture = $dateStr > $today;

                                // Get meal types for this day
                                $mealTypes = [];
                                $locations = [];
                                if ($hasAttendance) {
                                    $mealTypes = $attendanceDates[$dateStr]->pluck('meal_type')->unique()->toArray();
                                    $locations = $attendanceDates[$dateStr]->pluck('location')->unique()->map(function($loc) {
                                        return substr($loc, 0, 1); // First letter: R, B, M, K
                                    })->toArray();
                                }

                                // Determine background color
                                if ($isFuture) {
                                    $bgColor = 'rgba(128, 128, 128, 0.2)'; // Gray for future
                                    $borderColor = 'transparent';
                                } elseif ($hasAttendance) {
                                    $bgColor = 'rgba(0, 200, 83, 0.3)'; // Green for attendance
                                    $borderColor = 'rgba(0, 200, 83, 0.8)';
                                } else {
                                    $bgColor = 'rgba(255, 82, 82, 0.3)'; // Red for no attendance
                                    $borderColor = 'rgba(255, 82, 82, 0.5)';
                                }

                                if ($isToday) {
                                    $borderColor = 'var(--primary)';
                                }
                            @endphp
                            <div style="aspect-ratio: 1; background: {{ $bgColor }}; border-radius: 4px; display: flex; flex-direction: column; align-items: center; justify-content: center; font-size: 0.75rem; position: relative; border: 2px solid {{ $borderColor }}; cursor: {{ $hasAttendance ? 'pointer' : 'default' }};"
                                @if($hasAttendance) title="{{ implode(', ', array_map('ucfirst', $mealTypes)) }} @ {{ implode('/', $attendanceDates[$dateStr]->pluck('location')->unique()->toArray()) }}" @endif>
                                <span
                                    style="font-weight: {{ $isToday ? 'bold' : 'normal' }}; color: {{ $isToday ? 'var(--primary)' : 'inherit' }};">{{ $day }}</span>
                                @if($hasAttendance)
                                    <span style="font-size: 0.5rem; color: var(--accent);">{{ implode('', $locations) }}</span>
                                @endif
                            </div>
                        @endfor
                    </div>
                </div>

                <!-- Legend -->
                <div
                    style="display: flex; gap: 1rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--card-border); flex-wrap: wrap; font-size: 0.8rem;">
                    <div style="display: flex; align-items: center; gap: 0.35rem;">
                        <div
                            style="width: 16px; height: 16px; background: rgba(0, 200, 83, 0.3); border: 2px solid rgba(0, 200, 83, 0.8); border-radius: 3px;">
                        </div>
                        <span>Attended</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.35rem;">
                        <div
                            style="width: 16px; height: 16px; background: rgba(255, 82, 82, 0.3); border: 2px solid rgba(255, 82, 82, 0.5); border-radius: 3px;">
                        </div>
                        <span>No Attendance</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.35rem;">
                        <div style="width: 16px; height: 16px; background: rgba(128, 128, 128, 0.2); border-radius: 3px;">
                        </div>
                        <span>Future</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.35rem;">
                        <div style="width: 16px; height: 16px; border: 2px solid var(--primary); border-radius: 3px;"></div>
                        <span>Today</span>
                    </div>
                </div>

                <!-- Summary -->
                @php
                    $totalDaysUntilToday = min($daysInMonth, (int) now()->format('d'));
                    if ($selectedMonth < date('Y-m')) {
                        $totalDaysUntilToday = $daysInMonth; // Past month: all days
                    } elseif ($selectedMonth > date('Y-m')) {
                        $totalDaysUntilToday = 0; // Future month: no days yet
                    }
                    $attendedDays = count($attendanceDates);
                    $totalMeals = $employee->attendances()
                        ->whereBetween('scanned_at', [$monthStart, $monthEnd])
                        ->count();
                @endphp
                <div
                    style="margin-top: 1rem; padding: 0.75rem; background: rgba(255, 255, 255, 0.03); border-radius: 8px; display: flex; justify-content: space-around; text-align: center;">
                    <div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: var(--success);">{{ $attendedDays }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Days Attended</div>
                    </div>
                    <div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary);">{{ $totalMeals }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Total Meals</div>
                    </div>
                    <div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: var(--accent);">
                            {{ $totalDaysUntilToday > 0 ? round(($attendedDays / $totalDaysUntilToday) * 100) : 0 }}%
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Attendance Rate</div>
                    </div>
                </div>

                <div class="mt-2" style="text-align: center;">
                    <a href="{{ route('historical.index', ['employee_id' => $employee->id]) }}"
                        style="color: var(--primary-light); font-size: 0.875rem;">
                        View detailed meal history &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function loadCalendar() {
            const month = document.getElementById('monthSelector').value;
            const url = new URL(window.location.href);
            url.searchParams.set('month', month);
            window.location.href = url.toString();
        }
    </script>
@endpush