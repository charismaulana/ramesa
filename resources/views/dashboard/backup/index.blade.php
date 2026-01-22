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
                            <a href="{{ route('dashboard', ['date_from' => $dateStr, 'date_to' => $dateStr]) }}"
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

    <!-- Estimated Invoice (Now after By Location) -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">💰 Estimated Invoice
                ({{ $dateFrom == $dateTo ? $dateFrom : $dateFrom . ' - ' . $dateTo }})</h2>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Meal Type</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-right">Price/Unit</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>🌅 Breakfast</td>
                        <td class="text-center">{{ number_format($totalStats['breakfast']) }}</td>
                        <td class="text-right">Rp {{ number_format($mealPrices->breakfast_price, 0, ',', '.') }}</td>
                        <td class="text-right" style="color: var(--accent);">Rp
                            {{ number_format($estimatedInvoice['breakfast'], 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td>☀️ Lunch</td>
                        <td class="text-center">{{ number_format($totalStats['lunch']) }}</td>
                        <td class="text-right">Rp {{ number_format($mealPrices->lunch_price, 0, ',', '.') }}</td>
                        <td class="text-right" style="color: var(--accent);">Rp
                            {{ number_format($estimatedInvoice['lunch'], 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td>🌙 Dinner</td>
                        <td class="text-center">{{ number_format($totalStats['dinner']) }}</td>
                        <td class="text-right">Rp {{ number_format($mealPrices->dinner_price, 0, ',', '.') }}</td>
                        <td class="text-right" style="color: var(--accent);">Rp
                            {{ number_format($estimatedInvoice['dinner'], 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td>🌃 Supper</td>
                        <td class="text-center">{{ number_format($totalStats['supper']) }}</td>
                        <td class="text-right">Rp {{ number_format($mealPrices->supper_price, 0, ',', '.') }}</td>
                        <td class="text-right" style="color: var(--accent);">Rp
                            {{ number_format($estimatedInvoice['supper'], 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td>🍿 Snack</td>
                        <td class="text-center">{{ number_format($totalStats['snack']) }}</td>
                        <td class="text-right">Rp {{ number_format($mealPrices->snack_price, 0, ',', '.') }}</td>
                        <td class="text-right" style="color: var(--accent);">Rp
                            {{ number_format($estimatedInvoice['snack'], 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="background: rgba(255, 69, 0, 0.1); font-weight: bold;">
                        <td colspan="3">GRAND TOTAL</td>
                        <td class="text-right" style="color: var(--primary); font-size: 1.2rem;">Rp
                            {{ number_format($estimatedInvoice['total'], 0, ',', '.') }}
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
            window.location.href = url.toString();
        }
    </script>
@endpush