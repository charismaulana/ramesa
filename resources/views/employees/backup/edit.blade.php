@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h1 class="page-title">EDIT EMPLOYEE</h1>
        <p class="page-subtitle">Update employee information</p>
    </div>

    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Employee Information</h2>
                </div>

                <form action="{{ route('employees.update', $employee) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="form-label" for="employee_number">Employee Number *</label>
                        <input type="text" name="employee_number" id="employee_number" class="form-control"
                            value="{{ old('employee_number', $employee->employee_number) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="name">Full Name *</label>
                        <input type="text" name="name" id="name" class="form-control"
                            value="{{ old('name', $employee->name) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="company">Company</label>
                        <input type="text" name="company" id="company" class="form-control"
                            value="{{ old('company', $employee->company) }}"
                            placeholder="e.g., GS Ramba, PT Subkon, Visitor">
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label" for="position">Position</label>
                                <input type="text" name="position" id="position" class="form-control"
                                    value="{{ old('position', $employee->position) }}">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label" for="department">Department</label>
                                <select name="department" id="department" class="form-control">
                                    <option value="">Select Department</option>
                                    <option value="GS" {{ old('department', $employee->department) == 'GS' ? 'selected' : '' }}>GS</option>
                                    <option value="ICT" {{ old('department', $employee->department) == 'ICT' ? 'selected' : '' }}>ICT</option>
                                    <option value="SCM" {{ old('department', $employee->department) == 'SCM' ? 'selected' : '' }}>SCM</option>
                                    <option value="HSSE" {{ old('department', $employee->department) == 'HSSE' ? 'selected' : '' }}>HSSE</option>
                                    <option value="PO" {{ old('department', $employee->department) == 'PO' ? 'selected' : '' }}>PO</option>
                                    <option value="RAM" {{ old('department', $employee->department) == 'RAM' ? 'selected' : '' }}>RAM</option>
                                    <option value="WS" {{ old('department', $employee->department) == 'WS' ? 'selected' : '' }}>WS</option>
                                    <option value="FM" {{ old('department', $employee->department) == 'FM' ? 'selected' : '' }}>FM</option>
                                    <option value="RELATION" {{ old('department', $employee->department) == 'RELATION' ? 'selected' : '' }}>RELATION</option>
                                    <option value="PE" {{ old('department', $employee->department) == 'PE' ? 'selected' : '' }}>PE</option>
                                    <option value="Plan & Eval" {{ old('department', $employee->department) == 'Plan & Eval' ? 'selected' : '' }}>Plan & Eval</option>
                                    <option value="LMF" {{ old('department', $employee->department) == 'LMF' ? 'selected' : '' }}>LMF</option>
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
                                    <option value="Ramba" {{ old('location', $employee->location) == 'Ramba' ? 'selected' : '' }}>Ramba</option>
                                    <option value="Bentayan" {{ old('location', $employee->location) == 'Bentayan' ? 'selected' : '' }}>Bentayan</option>
                                    <option value="Mangunjaya" {{ old('location', $employee->location) == 'Mangunjaya' ? 'selected' : '' }}>Mangunjaya</option>
                                    <option value="Keluang" {{ old('location', $employee->location) == 'Keluang' ? 'selected' : '' }}>Keluang</option>
                                    <option value="Rig 01" {{ old('location', $employee->location) == 'Rig 01' ? 'selected' : '' }}>Rig 01</option>
                                    <option value="Rig 02" {{ old('location', $employee->location) == 'Rig 02' ? 'selected' : '' }}>Rig 02</option>
                                    <option value="Rig 03" {{ old('location', $employee->location) == 'Rig 03' ? 'selected' : '' }}>Rig 03</option>
                                    <option value="Rig 06" {{ old('location', $employee->location) == 'Rig 06' ? 'selected' : '' }}>Rig 06</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label" for="accommodation">Accommodation</label>
                                <input type="text" name="accommodation" id="accommodation" class="form-control"
                                    value="{{ old('accommodation', $employee->accommodation) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label" for="active_status">Active Status *</label>
                                <select name="active_status" id="active_status" class="form-control" required>
                                    <option value="active" {{ old('active_status', $employee->active_status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('active_status', $employee->active_status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label" for="employee_status">Employee Status *</label>
                                <select name="employee_status" id="employee_status" class="form-control" required>
                                    <option value="">Select Status</option>
                                    <option value="Pekerja" {{ old('employee_status', $employee->employee_status) == 'Pekerja' ? 'selected' : '' }}>Pekerja</option>
                                    <option value="TA" {{ old('employee_status', $employee->employee_status) == 'TA' ? 'selected' : '' }}>TA</option>
                                    <option value="TKJP" {{ old('employee_status', $employee->employee_status) == 'TKJP' ? 'selected' : '' }}>TKJP</option>
                                    <option value="Contractor" {{ old('employee_status', $employee->employee_status) == 'Contractor' ? 'selected' : '' }}>Contractor
                                    </option>
                                    <option value="Visitor" {{ old('employee_status', $employee->employee_status) == 'Visitor' ? 'selected' : '' }}>Visitor</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-1 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i>
                            Update Employee
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
                    <h2 class="card-title">Quick Actions</h2>
                </div>

                <div class="d-flex gap-1" style="flex-wrap: wrap;">
                    <a href="{{ route('employees.printCard', $employee) }}" class="btn btn-primary">
                        <i class="bi bi-credit-card"></i>
                        View Meal Card
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection