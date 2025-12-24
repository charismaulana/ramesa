@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h1 class="page-title">ADD NEW EMPLOYEE</h1>
        <p class="page-subtitle">Create a new employee record with auto-generated QR code</p>
    </div>

    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Employee Information</h2>
                </div>

                <form action="{{ route('employees.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label class="form-label" for="employee_number">Employee Number <small
                                style="color: var(--text-muted);">(Leave blank for visitors/subcontractors - will
                                auto-generate)</small></label>
                        <input type="text" name="employee_number" id="employee_number" class="form-control"
                            value="{{ old('employee_number') }}" placeholder="e.g., 19010001 or leave blank">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="name">Full Name *</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}"
                            placeholder="Enter full name" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="company">Company</label>
                        <input type="text" name="company" id="company" class="form-control" value="{{ old('company') }}"
                            placeholder="e.g., GS Ramba, PT Subkon, Visitor">
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label" for="position">Position</label>
                                <input type="text" name="position" id="position" class="form-control"
                                    value="{{ old('position') }}" placeholder="e.g., Technician">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label" for="department">Department</label>
                                <select name="department" id="department" class="form-control">
                                    <option value="">Select Department</option>
                                    <option value="GS" {{ old('department') == 'GS' ? 'selected' : '' }}>GS</option>
                                    <option value="ICT" {{ old('department') == 'ICT' ? 'selected' : '' }}>ICT</option>
                                    <option value="SCM" {{ old('department') == 'SCM' ? 'selected' : '' }}>SCM</option>
                                    <option value="HSSE" {{ old('department') == 'HSSE' ? 'selected' : '' }}>HSSE</option>
                                    <option value="PO" {{ old('department') == 'PO' ? 'selected' : '' }}>PO</option>
                                    <option value="RAM" {{ old('department') == 'RAM' ? 'selected' : '' }}>RAM</option>
                                    <option value="WS" {{ old('department') == 'WS' ? 'selected' : '' }}>WS</option>
                                    <option value="FM" {{ old('department') == 'FM' ? 'selected' : '' }}>FM</option>
                                    <option value="RELATION" {{ old('department') == 'RELATION' ? 'selected' : '' }}>RELATION
                                    </option>
                                    <option value="PE" {{ old('department') == 'PE' ? 'selected' : '' }}>PE</option>
                                    <option value="Plan & Eval" {{ old('department') == 'Plan & Eval' ? 'selected' : '' }}>
                                        Plan & Eval</option>
                                    <option value="LMF" {{ old('department') == 'LMF' ? 'selected' : '' }}>LMF</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label" for="location">Homebase *</label>
                                <select name="location" id="location" class="form-control" required>
                                    <option value="">Select Homebase</option>
                                    <option value="Ramba" {{ old('location') == 'Ramba' ? 'selected' : '' }}>Ramba</option>
                                    <option value="Bentayan" {{ old('location') == 'Bentayan' ? 'selected' : '' }}>Bentayan
                                    </option>
                                    <option value="Mangunjaya" {{ old('location') == 'Mangunjaya' ? 'selected' : '' }}>
                                        Mangunjaya</option>
                                    <option value="Keluang" {{ old('location') == 'Keluang' ? 'selected' : '' }}>Keluang
                                    </option>
                                    <option value="Rig 01" {{ old('location') == 'Rig 01' ? 'selected' : '' }}>Rig 01</option>
                                    <option value="Rig 02" {{ old('location') == 'Rig 02' ? 'selected' : '' }}>Rig 02</option>
                                    <option value="Rig 03" {{ old('location') == 'Rig 03' ? 'selected' : '' }}>Rig 03</option>
                                    <option value="Rig 06" {{ old('location') == 'Rig 06' ? 'selected' : '' }}>Rig 06</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label" for="accommodation">Accommodation</label>
                                <input type="text" name="accommodation" id="accommodation" class="form-control"
                                    value="{{ old('accommodation') }}" placeholder="e.g., Camp 1">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label" for="active_status">Active Status *</label>
                                <select name="active_status" id="active_status" class="form-control" required>
                                    <option value="active" {{ old('active_status') == 'active' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="inactive" {{ old('active_status') == 'inactive' ? 'selected' : '' }}>
                                        Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label" for="employee_status">Employee Status *</label>
                                <select name="employee_status" id="employee_status" class="form-control" required>
                                    <option value="">Select Status</option>
                                    <option value="Pekerja" {{ old('employee_status') == 'Pekerja' ? 'selected' : '' }}>
                                        Pekerja</option>
                                    <option value="TA" {{ old('employee_status') == 'TA' ? 'selected' : '' }}>TA</option>
                                    <option value="TKJP" {{ old('employee_status') == 'TKJP' ? 'selected' : '' }}>TKJP
                                    </option>
                                    <option value="Contractor" {{ old('employee_status') == 'Contractor' ? 'selected' : '' }}>
                                        Contractor</option>
                                    <option value="Visitor" {{ old('employee_status') == 'Visitor' ? 'selected' : '' }}>
                                        Visitor</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-1 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i>
                            Create Employee
                        </button>
                        <a href="{{ route('employees.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Information</h2>
                </div>

                <div style="color: var(--text-secondary);">
                    <div
                        style="padding: 1.5rem; background: linear-gradient(135deg, rgba(255, 69, 0, 0.1), rgba(255, 140, 0, 0.05)); border-radius: 12px; margin-bottom: 1rem;">
                        <h4 style="color: var(--accent); margin-bottom: 0.5rem;">
                            <i class="bi bi-qr-code"></i> Auto QR Code Generation
                        </h4>
                        <p style="margin: 0;">A unique QR code will be automatically generated using the employee number
                            when you create this record.</p>
                    </div>

                    <div
                        style="padding: 1.5rem; background: rgba(255, 255, 255, 0.03); border-radius: 12px; border: 1px solid var(--card-border);">
                        <h4 style="color: var(--primary-light); margin-bottom: 1rem;">Required Fields</h4>
                        <ul style="padding-left: 1.5rem; line-height: 1.8;">
                            <li><strong>Employee Number</strong> - Must be unique</li>
                            <li><strong>Full Name</strong> - Employee's full name</li>
                            <li><strong>Status</strong> - Active or Inactive</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection