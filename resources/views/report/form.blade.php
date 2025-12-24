@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h1 class="page-title">PRINT PDF REPORT</h1>
        <p class="page-subtitle">Generate catering consumption recap report</p>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Report Options</h2>
        </div>

        <form action="{{ route('report.generate') }}" method="GET">
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Start Date *</label>
                        <input type="date" name="date_from" class="form-control" value="{{ date('Y-m-01') }}" required>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">End Date *</label>
                        <input type="date" name="date_to" class="form-control" value="{{ date('Y-m-t') }}" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Location</label>
                <select name="location" class="form-control">
                    <option value="">All Locations</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc }}">{{ $loc }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Employee Status</label>
                <div class="status-checkboxes">
                    @foreach($employeeStatuses as $status)
                        <label class="status-checkbox">
                            <input type="checkbox" name="employee_statuses[]" value="{{ $status }}" checked>
                            <span>{{ $status }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Report Type</label>
                <div class="report-type-options">
                    <label class="report-type-option">
                        <input type="radio" name="report_type" value="summary" checked>
                        <div class="report-type-card">
                            <div class="report-type-icon"><i class="bi bi-bar-chart"></i></div>
                            <div class="report-type-info">
                                <strong>Summary</strong>
                                <p>Total counts per location and employee status</p>
                            </div>
                        </div>
                    </label>
                    <label class="report-type-option">
                        <input type="radio" name="report_type" value="detailed">
                        <div class="report-type-card">
                            <div class="report-type-icon"><i class="bi bi-table"></i></div>
                            <div class="report-type-info">
                                <strong>Detailed (Daily)</strong>
                                <p>Daily breakdown by date for each status</p>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="d-flex gap-1 mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-file-earmark-pdf"></i> Generate PDF
                </button>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    <style>
        .status-checkboxes {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .status-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--card-border);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .status-checkbox:hover {
            border-color: var(--primary);
        }

        .status-checkbox input:checked+span {
            color: var(--accent);
        }

        .status-checkbox input {
            accent-color: var(--primary);
        }

        .report-type-options {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .report-type-option {
            flex: 1;
            min-width: 250px;
            cursor: pointer;
        }

        .report-type-option input {
            display: none;
        }

        .report-type-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.25rem;
            background: rgba(255, 255, 255, 0.03);
            border: 2px solid var(--card-border);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .report-type-option input:checked+.report-type-card {
            border-color: var(--primary);
            background: rgba(255, 69, 0, 0.1);
        }

        .report-type-card:hover {
            border-color: var(--primary-light);
        }

        .report-type-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .report-type-info strong {
            display: block;
            margin-bottom: 0.25rem;
        }

        .report-type-info p {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin: 0;
        }
    </style>
@endpush