@extends('layouts.app')

@section('content')
    <div class="page-header d-flex justify-between align-items-center">
        <div>
            <h1 class="page-title">MANAGE ROOMS</h1>
            <p class="page-subtitle">Setup room groups for {{ $location }}</p>
        </div>
        <div class="d-flex gap-1 align-items-center">
            <form action="{{ route('rooms.manage') }}" method="GET" class="d-flex gap-1">
                <select name="location" class="form-control" style="width: auto;" onchange="this.form.submit()">
                    @foreach($locations as $loc)
                        <option value="{{ $loc }}" {{ $location == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('rooms.dashboard', ['location' => $location]) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Add New Group Form - Inline -->
        <div class="col-12" style="margin-bottom: 1rem;">
            <div class="card" style="padding: 1rem;">
                <form action="{{ route('rooms.storeGroup') }}" method="POST" class="d-flex gap-1 align-items-end"
                    style="flex-wrap: wrap;">
                    @csrf
                    <input type="hidden" name="location" value="{{ $location }}">

                    <div style="flex: 1; min-width: 120px;">
                        <label class="form-label" for="name" style="font-size: 0.8rem; margin-bottom: 0.25rem;">Group Name
                            *</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="GRU, GRM..." required
                            style="padding: 0.5rem;">
                    </div>

                    <div style="flex: 3; min-width: 200px;">
                        <label class="form-label" for="rooms" style="font-size: 0.8rem; margin-bottom: 0.25rem;">Rooms
                            (comma-separated) *</label>
                        <input type="text" name="rooms" id="rooms" class="form-control" placeholder="A1, A2, A3, A4, A5..."
                            required style="padding: 0.5rem;">
                    </div>

                    <div style="width: 80px;">
                        <label class="form-label" for="capacity"
                            style="font-size: 0.8rem; margin-bottom: 0.25rem;">Capacity</label>
                        <input type="number" name="capacity" id="capacity" class="form-control" value="1" min="1" max="10"
                            style="padding: 0.5rem;">
                    </div>

                    <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; white-space: nowrap;">
                        <i class="bi bi-plus-lg"></i> Add Group
                    </button>
                </form>
            </div>
        </div>

        <!-- Existing Groups -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">📋 Room Groups for {{ $location }}</h2>
                </div>

                @if($roomGroups->isEmpty())
                    <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                        <i class="bi bi-house-door" style="font-size: 3rem; opacity: 0.5;"></i>
                        <p style="margin-top: 1rem;">No room groups defined yet.</p>
                        <p>Add your first group to start organizing rooms.</p>
                    </div>
                @else
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap; padding: 1rem;">
                        @foreach($roomGroups as $group)
                            <div
                                style="flex: 1; min-width: 250px; background: rgba(255, 255, 255, 0.03); border-radius: 8px; border: 1px solid rgba(100, 70, 40, 0.3); overflow: hidden;">
                                <div
                                    style="background: #3d1a0a; color: #d4a84b; padding: 0.75rem 1rem; display: flex; justify-content: space-between; align-items: center;">
                                    <strong style="text-transform: uppercase; letter-spacing: 0.5px;">{{ $group->name }}</strong>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('rooms.editGroup', $group->id) }}" class="btn btn-sm"
                                            style="background: rgba(255, 255, 255, 0.15); color: #d4a84b; border: none; padding: 0.25rem 0.5rem;">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('rooms.destroyGroup', $group->id) }}" method="POST"
                                            style="display: inline;"
                                            onsubmit="return confirm('Delete this room group and all its rooms?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm"
                                                style="background: rgba(200, 50, 50, 0.6); color: #ffaaaa; border: none; padding: 0.25rem 0.5rem;">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div style="padding: 0.75rem 1rem;">
                                    <div style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 0.5rem;">
                                        {{ $group->rooms->count() }} rooms
                                    </div>
                                    <div style="display: flex; flex-wrap: wrap; gap: 0.25rem;">
                                        @foreach($group->rooms as $room)
                                            <span class="badge"
                                                style="font-size: 0.75rem; background: rgba(100, 70, 40, 0.4); color: #ccc; {{ !$room->is_fillable ? 'background: #444; color: #888;' : '' }}">
                                                {{ $room->room_code }}
                                                @if($room->capacity > 1)<sup>×{{ $room->capacity }}</sup>@endif
                                                @if(!$room->is_fillable)<i class="bi bi-lock" style="margin-left: 2px;"></i>@endif
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection