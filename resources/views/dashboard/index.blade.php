@extends('layouts.app')

@section('content')
    <div class="page-header d-flex justify-between align-items-center">
        <div>
            <h1 class="page-title">DASHBOARD</h1>
            <p class="page-subtitle">Meal Attendance Monitoring</p>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-calendar-day"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $todayTotal }}</div>
                <div class="stat-label">Today</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-calendar-minus"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $yesterdayTotal }}</div>
                <div class="stat-label">Yesterday</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-calendar-week"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $thisWeekTotal }}</div>
                <div class="stat-label">This Week</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-people"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $activeEmployees }}</div>
                <div class="stat-label">Active Employees</div>
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