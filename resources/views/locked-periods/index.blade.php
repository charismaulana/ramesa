@extends('layouts.app')

@section('content')
    <div class="page-header d-flex justify-between align-items-center">
        <div>
            <h1 class="page-title">LOCKED PERIODS</h1>
            <p class="page-subtitle">Manage date periods that are locked from editing by Tim Catering</p>
        </div>
        <a href="{{ route('admin.users') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Admin
        </a>
    </div>

    <div class="row">
        <!-- Create New Lock Form -->
        <div class="col-4">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🔒 Lock New Period</h2>
                </div>

                <form action="{{ route('admin.lockedPeriods.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label class="form-label" for="start_date">Start Date *</label>
                        <input type="date" name="start_date" id="start_date" class="form-control"
                            value="{{ old('start_date') }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="end_date">End Date *</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date') }}"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="location">Location</label>
                        <select name="location" id="location" class="form-control">
                            <option value="">All Locations</option>
                            <option value="Ramba">Ramba</option>
                            <option value="Bentayan">Bentayan</option>
                            <option value="Mangunjaya">Mangunjaya</option>
                            <option value="Keluang">Keluang</option>
                        </select>
                        <small style="color: var(--text-muted);">Leave empty to lock all locations</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="reason">Reason (Optional)</label>
                        <input type="text" name="reason" id="reason" class="form-control" value="{{ old('reason') }}"
                            placeholder="e.g. Monthly close, Audit period">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="bi bi-lock"></i> Lock Period
                    </button>
                </form>
            </div>
        </div>

        <!-- Existing Locked Periods -->
        <div class="col-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">📋 Active Locked Periods</h2>
                </div>

                @if($lockedPeriods->isEmpty())
                    <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                        <i class="bi bi-unlock" style="font-size: 3rem; opacity: 0.5;"></i>
                        <p style="margin-top: 1rem;">No periods are currently locked.</p>
                        <p>Tim Catering can edit all attendance records.</p>
                    </div>
                @else
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>Location</th>
                                    <th>Reason</th>
                                    <th>Locked By</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lockedPeriods as $period)
                                    <tr>
                                        <td>
                                            <strong>{{ $period->start_date->format('d/m/Y') }}</strong>
                                            <span style="color: var(--text-muted);">to</span>
                                            <strong>{{ $period->end_date->format('d/m/Y') }}</strong>
                                        </td>
                                        <td>
                                            @if($period->location)
                                                <span class="badge badge-primary">{{ $period->location }}</span>
                                            @else
                                                <span class="badge badge-warning">All Locations</span>
                                            @endif
                                        </td>
                                        <td>{{ $period->reason ?? '-' }}</td>
                                        <td>{{ $period->lockedByUser->name ?? 'Unknown' }}</td>
                                        <td>{{ $period->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <form action="{{ route('admin.lockedPeriods.destroy', $period->id) }}" method="POST"
                                                style="display: inline;"
                                                onsubmit="return confirm('Are you sure you want to unlock this period? Tim Catering will be able to edit records in this date range.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm"
                                                    style="background: rgba(255, 68, 68, 0.2); color: #FF4444; border: 1px solid rgba(255, 68, 68, 0.3);">
                                                    <i class="bi bi-unlock"></i> Unlock
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection