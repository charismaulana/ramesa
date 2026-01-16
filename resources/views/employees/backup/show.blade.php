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
                        <span>{{ $employee->accommodation ?? '-' }}</span>
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
                <div class="card-header">
                    <h2 class="card-title">Recent Meals</h2>
                </div>

            @php
                $recentMeals = $employee->attendances()->latest('scanned_at')->take(5)->get();
            @endphp

            @if($recentMeals->count() > 0)
                <div style="display: grid; gap: 0.75rem;">
                    @foreach($recentMeals as $meal)
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: rgba(255, 255, 255, 0.03); border-radius: 8px;">
                            <div>
                                <span class="badge badge-primary">{{ ucfirst($meal->meal_type) }}</span>
                                <span style="color: var(--text-muted); font-size: 0.875rem; margin-left: 0.5rem;">
                                    {{ $meal->scan_method == 'qr_scan' ? 'QR Scan' : 'Manual' }}
                                </span>
                            </div>
                            <span style="color: var(--text-secondary); font-size: 0.875rem;">
                                {{ $meal->scanned_at->format('d M Y, H:i') }}
                            </span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-2">
                    <a href="{{ route('historical.index', ['employee_id' => $employee->id]) }}"
                        style="color: var(--primary-light);">
                        View all meals &rarr;
                    </a>
                </div>
            @else
                <p style="color: var(--text-muted); text-align: center; padding: 1rem;">
                    No meal records yet
                </p>
            @endif
        </div>
    </div>
    </div>
@endsection