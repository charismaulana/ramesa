@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h1 class="page-title">BULK EDIT ATTENDANCE</h1>
        <p class="page-subtitle">Edit all meals for {{ $employee->name }} on {{ $date->format('d/m/Y') }}</p>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-between align-items-center">
            <h2 class="card-title">Edit Meals</h2>
            <a href="{{ route('historical.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <form action="{{ route('historical.bulkEdit') }}" method="POST" style="padding: 2rem;">
            @csrf
            <input type="hidden" name="original_employee_id" value="{{ $employee->id }}">
            <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">

            <!-- Current Info -->
            <div
                style="margin-bottom: 2rem; padding: 1rem; background: rgba(255, 165, 0, 0.1); border-radius: 8px; border: 1px solid var(--card-border);">
                <div style="display: flex; gap: 2rem; flex-wrap: wrap; align-items: center;">
                    <div>
                        <span style="color: var(--text-muted); font-size: 0.85rem;">Current Employee</span>
                        <div style="font-weight: 600; color: var(--primary);">{{ $employee->employee_number }} -
                            {{ $employee->name }}</div>
                    </div>
                    <div>
                        <span style="color: var(--text-muted); font-size: 0.85rem;">Date</span>
                        <div style="font-weight: 600;">{{ $date->format('d M Y') }}</div>
                    </div>
                    <div>
                        <span style="color: var(--text-muted); font-size: 0.85rem;">Current Location</span>
                        <div style="font-weight: 600;">{{ $attendances->first()->location ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <span style="color: var(--text-muted); font-size: 0.85rem;">Total Meals</span>
                        <div style="font-weight: 600; color: var(--success);">{{ $attendances->count() }} meals</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Change Employee -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="new_employee_id">Change Employee To *</label>
                        <select name="new_employee_id" id="new_employee_id" class="form-control" required>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ $employee->id == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->employee_number }} - {{ $emp->name }}
                                </option>
                            @endforeach
                        </select>
                        <small style="color: var(--text-muted);">Select same employee to keep unchanged</small>
                    </div>
                </div>

                <!-- Location -->
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="location">Location *</label>
                        <select name="location" id="location" class="form-control" required>
                            <option value="Ramba" {{ ($attendances->first()->location ?? '') == 'Ramba' ? 'selected' : '' }}>
                                Ramba</option>
                            <option value="Bentayan" {{ ($attendances->first()->location ?? '') == 'Bentayan' ? 'selected' : '' }}>Bentayan</option>
                            <option value="Mangunjaya" {{ ($attendances->first()->location ?? '') == 'Mangunjaya' ? 'selected' : '' }}>Mangunjaya</option>
                            <option value="Keluang" {{ ($attendances->first()->location ?? '') == 'Keluang' ? 'selected' : '' }}>Keluang</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Meal Checkboxes -->
            <div class="form-group">
                <label class="form-label">Select Meals to Keep</label>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1rem;">
                    <i class="bi bi-exclamation-triangle" style="color: var(--warning);"></i>
                    Unchecked meals will be <strong>deleted</strong>. Only checked meals will be kept/updated.
                </p>

                <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                    @php
                        $existingMeals = $attendances->pluck('meal_type')->toArray();
                        $allMeals = ['breakfast', 'lunch', 'dinner', 'supper', 'snack'];
                        $mealIcons = [
                            'breakfast' => '🌅',
                            'lunch' => '☀️',
                            'dinner' => '🌙',
                            'supper' => '🌃',
                            'snack' => '🍿',
                        ];
                    @endphp

                    @foreach($allMeals as $meal)
                        @php
                            $hasRecord = in_array($meal, $existingMeals);
                            $attendance = $attendances->where('meal_type', $meal)->first();
                        @endphp
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; 
                                    background: {{ $hasRecord ? 'rgba(0, 255, 136, 0.1)' : 'rgba(100, 100, 100, 0.1)' }};
                                    border: 1px solid {{ $hasRecord ? 'var(--success)' : 'var(--card-border)' }};
                                    border-radius: 8px; cursor: {{ $hasRecord ? 'pointer' : 'not-allowed' }};
                                    opacity: {{ $hasRecord ? '1' : '0.5' }};">
                            @if($hasRecord)
                                <input type="checkbox" name="meals[]" value="{{ $meal }}" checked
                                    style="accent-color: var(--primary); width: 18px; height: 18px;">
                            @else
                                <input type="checkbox" disabled style="width: 18px; height: 18px;">
                            @endif
                            <span style="font-size: 1.2rem;">{{ $mealIcons[$meal] }}</span>
                            <span style="font-weight: 500;">{{ ucfirst($meal) }}</span>
                            @if($hasRecord && $attendance)
                                <span style="color: var(--text-muted); font-size: 0.8rem; margin-left: 0.5rem;">
                                    ({{ $attendance->scanned_at->format('H:i') }})
                                </span>
                            @else
                                <span style="color: var(--text-muted); font-size: 0.8rem; margin-left: 0.5rem;">
                                    (no record)
                                </span>
                            @endif
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Existing Proof Info -->
            @php
                $proofPath = $attendances->first()->absence_proof ?? null;
            @endphp
            @if($proofPath)
                <div
                    style="margin-top: 1.5rem; padding: 1rem; background: rgba(0, 255, 136, 0.1); border-radius: 8px; border: 1px solid rgba(0, 255, 136, 0.3);">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <i class="bi bi-file-earmark-check" style="font-size: 1.5rem; color: var(--success);"></i>
                        <div>
                            <div style="font-weight: 500;">Absence Proof Attached</div>
                            <div style="color: var(--text-muted); font-size: 0.85rem;">{{ basename($proofPath) }}</div>
                        </div>
                        <a href="{{ Storage::disk('public_direct')->url($proofPath) }}" target="_blank"
                            class="btn btn-sm btn-primary">
                            <i class="bi bi-eye"></i> View
                        </a>
                    </div>
                </div>
            @endif

            <div class="d-flex gap-1" style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i>
                    Save Changes
                </button>
                <a href="{{ route('historical.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-lg"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection