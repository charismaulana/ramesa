@extends('layouts.app')

@section('content')
    <div class="page-header d-flex justify-between align-items-center">
        <div>
            <h1 class="page-title">EDIT ROOM</h1>
            <p class="page-subtitle">{{ $room->roomGroup->name }} {{ $room->room_code }} - {{ $room->location }}</p>
        </div>
        <a href="{{ route('rooms.editGroup', $room->room_group_id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Group
        </a>
    </div>

    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Room Settings</h2>
                </div>

                <form action="{{ route('rooms.updateRoom', $room->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="form-label" for="room_code">Room Code *</label>
                        <input type="text" name="room_code" id="room_code" class="form-control"
                            value="{{ old('room_code', $room->room_code) }}" required>
                        <small style="color: var(--text-muted);">Full room key will be: "{{ $room->roomGroup->name }}
                            [code]"</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="capacity">Capacity *</label>
                        <input type="number" name="capacity" id="capacity" class="form-control"
                            value="{{ old('capacity', $room->capacity) }}" min="0" max="10" required>
                        <small style="color: var(--text-muted);">Number of people this room can hold (0 = not for
                            occupancy)</small>
                    </div>

                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" name="is_fillable" value="1" {{ old('is_fillable', $room->is_fillable) ? 'checked' : '' }}>
                            <span style="margin-left: 0.5rem;">Room can be occupied</span>
                        </label>
                        <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">
                            Uncheck for storage rooms, warehouses, or other non-occupiable spaces
                        </small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="notes">Notes</label>
                        <input type="text" name="notes" id="notes" class="form-control"
                            value="{{ old('notes', $room->notes) }}" placeholder="e.g., Warehouse, VIP Room, Storage">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="bi bi-check-lg"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>

        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Room Info</h2>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; padding: 1rem;">
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.85rem;">Full Room Key</div>
                        <div style="font-size: 1.25rem; font-weight: bold; color: var(--accent);">
                            {{ $room->roomGroup->name }} {{ $room->room_code }}
                        </div>
                    </div>
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.85rem;">Location</div>
                        <div style="font-size: 1.25rem; font-weight: bold;">{{ $room->location }}</div>
                    </div>
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.85rem;">Group</div>
                        <div style="font-size: 1.25rem; font-weight: bold;">{{ $room->roomGroup->name }}</div>
                    </div>
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.85rem;">Status</div>
                        <div style="font-size: 1.25rem;">
                            @if($room->is_fillable)
                                <span style="color: var(--success);">Fillable</span>
                            @else
                                <span style="color: #888;">Not Fillable</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div style="padding: 1rem; border-top: 1px solid var(--card-border);">
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;">
                        To assign an employee to this room, set their accommodation field to:
                    </p>
                    <code
                        style="background: rgba(255,165,0,0.2); padding: 0.5rem 1rem; border-radius: 4px; display: block;">
                            {{ $room->roomGroup->name }} {{ $room->room_code }}
                        </code>
                </div>
            </div>
        </div>
    </div>
@endsection