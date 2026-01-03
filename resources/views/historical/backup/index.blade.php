@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h1 class="page-title">MEALS HISTORICAL</h1>
        <p class="page-subtitle">View meal attendance records</p>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Filters</h2>
        </div>

        <form action="{{ route('historical.index') }}" method="GET" class="filter-bar">
            <div class="form-group" style="flex: 1; min-width: 200px;">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Employee name or ID..."
                    value="{{ request('search') }}">
            </div>

            <div class="form-group">
                <label class="form-label">Department</label>
                <select name="department" class="form-control">
                    <option value="">All Departments</option>
                    <option value="GS" {{ request('department') == 'GS' ? 'selected' : '' }}>GS</option>
                    <option value="ICT" {{ request('department') == 'ICT' ? 'selected' : '' }}>ICT</option>
                    <option value="SCM" {{ request('department') == 'SCM' ? 'selected' : '' }}>SCM</option>
                    <option value="HSSE" {{ request('department') == 'HSSE' ? 'selected' : '' }}>HSSE</option>
                    <option value="PO" {{ request('department') == 'PO' ? 'selected' : '' }}>PO</option>
                    <option value="RAM" {{ request('department') == 'RAM' ? 'selected' : '' }}>RAM</option>
                    <option value="WS" {{ request('department') == 'WS' ? 'selected' : '' }}>WS</option>
                    <option value="FM" {{ request('department') == 'FM' ? 'selected' : '' }}>FM</option>
                    <option value="RELATION" {{ request('department') == 'RELATION' ? 'selected' : '' }}>RELATION</option>
                    <option value="PE" {{ request('department') == 'PE' ? 'selected' : '' }}>PE</option>
                    <option value="Plan & Eval" {{ request('department') == 'Plan & Eval' ? 'selected' : '' }}>Plan & Eval
                    </option>
                    <option value="LMF" {{ request('department') == 'LMF' ? 'selected' : '' }}>LMF</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Location</label>
                <select name="location" class="form-control">
                    <option value="">All Locations</option>
                    <option value="Ramba" {{ request('location') == 'Ramba' ? 'selected' : '' }}>Ramba</option>
                    <option value="Bentayan" {{ request('location') == 'Bentayan' ? 'selected' : '' }}>Bentayan</option>
                    <option value="Keluang" {{ request('location') == 'Keluang' ? 'selected' : '' }}>Keluang</option>
                    <option value="Mangunjaya" {{ request('location') == 'Mangunjaya' ? 'selected' : '' }}>Mangunjaya</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Meal Type</label>
                <select name="meal_type" class="form-control">
                    <option value="">All Meals</option>
                    <option value="breakfast" {{ request('meal_type') == 'breakfast' ? 'selected' : '' }}>Breakfast</option>
                    <option value="lunch" {{ request('meal_type') == 'lunch' ? 'selected' : '' }}>Lunch</option>
                    <option value="dinner" {{ request('meal_type') == 'dinner' ? 'selected' : '' }}>Dinner</option>
                    <option value="supper" {{ request('meal_type') == 'supper' ? 'selected' : '' }}>Supper</option>
                    <option value="snack" {{ request('meal_type') == 'snack' ? 'selected' : '' }}>Snack</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>

            <div class="form-group">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>

            <div class="d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search"></i>
                    Search
                </button>
                <a href="{{ route('historical.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-x-lg"></i>
                    Clear
                </a>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="card-title">Attendance Records
                ({{ $attendances->total() }})</h2>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>
                            <a href="{{ route('historical.index', array_merge(request()->all(), ['sort_by' => 'scanned_at', 'sort_dir' => ($sortBy == 'scanned_at' && $sortDir == 'desc') ? 'asc' : 'desc'])) }}"
                                class="sortable {{ $sortBy == 'scanned_at' ? 'active' : '' }}">
                                Date & Time
                                @if($sortBy == 'scanned_at')
                                    <i class="bi bi-chevron-{{ $sortDir == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Department</th>
                        <th>
                            <a href="{{ route('historical.index', array_merge(request()->all(), ['sort_by' => 'location', 'sort_dir' => ($sortBy == 'location' && $sortDir == 'asc') ? 'desc' : 'asc'])) }}"
                                class="sortable {{ $sortBy == 'location' ? 'active' : '' }}">
                                Location
                                @if($sortBy == 'location')
                                    <i class="bi bi-chevron-{{ $sortDir == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('historical.index', array_merge(request()->all(), ['sort_by' => 'meal_type', 'sort_dir' => ($sortBy == 'meal_type' && $sortDir == 'asc') ? 'desc' : 'asc'])) }}"
                                class="sortable {{ $sortBy == 'meal_type' ? 'active' : '' }}">
                                Meal Type
                                @if($sortBy == 'meal_type')
                                    <i class="bi bi-chevron-{{ $sortDir == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('historical.index', array_merge(request()->all(), ['sort_by' => 'scan_method', 'sort_dir' => ($sortBy == 'scan_method' && $sortDir == 'asc') ? 'desc' : 'asc'])) }}"
                                class="sortable {{ $sortBy == 'scan_method' ? 'active' : '' }}">
                                Method
                                @if($sortBy == 'scan_method')
                                    <i class="bi bi-chevron-{{ $sortDir == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>Recorded By</th>
                        <th>Proof</th>
                        @if(auth()->user()->canAccessFullFeatures())
                            <th>Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $attendance)
                        <tr>
                            <td>
                                <div style="font-size: 0.9rem;">
                                    {{ $attendance->scanned_at->format('d M Y') }}
                                </div>
                                <div style="color: var(--text-muted); font-size: 0.85rem;">
                                    {{ $attendance->scanned_at->format('H:i:s') }}
                                </div>
                            </td>
                            <td style="color: var(--accent);">
                                {{ $attendance->employee->employee_number ?? '-' }}
                            </td>
                            <td>{{ $attendance->employee->name ?? '-' }}</td>
                            <td>{{ $attendance->employee->department ?? '-' }}</td>
                            <td>{{ $attendance->location ?? $attendance->employee->location ?? '-' }}</td>
                            <td>
                                @php
                                    $mealIcons = [
                                        'breakfast' => 'bi-sunrise',
                                        'lunch' => 'bi-sun',
                                        'dinner' => 'bi-sunset',
                                        'supper' => 'bi-moon-stars',
                                        'snack' => 'bi-cup-straw'
                                    ];
                                    $mealColors = [
                                        'breakfast' => 'var(--accent)',
                                        'lunch' => 'var(--secondary)',
                                        'dinner' => 'var(--primary)',
                                        'supper' => 'var(--primary-light)',
                                        'snack' => '#9B59B6'
                                    ];
                                @endphp
                                <span style="display: inline-flex; align-items: center; gap: 0.5rem;">
                                    <i class="bi {{ $mealIcons[$attendance->meal_type] ?? 'bi-circle' }}"
                                        style="color: {{ $mealColors[$attendance->meal_type] ?? 'var(--text-secondary)' }};"></i>
                                    {{ ucfirst($attendance->meal_type) }}
                                </span>
                            </td>
                            <td>
                                <span
                                    class="badge {{ $attendance->scan_method == 'qr_scan' ? 'badge-primary' : 'badge-warning' }}">
                                    {{ $attendance->scan_method == 'qr_scan' ? 'QR Scan' : 'Manual' }}
                                </span>
                            </td>
                            <td style="color: var(--text-muted);">
                                {{ $attendance->recorded_by ?? '-' }}
                                @if($attendance->edited_by)
                                    <div style="font-size: 0.75rem; color: var(--accent); margin-top: 0.25rem;">
                                        <i class="bi bi-pencil"></i> Edited by {{ $attendance->edited_by }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($attendance->absence_proof)
                                    <a href="{{ Storage::url($attendance->absence_proof) }}" target="_blank"
                                        class="btn btn-primary btn-sm" title="View Absence Proof">
                                        <i class="bi bi-file-earmark-image"></i>
                                    </a>
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>
                            @if(auth()->user()->canAccessFullFeatures())
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <a href="{{ route('historical.edit', $attendance->id) }}" class="btn btn-secondary btn-sm"
                                            title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('historical.destroy', $attendance->id) }}" method="POST"
                                            style="display: inline;" onsubmit="return confirm('Delete this record?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 3rem; color: var(--text-muted);">
                                <i class="bi bi-inbox" style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
                                No attendance records found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Info -->
        <div
            style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; color: var(--text-muted); font-size: 0.9rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                Showing {{ $attendances->firstItem() ?? 0 }} to {{ $attendances->lastItem() ?? 0 }} of
                {{ $attendances->total() }} entries
            </div>
            <div>
                Page {{ $attendances->currentPage() }} of {{ $attendances->lastPage() }}
            </div>
        </div>

        @if($attendances->hasPages())
            <div class="pagination">
                @if($attendances->onFirstPage())
                    <span class="disabled">&laquo; Previous</span>
                @else
                    <a href="{{ $attendances->previousPageUrl() }}">&laquo; Previous</a>
                @endif

                @php
                    $start = max(1, $attendances->currentPage() - 2);
                    $end = min($attendances->lastPage(), $attendances->currentPage() + 2);
                @endphp

                @if($start > 1)
                    <a href="{{ $attendances->url(1) }}">1</a>
                    @if($start > 2)
                        <span style="color: var(--text-muted);">...</span>
                    @endif
                @endif

                @for($page = $start; $page <= $end; $page++)
                    @if($page == $attendances->currentPage())
                        <span class="active">{{ $page }}</span>
                    @else
                        <a href="{{ $attendances->url($page) }}">{{ $page }}</a>
                    @endif
                @endfor

                @if($end < $attendances->lastPage())
                    @if($end < $attendances->lastPage() - 1)
                        <span style="color: var(--text-muted);">...</span>
                    @endif
                    <a href="{{ $attendances->url($attendances->lastPage()) }}">{{ $attendances->lastPage() }}</a>
                @endif

                @if($attendances->hasMorePages())
                    <a href="{{ $attendances->nextPageUrl() }}">Next &raquo;</a>
                @else
                    <span class="disabled">Next &raquo;</span>
                @endif
            </div>
        @endif
    </div>

    <!-- Recap Export Modal -->
    <div id="recapModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); z-index: 9999; align-items: center; justify-content: center;">
        <div
            style="background: var(--card-bg); border: 2px solid var(--primary); border-radius: 12px; padding: 2rem; max-width: 500px; width: 90%;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="color: var(--primary); margin: 0;">Meal Recap Export</h3>
                <button type="button" onclick="closeRecapModal()"
                    style="background: transparent; border: none; color: var(--text-muted); font-size: 1.5rem; cursor: pointer;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <form id="recapForm" action="{{ route('historical.recap') }}" method="GET">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label">Company Header</label>
                    <input type="text" name="company_header" class="form-control" value="PT. Brylian Indah"
                        placeholder="Company name...">
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label">Location *</label>
                    <select name="location" class="form-control" required>
                        <option value="Ramba">Ramba</option>
                        <option value="Bentayan">Bentayan</option>
                        <option value="Keluang">Keluang</option>
                        <option value="Mangunjaya">Mangunjaya</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label">Start Date *</label>
                    <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label">End Date *</label>
                    <input type="date" name="end_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>

                <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                    <div style="flex: 1;">
                        <label class="form-label">Prepared By</label>
                        <input type="text" name="prepared_by" class="form-control" placeholder="Name...">
                    </div>
                    <div style="flex: 1;">
                        <label class="form-label">Position</label>
                        <input type="text" name="prepared_position" class="form-control" value="Camp Boss"
                            placeholder="Position...">
                    </div>
                </div>

                <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                    <div style="flex: 1;">
                        <label class="form-label">Checked By</label>
                        <input type="text" name="checked_by" class="form-control" value="Dedy B. / Rai A. / Marnita"
                            placeholder="Name...">
                    </div>
                    <div style="flex: 1;">
                        <label class="form-label">Position</label>
                        <input type="text" name="checked_position" class="form-control" value="GS Ramba"
                            placeholder="Position...">
                    </div>
                </div>

                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeRecapModal()">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-file-earmark-excel"></i> Export Excel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRecapModal() {
            document.getElementById('recapModal').style.display = 'flex';
        }

        function closeRecapModal() {
            document.getElementById('recapModal').style.display = 'none';
        }

        // Close modal on background click
        document.getElementById('recapModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeRecapModal();
            }
        });
    </script>
@endsection