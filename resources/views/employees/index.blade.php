@extends('layouts.app')

@section('content')
    <div class="page-header d-flex justify-between align-items-center">
        <div>
            <h1 class="page-title">EMPLOYEE LIST</h1>
            <p class="page-subtitle">Manage all employee records</p>
        </div>
        <a href="{{ route('employees.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus"></i>
            Add Employee
        </a>
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
                            <a href="{{ route('employees.index', array_merge(request()->all(), ['sort_by' => 'company', 'sort_dir' => ($sortBy == 'company' && $sortDir == 'asc') ? 'desc' : 'asc'])) }}"
                                class="sortable {{ $sortBy == 'company' ? 'active' : '' }}">
                                Company
                                @if($sortBy == 'company')
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
                            <td>{{ $employee->company ?? '-' }}</td>
                            <td>{{ $employee->department ?? '-' }}</td>
                            <td>{{ $employee->location ?? '-' }}</td>
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

                @foreach($employees->getUrlRange(1, $employees->lastPage()) as $page => $url)
                    @if($page == $employees->currentPage())
                        <span class="active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if($employees->hasMorePages())
                    <a href="{{ $employees->nextPageUrl() }}">Next &raquo;</a>
                @else
                    <span class="disabled">Next &raquo;</span>
                @endif
            </div>
        @endif
    </div>
@endsection