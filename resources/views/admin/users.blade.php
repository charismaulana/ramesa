@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h1 class="page-title">USER MANAGEMENT</h1>
        <p class="page-subtitle">Approve and manage user accounts</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Pending Approval</h2>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Registered</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users->where('is_approved', false) as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge {{ $user->role === 'tim_catering' ? 'badge-primary' : 'badge-warning' }}">
                                    {{ $user->role === 'tim_catering' ? 'Tim Catering' : 'Employee' }}
                                </span>
                            </td>
                            <td>{{ $user->created_at->format('d M Y H:i') }}</td>
                            <td><span class="badge"
                                    style="background: rgba(255, 215, 0, 0.2); color: var(--accent);">Pending</span></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <form action="{{ route('admin.approve', $user) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm" title="Approve">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.reject', $user) }}" method="POST" style="display: inline;"
                                        onsubmit="return confirm('Hapus user ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Reject">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 2rem; color: var(--text-muted);">
                                Tidak ada user yang menunggu approval
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <h2 class="card-title">Approved Users</h2>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users->where('is_approved', true) as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <form action="{{ route('admin.updateRole', $user) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <select name="role" class="form-control" style="width: auto; padding: 0.5rem;"
                                        onchange="this.form.submit()">
                                        <option value="tim_catering" {{ $user->role === 'tim_catering' ? 'selected' : '' }}>Tim
                                            Catering</option>
                                        <option value="employee" {{ $user->role === 'employee' ? 'selected' : '' }}>Employee
                                        </option>
                                    </select>
                                </form>
                            </td>
                            <td>{{ $user->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <form action="{{ route('admin.reject', $user) }}" method="POST" style="display: inline;"
                                    onsubmit="return confirm('Hapus user ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center" style="padding: 2rem; color: var(--text-muted);">
                                Tidak ada user yang sudah diapprove
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection