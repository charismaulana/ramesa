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

        <form action="{{ route('historical.update', $attendance->id) }}" method="POST" style="padding: 2rem;">
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