@extends('layouts.app')

@section('content')
    <div class="page-header d-flex justify-between align-items-center">
        <div>
            <h1 class="page-title">EMPLOYEE LIST</h1>
            <p class="page-subtitle">Manage all employee records</p>
        </div>
        <div class="d-flex gap-1">
            <a href="{{ route('employees.exportPobForm') }}" class="btn btn-secondary">
                <i class="bi bi-file-earmark-excel"></i>
                Export POB
            </a>
            <a href="{{ route('employees.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus"></i>
                Add Employee
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Filters</h2>
        </div>

        <form action="{{ route('employees.index') }}" method="GET" class="filter-bar">
            <div class="form-group" style="flex: 1; min-width: 200px;">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Name, ID, department..."
                    value="{{ request('search') }}">
            </div>

            <div class="form-group">
                <label class="form-label">Employee Status</label>
                <select name="employee_status" class="form-control">
                    <option value="">All Status</option>
                    <option value="Pekerja" {{ request('employee_status') == 'Pekerja' ? 'selected' : '' }}>Pekerja</option>
                    <option value="TA" {{ request('employee_status') == 'TA' ? 'selected' : '' }}>TA</option>
                    <option value="TKJP" {{ request('employee_status') == 'TKJP' ? 'selected' : '' }}>TKJP</option>
                    <option value="Contractor" {{ request('employee_status') == 'Contractor' ? 'selected' : '' }}>Contractor
                    </option>
                    <option value="Visitor" {{ request('employee_status') == 'Visitor' ? 'selected' : '' }}>Visitor</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Department</label>
                <select name="department" class="form-control">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Homebase</label>
                <select name="location" class="form-control">
                    <option value="">All Homebases</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc }}" {{ request('location') == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                    @endforeach
                </select>
            </div>

            <div class="d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search"></i>
                    Search
                </button>
                <a href="{{ route('employees.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-x-lg"></i>
                    Clear
                </a>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Employees ({{ $employees->total() }})</h2>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>
                            <a href="{{ route('employees.index', array_merge(request()->all(), ['sort_by' => 'employee_number', 'sort_dir' => ($sortBy == 'employee_number' && $sortDir == 'asc') ? 'desc' : 'asc'])) }}"
                                class="sortable {{ $sortBy == 'employee_number' ? 'active' : '' }}">
                                Employee ID
                                @if($sortBy == 'employee_number')
                                    <i class="bi bi-chevron-{{ $sortDir == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('employees.index', array_merge(request()->all(), ['sort_by' => 'name', 'sort_dir' => ($sortBy == 'name' && $sortDir == 'asc') ? 'desc' : 'asc'])) }}"
                                class="sortable {{ $sortBy == 'name' ? 'active' : '' }}">
                                Name
                                @if($sortBy == 'name')
                                    <i class="bi bi-chevron-{{ $sortDir == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('employees.index', array_merge(request()->all(), ['sort_by' => 'group', 'sort_dir' => ($sortBy == 'group' && $sortDir == 'asc') ? 'desc' : 'asc'])) }}"
                                class="sortable {{ $sortBy == 'group' ? 'active' : '' }}">
                                Group
                                @if($sortBy == 'group')
                                    <i class="bi bi-chevron-{{ $sortDir == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('employees.index', array_merge(request()->all(), ['sort_by' => 'department', 'sort_dir' => ($sortBy == 'department' && $sortDir == 'asc') ? 'desc' : 'asc'])) }}"
                                class="sortable {{ $sortBy == 'department' ? 'active' : '' }}">
                                Department
                                @if($sortBy == 'department')
                                    <i class="bi bi-chevron-{{ $sortDir == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('employees.index', array_merge(request()->all(), ['sort_by' => 'location', 'sort_dir' => ($sortBy == 'location' && $sortDir == 'asc') ? 'desc' : 'asc'])) }}"
                                class="sortable {{ $sortBy == 'location' ? 'active' : '' }}">
                                Homebase
                                @if($sortBy == 'location')
                                    <i class="bi bi-chevron-{{ $sortDir == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('employees.index', array_merge(request()->all(), ['sort_by' => 'accommodation', 'sort_dir' => ($sortBy == 'accommodation' && $sortDir == 'asc') ? 'desc' : 'asc'])) }}"
                                class="sortable {{ $sortBy == 'accommodation' ? 'active' : '' }}">
                                Accommodation
                                @if($sortBy == 'accommodation')
                                    <i class="bi bi-chevron-{{ $sortDir == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('employees.index', array_merge(request()->all(), ['sort_by' => 'employee_status', 'sort_dir' => ($sortBy == 'employee_status' && $sortDir == 'asc') ? 'desc' : 'asc'])) }}"
                                class="sortable {{ $sortBy == 'employee_status' ? 'active' : '' }}">
                                Emp Status
                                @if($sortBy == 'employee_status')
                                    <i class="bi bi-chevron-{{ $sortDir == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('employees.index', array_merge(request()->all(), ['sort_by' => 'active_status', 'sort_dir' => ($sortBy == 'active_status' && $sortDir == 'asc') ? 'desc' : 'asc'])) }}"
                                class="sortable {{ $sortBy == 'active_status' ? 'active' : '' }}">
                                Active
                                @if($sortBy == 'active_status')
                                    <i class="bi bi-chevron-{{ $sortDir == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr>
                            <td>{{ $employee->employee_number }}</td>
                            <td>{{ $employee->name }}</td>
                            <td>
                                @if($employee->groups->count() > 0)
                                    {{ $employee->groups->pluck('name')->join(', ') }}
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>
                            <td>{{ $employee->department ?? '-' }}</td>
                            <td>{{ $employee->location ?? '-' }}</td>
                            <td>
                                @if($employee->accommodation && count($employee->accommodation) > 0)
                                    @php
                                        $rooms = [];
                                        foreach ($employee->accommodation as $loc => $room) {
                                            if (!empty($room)) {
                                                $rooms[] = strtoupper(substr($loc, 0, 1)) . ': ' . $room;
                                            }
                                        }
                                    @endphp
                                    {{ implode(', ', $rooms) ?: '-' }}
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>
                            <td>{{ $employee->employee_status ?? '-' }}</td>
                            <td>
                                <span
                                    class="badge {{ $employee->active_status == 'active' ? 'badge-success' : 'badge-danger' }}">
                                    {{ ucfirst($employee->active_status) }}
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('employees.show', $employee) }}" class="btn btn-secondary btn-sm"
                                        title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-secondary btn-sm"
                                        title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="{{ route('employees.printCard', $employee) }}" class="btn btn-secondary btn-sm"
                                        title="Print Card">
                                        <i class="bi bi-credit-card"></i>
                                    </a>
                                    <button type="button" class="btn btn-secondary btn-sm" title="Transfer Records"
                                        onclick="openMergeModal({{ $employee->id }}, '{{ addslashes($employee->employee_number) }}', '{{ addslashes($employee->name) }}')">
                                        <i class="bi bi-arrow-right-circle"></i>
                                    </button>
                                    <form action="{{ route('employees.destroy', $employee) }}" method="POST"
                                        style="display: inline;" onsubmit="return confirm('Delete this employee?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center" style="padding: 3rem; color: var(--text-muted);">
                                <i class="bi bi-inbox" style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
                                No employees found
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
                Showing {{ $employees->firstItem() ?? 0 }} to {{ $employees->lastItem() ?? 0 }} of {{ $employees->total() }}
                entries
            </div>
            <div>
                Page {{ $employees->currentPage() }} of {{ $employees->lastPage() }}
            </div>
        </div>

        @if($employees->hasPages())
            <div class="pagination">
                @if($employees->onFirstPage())
                    <span class="disabled">&laquo; Previous</span>
                @else
                    <a href="{{ $employees->previousPageUrl() }}">&laquo; Previous</a>
                @endif

                @php
                    $start = max(1, $employees->currentPage() - 2);
                    $end = min($employees->lastPage(), $employees->currentPage() + 2);
                @endphp

                @if($start > 1)
                    <a href="{{ $employees->url(1) }}">1</a>
                    @if($start > 2)
                        <span style="color: var(--text-muted);">...</span>
                    @endif
                @endif

                @for($page = $start; $page <= $end; $page++)
                    @if($page == $employees->currentPage())
                        <span class="active">{{ $page }}</span>
                    @else
                        <a href="{{ $employees->url($page) }}">{{ $page }}</a>
                    @endif
                @endfor

                @if($end < $employees->lastPage())
                    @if($end < $employees->lastPage() - 1)
                        <span style="color: var(--text-muted);">...</span>
                    @endif
                    <a href="{{ $employees->url($employees->lastPage()) }}">{{ $employees->lastPage() }}</a>
                @endif

                @if($employees->hasMorePages())
                    <a href="{{ $employees->nextPageUrl() }}">Next &raquo;</a>
                @else
                    <span class="disabled">Next &raquo;</span>
                @endif
            </div>
        @endif
    </div>

    <!-- Attendance Stats Card -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="card-title">📊 Attendance Stats ({{ $currentMonthStart->format('F Y') }})</h2>
            <form method="GET" action="{{ route('employees.index') }}" style="margin: 0;">
                <select name="stats_month" class="form-control" style="width: auto; display: inline-block;"
                    onchange="this.form.submit()">
                    @for($i = 0; $i < 12; $i++)
                        @php
                            $month = now()->subMonths($i);
                            $monthValue = $month->format('Y-m');
                            $monthLabel = $month->format('F Y');
                            $selected = request('stats_month', now()->format('Y-m')) == $monthValue;
                        @endphp
                        <option value="{{ $monthValue }}" {{ $selected ? 'selected' : '' }}>{{ $monthLabel }}</option>
                    @endfor
                </select>
            </form>
        </div>
        <div class="row" style="padding: 1rem; gap: 1rem;">
            <!-- High Attendance (>20 days) -->
            <div class="col-6" style="flex: 1; min-width: 300px;">
                <div
                    style="background: rgba(40, 167, 69, 0.1); border: 1px solid #28a745; border-radius: 8px; padding: 1rem;">
                    <h4 style="margin: 0 0 0.75rem 0; color: #28a745; font-size: 0.95rem;">
                        ✅ High Attendance (>20 days)
                        <span class="badge badge-success">{{ $highAttendanceEmployees->count() }}</span>
                    </h4>
                    @if($highAttendanceEmployees->count() > 0)
                        <div style="max-height: 200px; overflow-y: auto;">
                            <table style="width: 100%; font-size: 0.85rem;">
                                <thead>
                                    <tr style="background: rgba(40, 167, 69, 0.1);">
                                        <th style="padding: 0.35rem;">Name</th>
                                        <th style="padding: 0.35rem;">Department</th>
                                        <th style="padding: 0.35rem;">Position</th>
                                        <th style="padding: 0.35rem;">Status</th>
                                        <th style="padding: 0.35rem; text-align: center;">Days</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($highAttendanceEmployees as $emp)
                                        <tr>
                                            <td style="padding: 0.35rem;">
                                                <a href="{{ route('employees.show', $emp) }}"
                                                    style="color: var(--text);">{{ $emp->name }}</a>
                                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                                    {{ $emp->employee_number }}
                                                </div>
                                            </td>
                                            <td style="padding: 0.35rem; color: var(--text-muted); font-size: 0.8rem;">
                                                {{ $emp->department ?? '-' }}
                                            </td>
                                            <td style="padding: 0.35rem; color: var(--text-muted); font-size: 0.8rem;">
                                                {{ $emp->position ?? '-' }}
                                            </td>
                                            <td style="padding: 0.35rem; font-size: 0.8rem;">{{ $emp->employee_status ?? '-' }}</td>
                                            <td style="padding: 0.35rem; text-align: center;">
                                                <strong style="color: #28a745;">{{ $emp->attendance_days }}</strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p style="color: var(--text-muted); margin: 0; font-size: 0.85rem;">No employees with >20 attendance
                            days</p>
                    @endif
                </div>
            </div>

            <!-- No Attendance -->
            <div class="col-6" style="flex: 1; min-width: 300px;">
                <div
                    style="background: rgba(220, 53, 69, 0.1); border: 1px solid #dc3545; border-radius: 8px; padding: 1rem;">
                    <h4 style="margin: 0 0 0.75rem 0; color: #dc3545; font-size: 0.95rem;">
                        ❌ No Attendance This Month
                        <span class="badge badge-danger">{{ $noAttendanceEmployees->count() }}</span>
                    </h4>
                    @if($noAttendanceEmployees->count() > 0)
                        <div style="max-height: 200px; overflow-y: auto;">
                            <table style="width: 100%; font-size: 0.85rem;">
                                <thead>
                                    <tr style="background: rgba(220, 53, 69, 0.1);">
                                        <th style="padding: 0.35rem;">Name</th>
                                        <th style="padding: 0.35rem;">Department</th>
                                        <th style="padding: 0.35rem;">Position</th>
                                        <th style="padding: 0.35rem;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($noAttendanceEmployees as $emp)
                                        <tr>
                                            <td style="padding: 0.35rem;">
                                                <a href="{{ route('employees.show', $emp) }}"
                                                    style="color: var(--text);">{{ $emp->name }}</a>
                                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                                    {{ $emp->employee_number }}
                                                </div>
                                            </td>
                                            <td style="padding: 0.35rem; color: var(--text-muted); font-size: 0.8rem;">
                                                {{ $emp->department ?? '-' }}
                                            </td>
                                            <td style="padding: 0.35rem; color: var(--text-muted); font-size: 0.8rem;">
                                                {{ $emp->position ?? '-' }}
                                            </td>
                                            <td style="padding: 0.35rem; font-size: 0.8rem;">{{ $emp->employee_status ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p style="color: var(--text-muted); margin: 0; font-size: 0.85rem;">All active employees have attendance
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>



    <!-- Merge Records Modal -->
    <div id="mergeModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3 class="modal-title"><i class="bi bi-arrow-right-circle"></i> Transfer Meal Records</h3>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeMergeModal()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form action="{{ route('employees.mergeRecords') }}" method="POST" id="mergeForm">
                @csrf
                <input type="hidden" name="source_id" id="merge_source_id">

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label">Transfer FROM:</label>
                    <div id="source_info"
                        style="padding: 0.75rem; background: rgba(255, 100, 100, 0.1); border-radius: 8px; border: 1px solid rgba(255, 100, 100, 0.3);">
                        <strong id="source_name">-</strong>
                        <span style="color: var(--text-muted); font-size: 0.85rem;" id="source_number"></span>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label">Transfer TO: *</label>
                    <input type="text" id="target_search" class="form-control" placeholder="Search employee name or ID..."
                        oninput="searchTargetEmployees()" autocomplete="off">
                    <div id="target_results"
                        style="max-height: 200px; overflow-y: auto; margin-top: 0.5rem; display: none;">
                        <!-- Results will be populated here -->
                    </div>
                    <input type="hidden" name="target_id" id="merge_target_id" required>
                    <div id="target_info"
                        style="display: none; margin-top: 0.5rem; padding: 0.75rem; background: rgba(100, 255, 100, 0.1); border-radius: 8px; border: 1px solid rgba(100, 255, 100, 0.3);">
                        <strong id="target_name">-</strong>
                        <span style="color: var(--text-muted); font-size: 0.85rem;" id="target_number"></span>
                        <button type="button" class="btn btn-secondary btn-sm" style="float: right;"
                            onclick="clearTargetSelection()">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group"
                    style="margin-bottom: 1rem; padding: 0.75rem; background: rgba(255, 165, 0, 0.1); border-radius: 8px;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--accent);">
                        <input type="checkbox" name="delete_source" value="1" style="accent-color: var(--primary);">
                        Delete source employee after transfer
                    </label>
                </div>

                <div class="alert"
                    style="background: rgba(255, 69, 0, 0.1); border: 1px solid rgba(255, 69, 0, 0.3); padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem;">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Warning:</strong> This will transfer ALL meal records from the source employee to the target.
                    This action cannot be undone!
                </div>

                <input type="hidden" name="duplicate_action" id="duplicate_action" value="skip">

                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-primary" id="mergeSubmitBtn" disabled onclick="checkAndTransfer()">
                        <i class="bi bi-arrow-right-circle"></i> Transfer Records
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeMergeModal()">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Duplicates Modal -->
    <div id="duplicatesModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 700px; max-height: 80vh; overflow-y: auto;">
            <div class="modal-header">
                <h3 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Duplicate Records Found</h3>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeDuplicatesModal()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div style="padding: 1rem;">
                <div class="alert"
                    style="background: rgba(255, 165, 0, 0.1); border: 1px solid rgba(255, 165, 0, 0.3); padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem;">
                    <strong><span id="duplicateCount">0</span> duplicate meals</strong> were found. These records exist for
                    the same date and meal type in both employees.
                </div>

                <div
                    style="max-height: 300px; overflow-y: auto; border: 1px solid var(--card-border); border-radius: 8px; margin-bottom: 1rem;">
                    <table class="data-table" style="margin: 0;">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Meal</th>
                                <th>Source Location</th>
                                <th>Target Location</th>
                            </tr>
                        </thead>
                        <tbody id="duplicatesList">
                        </tbody>
                    </table>
                </div>

                <div class="d-flex gap-1" style="flex-wrap: wrap;">
                    <button type="button" class="btn btn-secondary" onclick="transferWithAction('skip')" style="flex: 1;">
                        <i class="bi bi-skip-forward"></i> Skip Duplicates
                    </button>
                    <button type="button" class="btn btn-warning" onclick="transferWithAction('overwrite')"
                        style="flex: 1;">
                        <i class="bi bi-arrow-repeat"></i> Overwrite with Source
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeDuplicatesModal()" style="flex: 1;">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 1);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: var(--card-bg, #1a1a1a);
            border: 1px solid var(--card-border, #333);
            border-radius: 12px;
            padding: 1.5rem;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--card-border, #333);
        }

        .modal-title {
            margin: 0;
            font-size: 1.25rem;
        }
    </style>
@endpush

@push('scripts')
    <script>
        let searchTimeout;

        function openMergeModal(sourceId, sourceNumber, sourceName) {
            document.getElementById('merge_source_id').value = sourceId;
            document.getElementById('source_name').textContent = sourceName;
            document.getElementById('source_number').textContent = ' (#' + sourceNumber + ')';
            document.getElementById('target_search').value = '';
            document.getElementById('merge_target_id').value = '';
            document.getElementById('target_info').style.display = 'none';
            document.getElementById('target_results').style.display = 'none';
            document.getElementById('mergeSubmitBtn').disabled = true;
            document.getElementById('mergeModal').style.display = 'flex';
        }

        function closeMergeModal() {
            document.getElementById('mergeModal').style.display = 'none';
        }

        function searchTargetEmployees() {
            clearTimeout(searchTimeout);
            const search = document.getElementById('target_search').value;
            const sourceId = document.getElementById('merge_source_id').value;

            if (search.length < 2) {
                document.getElementById('target_results').style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`{{ route('employees.mergeOptions') }}?search=${encodeURIComponent(search)}&exclude_id=${sourceId}`)
                    .then(response => response.json())
                    .then(employees => {
                        const resultsDiv = document.getElementById('target_results');
                        if (employees.length === 0) {
                            resultsDiv.innerHTML = '<div style="padding: 0.75rem; color: var(--text-muted);">No employees found</div>';
                        } else {
                            resultsDiv.innerHTML = employees.map(emp => `
                                                                                    <div class="search-result-item" onclick="selectTargetEmployee(${emp.id}, '${emp.employee_number}', '${emp.name.replace(/'/g, "\\'")}', '${emp.department || ''}')"
                                                                                        style="padding: 0.75rem; cursor: pointer; border-bottom: 1px solid var(--card-border); transition: background 0.2s;"
                                                                                        onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background=''">
                                                                                        <strong>${emp.name}</strong>
                                                                                        <span style="color: var(--text-muted); font-size: 0.85rem;">#${emp.employee_number}</span>
                                                                                        <span style="color: var(--accent); font-size: 0.75rem; margin-left: 0.5rem;">${emp.employee_status || ''}</span>
                                                                                    </div>
                                                                                `).join('');
                        }
                        resultsDiv.style.display = 'block';
                    });
            }, 300);
        }

        function selectTargetEmployee(id, number, name, department) {
            document.getElementById('merge_target_id').value = id;
            document.getElementById('target_name').textContent = name;
            document.getElementById('target_number').textContent = ' (#' + number + ') ' + department;
            document.getElementById('target_info').style.display = 'block';
            document.getElementById('target_results').style.display = 'none';
            document.getElementById('target_search').style.display = 'none';
            document.getElementById('mergeSubmitBtn').disabled = false;
        }

        function clearTargetSelection() {
            document.getElementById('merge_target_id').value = '';
            document.getElementById('target_info').style.display = 'none';
            document.getElementById('target_search').style.display = 'block';
            document.getElementById('target_search').value = '';
            document.getElementById('mergeSubmitBtn').disabled = true;
        }

        // Check for duplicates before transferring
        async function checkAndTransfer() {
            const sourceId = document.getElementById('merge_source_id').value;
            const targetId = document.getElementById('merge_target_id').value;

            if (!sourceId || !targetId) {
                alert('Please select both source and target employees');
                return;
            }

            // Show loading
            document.getElementById('mergeSubmitBtn').disabled = true;
            document.getElementById('mergeSubmitBtn').innerHTML = '<i class="bi bi-hourglass-split"></i> Checking...';

            try {
                const response = await fetch('{{ route("employees.checkMergeDuplicates") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ source_id: sourceId, target_id: targetId })
                });

                const data = await response.json();

                if (data.duplicate_count > 0) {
                    // Show duplicates modal
                    document.getElementById('duplicateCount').textContent = data.duplicate_count;

                    const listHtml = data.duplicates.map(d => `
                                            <tr>
                                                <td>${d.date}</td>
                                                <td>${d.meal_type}</td>
                                                <td>${d.source_location || '-'}</td>
                                                <td>${d.target_location || '-'}</td>
                                            </tr>
                                        `).join('');
                    document.getElementById('duplicatesList').innerHTML = listHtml;

                    document.getElementById('duplicatesModal').style.display = 'flex';
                } else {
                    // No duplicates, submit directly
                    document.getElementById('mergeForm').submit();
                }
            } catch (error) {
                console.error('Error checking duplicates:', error);
                alert('Error checking for duplicates. Please try again.');
            } finally {
                document.getElementById('mergeSubmitBtn').disabled = false;
                document.getElementById('mergeSubmitBtn').innerHTML = '<i class="bi bi-arrow-right-circle"></i> Transfer Records';
            }
        }

        function closeDuplicatesModal() {
            document.getElementById('duplicatesModal').style.display = 'none';
        }

        function transferWithAction(action) {
            document.getElementById('duplicate_action').value = action;
            closeDuplicatesModal();
            document.getElementById('mergeForm').submit();
        }

        // Close modal on outside click
        document.getElementById('mergeModal').addEventListener('click', function (e) {
            if (e.target === this) closeMergeModal();
        });

        document.getElementById('duplicatesModal').addEventListener('click', function (e) {
            if (e.target === this) closeDuplicatesModal();
        });
    </script>
@endpush