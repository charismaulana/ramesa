@extends('layouts.app')

@section('content')
    <div class="page-header d-flex justify-between align-items-center">
        <div>
            <h1 class="page-title">EDIT ROOM GROUP</h1>
            <p class="page-subtitle">{{ $group->name }} - {{ $group->location }}</p>
        </div>
        <a href="{{ route('rooms.manage', ['location' => $group->location]) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Manage
        </a>
    </div>

    <div class="row">
        <div class="col-4">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Group Settings</h2>
                </div>

                <form action="{{ route('rooms.updateGroup', $group->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="form-label" for="name">Group Name *</label>
                        <input type="text" name="name" id="name" class="form-control"
                            value="{{ old('name', $group->name) }}" required>
                        <small style="color: var(--text-muted);">This is the prefix for room matching (e.g., "GRU
                            A1")</small>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="bi bi-check-lg"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>

        <div class="col-8">
            <div class="card">
                <div class="card-header d-flex justify-between align-items-center">
                    <h2 class="card-title">Rooms in {{ $group->name }}</h2>
                    <span class="badge" style="background: var(--primary);">{{ $group->rooms->count() }} rooms</span>
                </div>

                <div class="table-container" style="margin: 0;">
                    <table style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Code</th>
                                <th style="width: 80px;">Capacity</th>
                                <th style="width: 80px;">Fillable</th>
                                <th>Notes</th>
                                <th style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group->rooms as $room)
                                <tr>
                                    <td><strong>{{ $room->room_code }}</strong></td>
                                    <td>{{ $room->capacity }}</td>
                                    <td>
                                        @if($room->is_fillable)
                                            <span style="color: var(--success);">✓ Yes</span>
                                        @else
                                            <span style="color: #888;">✗ No</span>
                                        @endif
                                    </td>
                                    <td style="color: var(--text-muted); font-size: 0.85rem;">{{ $room->notes ?? '-' }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('rooms.editRoom', $room->id) }}" class="btn btn-sm btn-secondary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('rooms.destroyRoom', $room->id) }}" method="POST"
                                                onsubmit="return confirm('Delete room {{ $room->room_code }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm"
                                                    style="background: rgba(255,68,68,0.2); color: #FF4444;">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection