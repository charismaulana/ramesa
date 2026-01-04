@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h1 class="page-title">EDIT ATTENDANCE RECORD</h1>
        <p class="page-subtitle">Update meal attendance information</p>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Edit Record</h2>
        </div>

        <form action="{{ route('historical.update', $attendance->id) }}" method="POST" style="padding: 2rem;"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="employee_id">Employee *</label>
                        <select name="employee_id" id="employee_id" class="form-control" required>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ old('employee_id', $attendance->employee_id) == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->employee_number }} - {{ $emp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="meal_type">Meal Type *</label>
                        <select name="meal_type" id="meal_type" class="form-control" required>
                            <option value="breakfast" {{ old('meal_type', $attendance->meal_type) == 'breakfast' ? 'selected' : '' }}>Breakfast</option>
                            <option value="lunch" {{ old('meal_type', $attendance->meal_type) == 'lunch' ? 'selected' : '' }}>
                                Lunch</option>
                            <option value="dinner" {{ old('meal_type', $attendance->meal_type) == 'dinner' ? 'selected' : '' }}>Dinner</option>
                            <option value="supper" {{ old('meal_type', $attendance->meal_type) == 'supper' ? 'selected' : '' }}>Supper</option>
                            <option value="snack" {{ old('meal_type', $attendance->meal_type) == 'snack' ? 'selected' : '' }}>
                                Snack</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="scanned_at">Date & Time *</label>
                        <input type="datetime-local" name="scanned_at" id="scanned_at" class="form-control"
                            value="{{ old('scanned_at', $attendance->scanned_at->format('Y-m-d\TH:i')) }}" required>
                    </div>
                </div>

                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="location">Meal Location *</label>
                        <select name="location" id="location" class="form-control" required>
                            <option value="Ramba" {{ old('location', $attendance->location) == 'Ramba' ? 'selected' : '' }}>
                                Ramba</option>
                            <option value="Bentayan" {{ old('location', $attendance->location) == 'Bentayan' ? 'selected' : '' }}>Bentayan</option>
                            <option value="Mangunjaya" {{ old('location', $attendance->location) == 'Mangunjaya' ? 'selected' : '' }}>Mangunjaya</option>
                            <option value="Keluang" {{ old('location', $attendance->location) == 'Keluang' ? 'selected' : '' }}>Keluang</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="recorded_by">Recorded By</label>
                <input type="text" name="recorded_by" id="recorded_by" class="form-control"
                    value="{{ old('recorded_by', $attendance->recorded_by) }}"
                    placeholder="Leave empty for QR scan records">
            </div>

            <!-- Absence Proof Section -->
            <div class="form-group"
                style="margin-top: 1.5rem; padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 8px; border: 1px solid var(--card-border);">
                <label class="form-label">📎 Absence Proof</label>

                @if($attendance->absence_proof)
                    <div
                        style="margin-bottom: 1rem; padding: 0.75rem; background: rgba(0, 255, 136, 0.1); border-radius: 8px; display: flex; align-items: center; gap: 1rem;">
                        <span style="color: var(--success);">Current file:</span>
                        <a href="{{ Storage::url($attendance->absence_proof) }}" target="_blank" class="btn btn-primary btn-sm">
                            <i class="bi bi-file-earmark-image"></i> View
                        </a>
                        <span
                            style="color: var(--text-muted); font-size: 0.85rem;">{{ basename($attendance->absence_proof) }}</span>
                    </div>
                @else
                    <div style="margin-bottom: 1rem; color: var(--text-muted);">No proof file attached</div>
                @endif

                <input type="file" name="absence_proof" id="absence_proof" class="form-control"
                    accept=".jpg,.jpeg,.png,.pdf" style="margin-bottom: 0.5rem;">
                <small style="color: var(--text-muted);">Upload new file to replace (JPG, PNG, PDF - max 10MB)</small>

                @if($attendance->absence_proof)
                    <div style="margin-top: 1rem; padding: 0.75rem; background: rgba(255, 165, 0, 0.1); border-radius: 8px;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--accent);">
                            <input type="checkbox" name="apply_to_all" value="1" style="accent-color: var(--primary);">
                            Apply new proof to ALL attendances with the same file
                            ({{ \App\Models\Attendance::where('absence_proof', $attendance->absence_proof)->count() }} records)
                        </label>
                    </div>
                @endif
            </div>

            @if($attendance->edited_by)
                <div class="alert alert-info"
                    style="background: rgba(0, 123, 255, 0.1); border: 1px solid rgba(0, 123, 255, 0.3); padding: 1rem; border-radius: 8px; margin-top: 1rem;">
                    <i class="bi bi-info-circle"></i>
                    Last edited by <strong>{{ $attendance->edited_by }}</strong> on
                    {{ $attendance->edited_at->format('d M Y H:i') }}
                </div>
            @endif

            <div class="d-flex gap-1" style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i>
                    Update Record
                </button>
                <a href="{{ route('historical.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-lg"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection