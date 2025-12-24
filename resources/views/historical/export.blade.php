@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h1 class="page-title">EXPORT DATA</h1>
        <p class="page-subtitle">Configure your export options</p>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Export Options</h2>
        </div>

        <form action="{{ route('historical.export') }}" method="GET">
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Start Date *</label>
                        <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-01') }}" required>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">End Date *</label>
                        <input type="date" name="end_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <select name="location" class="form-control">
                            <option value="">All Locations</option>
                            @foreach($locations as $location)
                                <option value="{{ $location }}">{{ $location }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Meal Type</label>
                        <select name="meal_type" class="form-control">
                            <option value="">All Meal Types</option>
                            @foreach($mealTypes as $meal)
                                <option value="{{ $meal }}">{{ ucfirst($meal) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Export Type</label>
                <div class="export-type-options">
                    <label class="export-type-option">
                        <input type="radio" name="export_type" value="detailed" checked>
                        <div class="export-type-card">
                            <div class="export-type-icon"><i class="bi bi-list-ul"></i></div>
                            <div class="export-type-info">
                                <strong>Detailed</strong>
                                <p>All individual attendance records with employee info</p>
                            </div>
                        </div>
                    </label>
                    <label class="export-type-option">
                        <input type="radio" name="export_type" value="summary">
                        <div class="export-type-card">
                            <div class="export-type-icon"><i class="bi bi-bar-chart"></i></div>
                            <div class="export-type-info">
                                <strong>Summary</strong>
                                <p>Meal counts grouped by date and location</p>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="d-flex gap-1 mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-download"></i> Export Excel
                </button>
                <a href="{{ route('historical.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Historical
                </a>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    <style>
        .export-type-options {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .export-type-option {
            flex: 1;
            min-width: 250px;
            cursor: pointer;
        }

        .export-type-option input {
            display: none;
        }

        .export-type-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.25rem;
            background: rgba(255, 255, 255, 0.03);
            border: 2px solid var(--card-border);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .export-type-option input:checked+.export-type-card {
            border-color: var(--primary);
            background: rgba(255, 69, 0, 0.1);
        }

        .export-type-card:hover {
            border-color: var(--primary-light);
        }

        .export-type-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .export-type-info strong {
            display: block;
            margin-bottom: 0.25rem;
        }

        .export-type-info p {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin: 0;
        }
    </style>
@endpush