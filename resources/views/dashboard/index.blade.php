@extends('layouts.app')

@section('content')
    <div class="page-header d-flex justify-between align-items-center">
        <div>
            <h1 class="page-title">DASHBOARD</h1>
            <p class="page-subtitle">Meal Attendance Monitoring</p>
        </div>
    </div>

    <!-- Attendance Calendar by Location -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="card-title">📅 Attendance Calendar</h2>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <select id="calendarMonth" class="form-control"
                    style="width: auto; padding: 0.25rem 0.5rem; font-size: 0.875rem;" onchange="changeCalendarMonth()">
                    @php
                        for ($i = 0; $i < 12; $i++) {
                            $monthDate = now()->subMonths($i);
                            $monthValue = $monthDate->format('Y-m');
                            $monthLabel = $monthDate->format('F Y');
                            $selected = $monthValue == $calendarMonth ? 'selected' : '';
                            echo "<option value='{$monthValue}' {$selected}>{$monthLabel}</option>";
                        }
                    @endphp
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            @foreach($locations as $location)
                @php
                    $daysInMonth = $calendarStart->daysInMonth;
                    $firstDayOfWeek = $calendarStart->dayOfWeek;
                    $today = now()->format('Y-m-d');
                    $locationData = $calendarData[$location] ?? [];
                @endphp
                <div style="background: rgba(255,255,255,0.02); border-radius: 8px; padding: 0.75rem;">
                    <h4 style="margin: 0 0 0.5rem 0; font-size: 0.9rem; color: var(--accent);">{{ $location }}</h4>

                    <!-- Day Headers -->
                    <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px; margin-bottom: 3px;">
                        @foreach(['S', 'M', 'T', 'W', 'T', 'F', 'S'] as $day)
                            <div style="text-align: center; font-size: 0.6rem; color: var(--text-muted);">{{ $day }}</div>
                        @endforeach
                    </div>

                    <!-- Calendar Days -->
                    <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px;">
                        @for($i = 0; $i < $firstDayOfWeek; $i++)
                            <div style="aspect-ratio: 1;"></div>
                        @endfor

                        @for($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $dateStr = $calendarStart->copy()->addDays($day - 1)->format('Y-m-d');
                                $hasData = isset($locationData[$dateStr]);
                                $mealCount = $locationData[$dateStr] ?? 0;
                                $isToday = $dateStr == $today;
                                $isFuture = $dateStr > $today;
                                $isLocked = isset($lockedDates[$location][$dateStr]);

                                if ($isFuture) {
                                    $bgColor = 'rgba(128, 128, 128, 0.1)';
                                } elseif ($isLocked && $hasData) {
                                    $bgColor = 'rgba(255, 69, 0, 0.6)'; // Orange-red for locked with data
                                } elseif ($isLocked) {
                                    $bgColor = 'rgba(255, 69, 0, 0.3)'; // Light orange for locked no data
                                } elseif ($hasData) {
                                    $bgColor = 'rgba(0, 200, 83, 0.4)';
                                } else {
                                    $bgColor = 'rgba(100, 100, 100, 0.2)';
                                }

                                $tooltip = $hasData ? $mealCount . ' meals' : 'No data';
                                if ($isLocked)
                                    $tooltip .= ' (🔒 Locked)';
                            @endphp
                            <a href="{{ route('dashboard', ['date_from' => $dateStr, 'date_to' => $dateStr, 'calendar_month' => $calendarMonth]) }}"
                                style="aspect-ratio: 1; background: {{ $bgColor }}; border-radius: 3px; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; text-decoration: none; color: inherit; {{ $isToday ? 'border: 1px solid var(--primary);' : '' }}"
                                title="{{ $tooltip }}">
                                {{ $day }}
                            </a>
                        @endfor
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Legend -->
        <div
            style="display: flex; gap: 1rem; margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid var(--card-border); font-size: 0.75rem; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 0.25rem;">
                <div style="width: 12px; height: 12px; background: rgba(0, 200, 83, 0.4); border-radius: 2px;"></div>
                <span>Has Data</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.25rem;">
                <div style="width: 12px; height: 12px; background: rgba(100, 100, 100, 0.2); border-radius: 2px;"></div>
                <span>No Data</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.25rem;">
                <div style="width: 12px; height: 12px; background: rgba(255, 69, 0, 0.5); border-radius: 2px;"></div>
                <span>🔒 Locked</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.25rem;">
                <div style="width: 12px; height: 12px; border: 1px solid var(--primary); border-radius: 2px;"></div>
                <span>Today</span>
            </div>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Filter by Date</h2>
        </div>
        <form action="{{ route('dashboard') }}" method="GET" class="filter-bar">
            <input type="hidden" name="calendar_month" value="{{ $calendarMonth }}">
            <div class="form-group">
                <label class="form-label">From</label>
                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
            </div>
            <div class="form-group">
                <label class="form-label">To</label>
                <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
            </div>
            <div class="d-flex gap-1" style="align-items: flex-end;">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-funnel"></i> Apply
                </button>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-x"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Stats by Location (Moved before Estimated Invoice) -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">📍 By Location
                ({{ $dateFrom == $dateTo ? $dateFrom : $dateFrom . ' - ' . $dateTo }})</h2>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Location</th>
                        <th class="text-center">🌅 B'fast</th>
                        <th class="text-center">☀️ Lunch</th>
                        <th class="text-center">🌙 Dinner</th>
                        <th class="text-center">🌃 Supper</th>
                        <th class="text-center">🍿 Snack</th>
                        <th class="text-center">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($statsByLocation as $location => $stats)
                        <tr>
                            <td><strong>{{ $location }}</strong></td>
                            <td class="text-center">{{ $stats['breakfast'] }}</td>
                            <td class="text-center">{{ $stats['lunch'] }}</td>
                            <td class="text-center">{{ $stats['dinner'] }}</td>
                            <td class="text-center">{{ $stats['supper'] }}</td>
                            <td class="text-center">{{ $stats['snack'] }}</td>
                            <td class="text-center"><strong style="color: var(--accent);">{{ $stats['total'] }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 2rem; color: var(--text-muted);">
                                No data for selected period
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background: rgba(255, 69, 0, 0.1);">
                        <td><strong>GRAND TOTAL</strong></td>
                        <td class="text-center"><strong>{{ $totalStats['breakfast'] }}</strong></td>
                        <td class="text-center"><strong>{{ $totalStats['lunch'] }}</strong></td>
                        <td class="text-center"><strong>{{ $totalStats['dinner'] }}</strong></td>
                        <td class="text-center"><strong>{{ $totalStats['supper'] }}</strong></td>
                        <td class="text-center"><strong>{{ $totalStats['snack'] }}</strong></td>
                        <td class="text-center"><strong
                                style="color: var(--accent); font-size: 1.2rem;">{{ $totalStats['total'] }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Breakdown by Date and Employee Status -->
    <div class="card">
        <div class="card-header"
            style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <h2 class="card-title">👥 Breakdown by Date & Employee Status</h2>
            <form method="GET" style="display: flex; gap: 0.5rem; align-items: center;">
                <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                <input type="hidden" name="date_to" value="{{ $dateTo }}">
                <input type="hidden" name="calendar_month" value="{{ $calendarMonth }}">
                <label style="font-size: 0.85rem;">Location:</label>
                <select name="breakdown_location" class="form-control" style="width: auto; padding: 0.25rem 0.5rem;"
                    onchange="this.form.submit()">
                    @foreach($locations as $loc)
                        <option value="{{ $loc }}" {{ $breakdownLocation == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="table-container" style="max-height: 600px; overflow-y: auto;">
            <table class="data-table">
                <thead style="position: sticky; top: 0; background: #1a1a2e; z-index: 1;">
                    <tr>
                        <th>DATE</th>
                        <th>STATUS</th>
                        <th class="text-center">🌅 B'FAST</th>
                        <th class="text-center">☀️ LUNCH</th>
                        <th class="text-center">🌙 DINNER</th>
                        <th class="text-center">🌃 SUPPER</th>
                        <th class="text-center">🍪 SNACK</th>
                        <th class="text-center">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dates as $date)
                        @php $dateRowCount = count($employeeStatuses) + 1; @endphp
                        @foreach($employeeStatuses as $statusIdx => $status)
                            @php
                                $stats = $statsByDate[$date][$status];
                                $isFirstRow = $statusIdx === 0;
                            @endphp
                            <tr style="{{ $isFirstRow ? 'border-top: 2px solid var(--primary);' : '' }}">
                                @if($isFirstRow)
                                    <td rowspan="{{ $dateRowCount }}"
                                        style="vertical-align: middle; background: rgba(255, 69, 0, 0.1);">
                                        <strong>{{ \Carbon\Carbon::parse($date)->format('d M') }}</strong>
                                        <div style="font-size: 0.7rem; color: var(--text-muted);">
                                            {{ \Carbon\Carbon::parse($date)->format('D') }}
                                        </div>
                                    </td>
                                @endif
                                <td>{{ $status }}</td>
                                <td class="text-center meal-count-cell" data-date="{{ $date }}"
                                    data-location="{{ $breakdownLocation }}" data-status="{{ $status }}" data-meal="breakfast"
                                    style="{{ $stats['breakfast'] ? 'cursor: pointer;' : '' }}"
                                    onclick="{{ $stats['breakfast'] ? 'showAttendanceDetails(this)' : '' }}">
                                    {{ $stats['breakfast'] ?: '-' }}</td>
                                <td class="text-center meal-count-cell" data-date="{{ $date }}"
                                    data-location="{{ $breakdownLocation }}" data-status="{{ $status }}" data-meal="lunch"
                                    style="{{ $stats['lunch'] ? 'cursor: pointer;' : '' }}"
                                    onclick="{{ $stats['lunch'] ? 'showAttendanceDetails(this)' : '' }}">
                                    {{ $stats['lunch'] ?: '-' }}</td>
                                <td class="text-center meal-count-cell" data-date="{{ $date }}"
                                    data-location="{{ $breakdownLocation }}" data-status="{{ $status }}" data-meal="dinner"
                                    style="{{ $stats['dinner'] ? 'cursor: pointer;' : '' }}"
                                    onclick="{{ $stats['dinner'] ? 'showAttendanceDetails(this)' : '' }}">
                                    {{ $stats['dinner'] ?: '-' }}</td>
                                <td class="text-center meal-count-cell" data-date="{{ $date }}"
                                    data-location="{{ $breakdownLocation }}" data-status="{{ $status }}" data-meal="supper"
                                    style="{{ $stats['supper'] ? 'cursor: pointer;' : '' }}"
                                    onclick="{{ $stats['supper'] ? 'showAttendanceDetails(this)' : '' }}">
                                    {{ $stats['supper'] ?: '-' }}</td>
                                <td class="text-center meal-count-cell" data-date="{{ $date }}"
                                    data-location="{{ $breakdownLocation }}" data-status="{{ $status }}" data-meal="snack"
                                    style="{{ $stats['snack'] ? 'cursor: pointer;' : '' }}"
                                    onclick="{{ $stats['snack'] ? 'showAttendanceDetails(this)' : '' }}">
                                    {{ $stats['snack'] ?: '-' }}</td>
                                <td class="text-center"><strong>{{ $stats['total'] ?: '-' }}</strong></td>
                            </tr>
                        @endforeach
                        <!-- Daily Total Row -->
                        <tr style="background: rgba(255, 165, 0, 0.15);">
                            <td><strong>Daily Total</strong></td>
                            <td class="text-center"><strong>{{ $dailyTotals[$date]['breakfast'] }}</strong></td>
                            <td class="text-center"><strong>{{ $dailyTotals[$date]['lunch'] }}</strong></td>
                            <td class="text-center"><strong>{{ $dailyTotals[$date]['dinner'] }}</strong></td>
                            <td class="text-center"><strong>{{ $dailyTotals[$date]['supper'] }}</strong></td>
                            <td class="text-center"><strong>{{ $dailyTotals[$date]['snack'] }}</strong></td>
                            <td class="text-center"><strong
                                    style="color: var(--accent);">{{ $dailyTotals[$date]['total'] }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Estimated Invoice Per Location -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">💰 Estimated Invoice by Location
                ({{ $dateFrom == $dateTo ? $dateFrom : $dateFrom . ' - ' . $dateTo }})</h2>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>LOCATION</th>
                        <th class="text-center">🌅 B'FAST<div
                                style="font-size: 0.7rem; font-weight: normal; color: var(--text-muted);">Rp
                                {{ number_format($mealPrices->breakfast_price, 0, ',', '.') }}/pax
                            </div>
                        </th>
                        <th class="text-center">☀️ LUNCH<div
                                style="font-size: 0.7rem; font-weight: normal; color: var(--text-muted);">Rp
                                {{ number_format($mealPrices->lunch_price, 0, ',', '.') }}/pax
                            </div>
                        </th>
                        <th class="text-center">🌙 DINNER<div
                                style="font-size: 0.7rem; font-weight: normal; color: var(--text-muted);">Rp
                                {{ number_format($mealPrices->dinner_price, 0, ',', '.') }}/pax
                            </div>
                        </th>
                        <th class="text-center">🌃 SUPPER<div
                                style="font-size: 0.7rem; font-weight: normal; color: var(--text-muted);">Rp
                                {{ number_format($mealPrices->supper_price, 0, ',', '.') }}/pax
                            </div>
                        </th>
                        <th class="text-center">🍪 SNACK<div
                                style="font-size: 0.7rem; font-weight: normal; color: var(--text-muted);">Rp
                                {{ number_format($mealPrices->snack_price, 0, ',', '.') }}/pax
                            </div>
                        </th>
                        <th class="text-right">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($locations as $location)
                        <tr>
                            <td><strong>{{ $location }}</strong></td>
                            <td class="text-center">
                                <div style="color: var(--accent);">Rp
                                    {{ number_format($invoiceByLocation[$location]['breakfast'], 0, ',', '.') }}
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                    {{ $statsByLocation[$location]['breakfast'] }} pax
                                </div>
                            </td>
                            <td class="text-center">
                                <div style="color: var(--accent);">Rp
                                    {{ number_format($invoiceByLocation[$location]['lunch'], 0, ',', '.') }}
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                    {{ $statsByLocation[$location]['lunch'] }} pax
                                </div>
                            </td>
                            <td class="text-center">
                                <div style="color: var(--accent);">Rp
                                    {{ number_format($invoiceByLocation[$location]['dinner'], 0, ',', '.') }}
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                    {{ $statsByLocation[$location]['dinner'] }} pax
                                </div>
                            </td>
                            <td class="text-center">
                                <div style="color: var(--accent);">Rp
                                    {{ number_format($invoiceByLocation[$location]['supper'], 0, ',', '.') }}
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                    {{ $statsByLocation[$location]['supper'] }} pax
                                </div>
                            </td>
                            <td class="text-center">
                                <div style="color: var(--accent);">Rp
                                    {{ number_format($invoiceByLocation[$location]['snack'], 0, ',', '.') }}
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                    {{ $statsByLocation[$location]['snack'] }} pax
                                </div>
                            </td>
                            <td class="text-right">
                                <div style="color: #ffc107; font-weight: bold;">Rp
                                    {{ number_format($invoiceByLocation[$location]['total'], 0, ',', '.') }}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: rgba(255, 69, 0, 0.1); font-weight: bold;">
                        <td colspan="6"><strong>GRAND TOTAL</strong></td>
                        <td class="text-right" style="color: #ffc107; font-size: 1.2rem;">
                            Rp {{ number_format($estimatedInvoice['total'], 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Meal Prices Settings (Super Admin Only) -->
    @if(auth()->user()->isSuperAdmin())
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">⚙️ Meal Price Settings</h2>
            </div>
            <form action="{{ route('dashboard.updatePrices') }}" method="POST" style="padding: 1rem;">
                @csrf
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-end;">
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 120px;">
                        <label class="form-label" style="font-size: 0.8rem;">🌅 Breakfast</label>
                        <input type="number" name="breakfast_price" class="form-control" style="padding: 0.4rem 0.6rem;"
                            value="{{ $mealPrices->breakfast_price }}" min="0" step="1" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 120px;">
                        <label class="form-label" style="font-size: 0.8rem;">☀️ Lunch</label>
                        <input type="number" name="lunch_price" class="form-control" style="padding: 0.4rem 0.6rem;"
                            value="{{ $mealPrices->lunch_price }}" min="0" step="1" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 120px;">
                        <label class="form-label" style="font-size: 0.8rem;">🌙 Dinner</label>
                        <input type="number" name="dinner_price" class="form-control" style="padding: 0.4rem 0.6rem;"
                            value="{{ $mealPrices->dinner_price }}" min="0" step="1" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 120px;">
                        <label class="form-label" style="font-size: 0.8rem;">🌃 Supper</label>
                        <input type="number" name="supper_price" class="form-control" style="padding: 0.4rem 0.6rem;"
                            value="{{ $mealPrices->supper_price }}" min="0" step="1" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 120px;">
                        <label class="form-label" style="font-size: 0.8rem;">🍿 Snack</label>
                        <input type="number" name="snack_price" class="form-control" style="padding: 0.4rem 0.6rem;"
                            value="{{ $mealPrices->snack_price }}" min="0" step="1" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-floppy"></i> Save
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Quick Actions</h2>
        </div>
        <div class="d-flex gap-1" style="flex-wrap: wrap;">
            <a href="{{ route('scan.index') }}" class="btn btn-primary">
                <i class="bi bi-qr-code-scan"></i> QR Scan
            </a>
            <a href="{{ route('bulk.index') }}" class="btn btn-secondary">
                <i class="bi bi-list-check"></i> Bulk Input
            </a>
            <a href="{{ route('historical.exportForm') }}" class="btn btn-secondary">
                <i class="bi bi-file-earmark-excel"></i> Export Data
            </a>
            <a href="{{ route('historical.index') }}" class="btn btn-secondary">
                <i class="bi bi-clock-history"></i> View Historical
            </a>
        </div>
    </div>

    <!-- Attendance Details Modal -->
    <div id="attendanceModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center;">
        <div
            style="background: #1a1a2e; border: 1px solid var(--card-border); border-radius: 12px; max-width: 800px; width: 90%; max-height: 80vh; overflow: hidden; display: flex; flex-direction: column;">
            <!-- Modal Header -->
            <div
                style="padding: 1.5rem; border-bottom: 1px solid var(--card-border); display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0; color: var(--accent); font-size: 1.25rem;">📋 Attendance Details</h3>
                    <p id="modalFilters" style="margin: 0.5rem 0 0 0; font-size: 0.85rem; color: var(--text-muted);"></p>
                </div>
                <button onclick="closeAttendanceModal()"
                    style="background: none; border: none; color: var(--text-muted); font-size: 1.5rem; cursor: pointer; padding: 0; width: 30px; height: 30px;">&times;</button>
            </div>

            <!-- Modal Body -->
            <div id="modalBody" style="padding: 1.5rem; overflow-y: auto; flex: 1;">
                <div id="loadingState" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                    <div style="font-size: 2rem;">⏳</div>
                    <p>Loading attendance details...</p>
                </div>

                <div id="contentState" style="display: none;">
                    <table class="data-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="text-align: left;">#</th>
                                <th style="text-align: left;">Name</th>
                                <th style="text-align: left;">Department</th>
                                <th style="text-align: left;">Recorded By</th>
                            </tr>
                        </thead>
                        <tbody id="attendanceTableBody">
                        </tbody>
                    </table>
                    <div id="emptyState"
                        style="display: none; text-align: center; padding: 2rem; color: var(--text-muted);">
                        <div style="font-size: 2rem;">📭</div>
                        <p>No attendance records found</p>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--card-border); text-align: right;">
                <span id="recordCount" style="color: var(--text-muted); margin-right: 1rem;"></span>
                <button onclick="closeAttendanceModal()" class="btn btn-secondary btn-sm">Close</button>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(26, 8, 8, 0.9), rgba(13, 4, 4, 0.95));
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            border-color: var(--primary);
            box-shadow: 0 10px 30px rgba(255, 69, 0, 0.2);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-value {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--accent);
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .meal-stats-row {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
            padding: 1rem;
        }

        .meal-stat {
            flex: 1;
            min-width: 120px;
            max-width: 180px;
            text-align: center;
            padding: 1.5rem 1rem;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--card-border);
            transition: all 0.3s ease;
        }

        .meal-stat:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
        }

        .meal-stat-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .meal-stat-value {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .meal-stat-label {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }

        .meal-stat.breakfast {
            border-top: 3px solid #FFD700;
        }

        .meal-stat.lunch {
            border-top: 3px solid #FF8C00;
        }

        .meal-stat.dinner {
            border-top: 3px solid #FF4500;
        }

        .meal-stat.supper {
            border-top: 3px solid #8B0000;
        }

        .meal-stat.snack {
            border-top: 3px solid #9B59B6;
        }

        .meal-stat.total {
            border-top: 3px solid var(--accent);
            background: rgba(255, 69, 0, 0.1);
        }

        .meal-stat.total .meal-stat-value {
            color: var(--accent);
        }

        .text-center {
            text-align: center;
        }
    </style>
@endpush

@push('scripts')
    <script>
        function changeCalendarMonth() {
            const month = document.getElementById('calendarMonth').value;
            const url = new URL(window.location.href);
            url.searchParams.set('calendar_month', month);
            // Remove date filter params so the controller will auto-sync date range to the selected month
            url.searchParams.delete('date_from');
            url.searchParams.delete('date_to');
            window.location.href = url.toString();
        }

        function showAttendanceDetails(cell) {
            const date = cell.dataset.date;
            const location = cell.dataset.location;
            const status = cell.dataset.status;
            const mealType = cell.dataset.meal;

            // Show modal
            const modal = document.getElementById('attendanceModal');
            modal.style.display = 'flex';

            // Show loading state
            document.getElementById('loadingState').style.display = 'block';
            document.getElementById('contentState').style.display = 'none';

            // Fetch attendance details
            const url = new URL('{{ route('dashboard.attendanceDetails') }}');
            url.searchParams.set('date', date);
            url.searchParams.set('location', location);
            url.searchParams.set('status', status);
            url.searchParams.set('meal_type', mealType);

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    // Hide loading
                    document.getElementById('loadingState').style.display = 'none';
                    document.getElementById('contentState').style.display = 'block';

                    // Update modal filters
                    document.getElementById('modalFilters').textContent =
                        `${data.filters.date} • ${data.filters.location} • ${data.filters.status} • ${data.filters.meal_type}`;

                    // Update record count
                    document.getElementById('recordCount').textContent =
                        `${data.count} record${data.count !== 1 ? 's' : ''}`;

                    // Populate table
                    const tbody = document.getElementById('attendanceTableBody');
                    tbody.innerHTML = '';

                    if (data.count > 0) {
                        data.data.forEach((attendance, index) => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                    <td>${index + 1}</td>
                                    <td><strong>${attendance.name}</strong></td>
                                    <td>${attendance.department}</td>
                                    <td>${attendance.recorded_by}</td>
                                `;
                            tbody.appendChild(row);
                        });
                        document.getElementById('emptyState').style.display = 'none';
                    } else {
                        document.getElementById('emptyState').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error fetching attendance details:', error);
                    document.getElementById('loadingState').style.display = 'none';
                    document.getElementById('contentState').style.display = 'block';
                    document.getElementById('emptyState').style.display = 'block';
                    document.getElementById('emptyState').innerHTML = `
                        < div style = "font-size: 2rem;" >⚠️</div >
                            <p>Error loading attendance details</p>
                    `;
                });
        }

        function closeAttendanceModal() {
            document.getElementById('attendanceModal').style.display = 'none';
        }

        // Close modal when clicking outside
        document.getElementById('attendanceModal')?.addEventListener('click', function (e) {
            if (e.target === this) {
                closeAttendanceModal();
            }
        });
    </script>
@endpush