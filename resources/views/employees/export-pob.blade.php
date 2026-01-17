@extends('layouts.app')

@section('content')
    <div class="page-header d-flex justify-between align-items-center">
        <div>
            <h1 class="page-title">EXPORT POB SCHEDULE</h1>
            <p class="page-subtitle">Export employee attendance schedule by month</p>
        </div>
        <a href="{{ route('employees.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Employees
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">📊 Export Options</h2>
        </div>
        <form action="{{ route('employees.exportPob') }}" method="POST" style="padding: 1.5rem;">
            @csrf
            <div class="row" style="gap: 1rem;">
                <div class="col-3">
                    <div class="form-group">
                        <label class="form-label">Month *</label>
                        <input type="month" name="month" class="form-control" value="{{ date('Y-m') }}" required>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label class="form-label">Location (Optional)</label>
                        <select name="location" class="form-control">
                            <option value="">All Locations</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc }}">{{ $loc }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-2" style="display: flex; align-items: flex-end;">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-file-earmark-excel"></i> Download Excel
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">ℹ️ Export Information</h2>
        </div>
        <div style="padding: 1.5rem;">
            <p>The POB Schedule export will generate an Excel file containing:</p>
            <ul style="margin-left: 1.5rem; margin-top: 0.5rem;">
                <li><strong>Employee Information:</strong> ID, Name, Department, Location, Status</li>
                <li><strong>Daily Attendance:</strong> Green cells indicate days with meal attendance</li>
                <li><strong>Grand Total:</strong> Total POB count per day at the bottom</li>
            </ul>
            <p style="margin-top: 1rem; color: var(--text-muted);">
                <i class="bi bi-info-circle"></i>
                Select a specific location to filter employees and attendance by that location,
                or leave empty to include all locations.
            </p>
        </div>
    </div>
@endsection