@extends('layouts.app')

@section('content')
    <div class="page-header d-flex justify-between align-items-center">
        <div>
            <h1 class="page-title">BULK ATTENDANCE INPUT</h1>
            <p class="page-subtitle">Input multiple employee meals at once (max 200 entries)</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="openGroupsModal()">
            <i class="bi bi-people"></i> Manage Groups
        </button>
    </div>

    <form action="{{ route('bulk.store') }}" method="POST" id="bulk-form" enctype="multipart/form-data">
        @csrf

        <div class="card">
            <div class="card-header d-flex justify-between align-items-center">
                <h2 class="card-title">Input Settings</h2>
            </div>

            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label class="form-label" for="date">Date *</label>
                        <input type="date" name="date" id="date" class="form-control"
                            value="{{ old('date', date('Y-m-d')) }}" required>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label class="form-label" for="recorded_by">Recorded By *</label>
                        <input type="text" name="recorded_by" id="recorded_by" class="form-control"
                            value="{{ old('recorded_by') }}" placeholder="Your name" required>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label class="form-label" for="location">Meal Location *</label>
                        <select name="location" id="location" class="form-control" required>
                            <option value="Ramba">Ramba</option>
                            <option value="Bentayan">Bentayan</option>
                            <option value="Mangunjaya">Mangunjaya</option>
                            <option value="Keluang">Keluang</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label" for="absence_proof">
                            📎 Absence Proof (Image/PDF) - Optional
                        </label>
                        <input type="file" name="absence_proof" id="absence_proof" class="form-control"
                            accept=".jpg,.jpeg,.png,.pdf" onchange="previewFile()">
                        <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">
                            Accepted formats: JPG, PNG, PDF (max 10MB)
                        </small>
                        <div id="file-preview" style="margin-top: 0.5rem; display: none;">
                            <span style="color: var(--success);">
                                ✓ <span id="file-name"></span>
                            </span>
                        </div>
                    </div>
                </div>
                @if($groups->count() > 0)
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">👥 Quick Load from Group</label>
                            <div style="display: flex; gap: 0.5rem;">
                                <select id="groupSelector" class="form-control" style="flex: 1;">
                                    <option value="">-- Select a group --</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}" data-employees='@json($group->employees)'>
                                            {{ $group->name }} ({{ $group->employees->count() }})
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-primary" onclick="loadGroup()">
                                    <i class="bi bi-download"></i> Load
                                </button>
                            </div>
                            <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">
                                Auto-fill B/L/D for all group members
                            </small>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-between align-items-center">
                <h2 class="card-title">Entries</h2>
                <button type="button" class="btn btn-primary btn-sm" onclick="addEntry()">
                    <i class="bi bi-plus-lg"></i> Add Entry
                </button>
            </div>

            <div id="entries-container">
                <!-- Entry rows will be added here -->
            </div>

            <div style="padding: 1rem; border-top: 1px solid var(--card-border);">
                <div class="d-flex gap-1 bulk-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Submit All
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="clearAll()">
                        <i class="bi bi-x-lg"></i> Clear All
                    </button>
                    <a href="{{ route('scan.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Scan
                    </a>
                </div>
            </div>
        </div>
    </form>

    <!-- Groups Management Modal -->
    <div id="groupsModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; overflow-y: auto;">
        <div class="groups-modal-content"
            style="position: relative; max-width: 900px; max-height: 90vh; margin: 5vh auto; background: #1a0a0a; border: 2px solid var(--primary); border-radius: 12px; padding: 1.5rem; overflow-y: auto;">
            <!-- Close Button -->
            <button type="button" onclick="closeGroupsModal()"
                style="position: absolute; top: 1rem; right: 1rem; background: transparent; border: none; color: var(--text-muted); font-size: 1.5rem; cursor: pointer; padding: 0.25rem; line-height: 1; z-index: 10;">
                <i class="bi bi-x-lg"></i>
            </button>

            <h2 style="font-size: 1.25rem; margin-bottom: 1rem; color: var(--primary);">👥 Manage Employee Groups</h2>

            <!-- Two Column Layout - Responsive -->
            <div class="groups-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <!-- Left Column: Create/Edit Group -->
                <div>
                    <h3 style="font-size: 0.9rem; margin-bottom: 0.75rem; color: var(--text-muted);">Create / Edit Group
                    </h3>

                    <!-- Group Name -->
                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <label class="form-label">Group Name</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="text" id="groupName" class="form-control" placeholder="e.g., ramba-staff-1"
                                style="flex: 1;">
                            <button type="button" class="btn btn-primary" onclick="saveGroup()">
                                <i class="bi bi-check-lg"></i> <span id="saveButtonText">Create</span>
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="cancelGroupEdit()" id="cancelButton"
                                style="display: none;">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Search Employees -->
                    <div class="form-group" style="margin-bottom: 0.5rem;">
                        <label class="form-label">Select Employees ({{ $employees->count() }} total)</label>
                        <input type="text" id="searchEmployeeInput" class="form-control"
                            placeholder="🔍 Search by name or employee number..." onkeyup="filterEmployees()">
                    </div>

                    <!-- Employee List - Fixed Height -->
                    <div
                        style="height: calc(90vh - 320px); max-height: 400px; overflow-y: auto; border: 1px solid var(--card-border); border-radius: 8px; padding: 0.5rem; background: rgba(0,0,0,0.2);">
                        <div
                            style="margin-bottom: 0.5rem; display: flex; gap: 0.5rem; position: sticky; top: 0; background: rgba(26,10,10,0.95); padding: 0.25rem 0; z-index: 1;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="selectAllEmployees()">
                                Select All
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="deselectAllEmployees()">
                                Deselect All
                            </button>
                            <span id="selectedCount"
                                style="color: var(--primary); font-size: 0.85rem; margin-left: auto; align-self: center;">0
                                selected</span>
                        </div>
                        <div id="employeeCheckboxes">
                            @foreach($employees as $employee)
                                <label class="employee-checkbox-label" data-name="{{ strtolower($employee->name) }}"
                                    data-number="{{ strtolower($employee->employee_number) }}"
                                    style="display: block; padding: 0.4rem 0.5rem; border-bottom: 1px solid var(--card-border); cursor: pointer; font-size: 0.85rem;">
                                    <input type="checkbox" class="employee-checkbox" value="{{ $employee->id }}"
                                        style="margin-right: 0.5rem;" onchange="updateSelectedCount()">
                                    {{ $employee->employee_number }} - {{ $employee->name }}
                                    ({{ $employee->department ?? 'N/A' }})
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Right Column: Existing Groups -->
                <div>
                    <h3 style="font-size: 0.9rem; margin-bottom: 0.75rem; color: var(--text-muted);">Existing Groups</h3>

                    <!-- Search Groups -->
                    <input type="text" id="searchGroupInput" class="form-control" placeholder="🔍 Search groups..."
                        onkeyup="filterGroups()" style="margin-bottom: 0.5rem;">

                    <!-- Groups List - Fixed Height -->
                    <div id="groupsList"
                        style="height: calc(90vh - 200px); max-height: 520px; overflow-y: auto; border: 1px solid var(--card-border); border-radius: 8px; padding: 0.5rem; background: rgba(0,0,0,0.2);">
                        <!-- Groups will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .entry-row {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid var(--card-border);
            background: rgba(255, 255, 255, 0.02);
        }

        .entry-row:hover {
            background: rgba(255, 255, 255, 0.04);
        }

        .entry-number {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-family: 'Orbitron', sans-serif;
            flex-shrink: 0;
        }

        .entry-content {
            flex: 1;
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
        }

        .employee-select {
            flex: 1;
            min-width: 250px;
        }

        .meal-checkboxes {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .meal-checkbox {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.5rem 0.75rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--card-border);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .meal-checkbox:hover {
            background: rgba(255, 69, 0, 0.1);
            border-color: var(--primary);
        }

        .meal-checkbox input:checked+span {
            color: var(--accent);
        }

        .meal-checkbox input {
            accent-color: var(--primary);
        }

        .btn-remove {
            background: rgba(255, 68, 68, 0.2);
            color: var(--error);
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-remove:hover {
            background: var(--error);
            color: white;
        }

        .employee-search-container {
            position: relative;
        }

        .employee-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #1a0a0a;
            border: 2px solid var(--primary);
            border-radius: 8px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }

        .employee-suggestion {
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid var(--card-border);
        }

        .employee-suggestion:hover {
            background: rgba(255, 69, 0, 0.2);
        }

        .employee-suggestion:last-child {
            border-bottom: none;
        }

        .selected-employee {
            background: rgba(255, 69, 0, 0.1);
            border: 1px solid rgba(255, 69, 0, 0.3);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .no-entries {
            padding: 3rem;
            text-align: center;
            color: var(--text-muted);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .bulk-actions {
                flex-direction: column !important;
            }

            .bulk-actions .btn {
                width: 100%;
                justify-content: center;
            }

            .entry-row {
                flex-direction: column;
                gap: 0.75rem;
            }

            .entry-content {
                flex-direction: column;
                width: 100%;
                gap: 0.75rem;
            }

            .employee-select {
                min-width: 100%;
            }

            .meal-checkboxes {
                width: 100%;
                justify-content: space-between;
            }

            .meal-checkbox {
                flex: 1;
                justify-content: center;
                font-size: 0.85rem;
                padding: 0.4rem 0.5rem;
            }

            .row .col-4 {
                flex: 0 0 100%;
                max-width: 100%;
            }

            .row .col-12 .row {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            .meal-checkbox {
                padding: 0.35rem 0.4rem;
                font-size: 0.75rem;
            }

            .entry-number {
                width: 32px;
                height: 32px;
                font-size: 0.8rem;
            }
        }

        /* Groups Modal Mobile Responsive */
        @media (max-width: 768px) {
            .groups-modal-content {
                margin: 1rem !important;
                max-height: calc(100vh - 2rem) !important;
                padding: 1rem !important;
            }

            .groups-grid {
                grid-template-columns: 1fr !important;
                gap: 1rem !important;
            }

            #groupsModal h2 {
                font-size: 1.1rem !important;
                padding-right: 2rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        const employees = @json($employees);
        let entryCount = 0;

        function addEntry() {
            if (entryCount >= 200) {
                alert('Maximum 200 entries allowed');
                return;
            }

            entryCount++;
            const container = document.getElementById('entries-container');
            const entryHtml = `
                                                                                                <div class="entry-row" id="entry-${entryCount}">
                                                                                                    <div class="entry-number">${entryCount}</div>
                                                                                                    <div class="entry-content">
                                                                                                        <div class="employee-select">
                                                                                                            <div class="employee-search-container">
                                                                                                                <input type="text" class="form-control employee-search" 
                                                                                                                    placeholder="Search employee..." 
                                                                                                                    onkeyup="searchEmployee(this, ${entryCount})"
                                                                                                                    onfocus="showSuggestions(${entryCount})"
                                                                                                                    data-entry="${entryCount}">
                                                                                                                <input type="hidden" name="entries[${entryCount}][employee_id]" id="employee-id-${entryCount}">
                                                                                                                <div class="employee-suggestions" id="suggestions-${entryCount}"></div>
                                                                                                            </div>
                                                                                                            <div class="selected-employee" id="selected-${entryCount}" style="display: none; margin-top: 0.5rem;">
                                                                                                                <span id="selected-name-${entryCount}"></span>
                                                                                                                <button type="button" class="btn-remove" style="width:24px;height:24px;" onclick="clearEmployee(${entryCount})">
                                                                                                                    <i class="bi bi-x"></i>
                                                                                                                </button>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                        <div class="meal-checkboxes">
                                                                                                            <label class="meal-checkbox">
                                                                                                                <input type="checkbox" name="entries[${entryCount}][meals][]" value="breakfast">
                                                                                                                <span>🌅 B'fast</span>
                                                                                                            </label>
                                                                                                            <label class="meal-checkbox">
                                                                                                                <input type="checkbox" name="entries[${entryCount}][meals][]" value="lunch">
                                                                                                                <span>☀️ Lunch</span>
                                                                                                            </label>
                                                                                                            <label class="meal-checkbox">
                                                                                                                <input type="checkbox" name="entries[${entryCount}][meals][]" value="dinner">
                                                                                                                <span>🌙 Dinner</span>
                                                                                                            </label>
                                                                                                            <label class="meal-checkbox">
                                                                                                                <input type="checkbox" name="entries[${entryCount}][meals][]" value="supper">
                                                                                                                <span>🌃 Supper</span>
                                                                                                            </label>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <button type="button" class="btn-remove" onclick="removeEntry(${entryCount})">
                                                                                                        <i class="bi bi-trash"></i>
                                                                                                    </button>
                                                                                                </div>
                                                                                            `;
            container.insertAdjacentHTML('beforeend', entryHtml);
            updateNoEntriesMessage();
        }

        function removeEntry(index) {
            document.getElementById(`entry-${index}`).remove();
            updateNoEntriesMessage();
            renumberEntries();
        }

        function renumberEntries() {
            const entries = document.querySelectorAll('.entry-row');
            entries.forEach((entry, idx) => {
                entry.querySelector('.entry-number').textContent = idx + 1;
            });
        }

        function updateNoEntriesMessage() {
            const container = document.getElementById('entries-container');
            const entries = container.querySelectorAll('.entry-row');

            let noEntriesEl = container.querySelector('.no-entries');

            if (entries.length === 0) {
                if (!noEntriesEl) {
                    container.innerHTML = `
                                                                                                        <div class="no-entries">
                                                                                                            <i class="bi bi-inbox" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                                                                                                            <p>No entries yet. Click "Add Entry" to start.</p>
                                                                                                        </div>
                                                                                                    `;
                }
            } else {
                if (noEntriesEl) {
                    noEntriesEl.remove();
                }
            }
        }

        function searchEmployee(input, entryIndex) {
            const query = input.value.toLowerCase();
            const suggestionsEl = document.getElementById(`suggestions-${entryIndex}`);

            if (query.length < 1) {
                suggestionsEl.style.display = 'none';
                return;
            }

            const filtered = employees.filter(emp =>
                emp.name.toLowerCase().includes(query) ||
                emp.employee_number.toLowerCase().includes(query) ||
                (emp.department && emp.department.toLowerCase().includes(query))
            ).slice(0, 10);

            if (filtered.length === 0) {
                suggestionsEl.innerHTML = '<div class="employee-suggestion">No employees found</div>';
            } else {
                suggestionsEl.innerHTML = filtered.map(emp => `
                                                                                                    <div class="employee-suggestion" onclick="selectEmployee(${entryIndex}, ${emp.id}, '${emp.employee_number}', '${emp.name.replace(/'/g, "\\'")}', '${(emp.department || '').replace(/'/g, "\\'")}', '${(emp.employee_status || '').replace(/'/g, "\\'")}')">
                                                                                                        <strong>${emp.employee_number}</strong> - ${emp.name}
                                                                                                        <span style="color: var(--text-muted);"> (${emp.department || ''} • ${emp.employee_status || ''})</span>
                                                                                                    </div>
                                                                                                `).join('');
            }

            suggestionsEl.style.display = 'block';
        }

        function showSuggestions(entryIndex) {
            // Show suggestions when focused
            const input = document.querySelector(`[data-entry="${entryIndex}"]`);
            if (input.value.length >= 1) {
                searchEmployee(input, entryIndex);
            }
        }

        function selectEmployee(entryIndex, id, number, name, department, employeeStatus) {
            document.getElementById(`employee-id-${entryIndex}`).value = id;
            document.getElementById(`selected-name-${entryIndex}`).innerHTML =
                `${number} - <strong>${name}</strong>` + (department ? ` <span style="color: var(--text-muted);">(${department} • ${employeeStatus || ''})</span>` : '');
            document.getElementById(`selected-${entryIndex}`).style.display = 'flex';
            document.getElementById(`suggestions-${entryIndex}`).style.display = 'none';

            // Hide search input
            const searchInput = document.querySelector(`[data-entry="${entryIndex}"]`);
            searchInput.style.display = 'none';
        }

        function clearEmployee(entryIndex) {
            document.getElementById(`employee-id-${entryIndex}`).value = '';
            document.getElementById(`selected-${entryIndex}`).style.display = 'none';

            const searchInput = document.querySelector(`[data-entry="${entryIndex}"]`);
            searchInput.style.display = 'block';
            searchInput.value = '';
            searchInput.focus();
        }

        function clearAll() {
            if (confirm('Clear all entries?')) {
                document.getElementById('entries-container').innerHTML = '';
                entryCount = 0;
                updateNoEntriesMessage();
            }
        }

        // Hide suggestions when clicking outside
        document.addEventListener('click', function (e) {
            if (!e.target.classList.contains('employee-search') && !e.target.closest('.employee-suggestions')) {
                document.querySelectorAll('.employee-suggestions').forEach(el => {
                    el.style.display = 'none';
                });
            }
        });

        // Initialize with empty message
        updateNoEntriesMessage();

        // Form validation
        document.getElementById('bulk-form').addEventListener('submit', function (e) {
            const entries = document.querySelectorAll('.entry-row');
            let valid = true;
            let errorMsg = '';

            if (entries.length === 0) {
                e.preventDefault();
                alert('Please add at least one entry');
                return;
            }

            entries.forEach((entry, idx) => {
                const employeeId = entry.querySelector('input[type="hidden"]').value;
                const meals = entry.querySelectorAll('input[type="checkbox"]:checked');

                if (!employeeId) {
                    valid = false;
                    errorMsg = `Entry ${idx + 1}: Please select an employee`;
                } else if (meals.length === 0) {
                    valid = false;
                    errorMsg = `Entry ${idx + 1}: Please select at least one meal`;
                }
            });

            if (!valid) {
                e.preventDefault();
                alert(errorMsg);
            }
        });

        // File preview function
        function previewFile() {
            const fileInput = document.getElementById('absence_proof');
            const preview = document.getElementById('file-preview');
            const fileName = document.getElementById('file-name');

            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                fileName.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        }

        // Load Group function
        function loadGroup() {
            const selector = document.getElementById('groupSelector');
            const selectedOption = selector.options[selector.selectedIndex];

            if (!selectedOption.value) {
                alert('Please select a group');
                return;
            }

            const groupEmployees = JSON.parse(selectedOption.getAttribute('data-employees'));

            if (groupEmployees.length === 0) {
                alert('This group has no employees');
                return;
            }

            if (groupEmployees.length > 200) {
                alert('This group has too many employees (max 200)');
                return;
            }

            // Clear existing entries
            if (entryCount > 0) {
                if (!confirm(`This will clear ${entryCount} existing entries. Continue?`)) {
                    return;
                }
                clearAll();
            }

            // Add entry for each employee with B, L, D auto-checked
            groupEmployees.forEach((employee) => {
                addEntry();

                // Get the current entry number
                const currentEntry = entryCount;

                // Wait a tiny bit for DOM to update, then select employee
                setTimeout(() => {
                    // Set the hidden employee ID input
                    const employeeIdInput = document.getElementById(`employee-id-${currentEntry}`);
                    if (employeeIdInput) {
                        employeeIdInput.value = employee.id;
                    }

                    // Hide search box and show selected employee
                    const entryRow = document.getElementById(`entry-${currentEntry}`);
                    if (entryRow) {
                        const searchInput = entryRow.querySelector('.employee-search');
                        const selectedDiv = document.getElementById(`selected-${currentEntry}`);
                        const selectedNameSpan = document.getElementById(`selected-name-${currentEntry}`);

                        if (searchInput && selectedDiv && selectedNameSpan) {
                            searchInput.style.display = 'none';
                            selectedDiv.style.display = 'flex';
                            selectedNameSpan.innerHTML = `${employee.employee_number} - <strong>${employee.name}</strong> <span style="color: var(--text-muted);">(${employee.department || 'N/A'} • ${employee.employee_status || 'N/A'})</span>`;
                        }

                        // Auto-check breakfast, lunch, dinner checkboxes by value
                        const checkboxes = entryRow.querySelectorAll('input[type="checkbox"]');
                        checkboxes.forEach(checkbox => {
                            if (checkbox.value === 'breakfast' || checkbox.value === 'lunch' || checkbox.value === 'dinner') {
                                checkbox.checked = true;
                            }
                        });
                    }
                }, 10);
            });

            setTimeout(() => {
                alert(`Loaded ${groupEmployees.length} employees from group "${selectedOption.text}"`);
                selector.value = ''; // Reset selector
            }, 100);
        }

        // Groups Modal Functions
        let currentEditingGroupId = null;
        let allGroups = [];

        async function openGroupsModal() {
            document.getElementById('groupsModal').style.display = 'flex';
            await loadGroups();
        }

        function closeGroupsModal() {
            document.getElementById('groupsModal').style.display = 'none';
            cancelGroupEdit();
        }

        async function loadGroups() {
            try {
                const response = await fetch('{{ route("groups.index") }}');
                allGroups = await response.json();
                renderGroupsList();
            } catch (error) {
                console.error('Error loading groups:', error);
                alert('Failed to load groups');
            }
        }

        function renderGroupsList() {
            const groupsList = document.getElementById('groupsList');
            if (allGroups.length === 0) {
                groupsList.innerHTML = '<p style="text-align: center; color: var(--text-muted); padding: 2rem;">No groups created yet</p>';
                return;
            }

            groupsList.innerHTML = allGroups.map(group => `
                                                <div style="padding: 1rem; border: 1px solid var(--card-border); border-radius: 8px; margin-bottom: 0.75rem; background: rgba(255,255,255,0.02);">
                                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                                        <div>
                                                            <strong style="color: var(--primary);">${group.name}</strong>
                                                            <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0.25rem 0 0 0;">
                                                                ${group.employees.length} employees
                                                            </p>
                                                        </div>
                                                        <div style="display: flex; gap: 0.5rem;">
                                                            <button class="btn btn-secondary btn-sm" onclick="editGroup(${group.id})">
                                                                <i class="bi bi-pencil"></i> Edit
                                                            </button>
                                                            <button class="btn btn-danger btn-sm" onclick="deleteGroup(${group.id}, '${group.name}')">
                                                                <i class="bi bi-trash"></i> Delete
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            `).join('');
        }

        async function saveGroup() {
            const groupName = document.getElementById('groupName').value.trim();
            const selectedEmployees = Array.from(document.querySelectorAll('.employee-checkbox:checked')).map(cb => cb.value);

            if (!groupName) {
                alert('Please enter a group name');
                return;
            }

            if (selectedEmployees.length === 0) {
                alert('Please select at least one employee');
                return;
            }

            try {
                const url = currentEditingGroupId
                    ? `{{ url('/groups') }}/${currentEditingGroupId}`
                    : '{{ route("groups.store") }}';

                const method = currentEditingGroupId ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: groupName,
                        employee_ids: selectedEmployees
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    cancelGroupEdit();
                    await loadGroups();
                    // Refresh group selector dropdown
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to save group'));
                }
            } catch (error) {
                console.error('Error saving group:', error);
                alert('Failed to save group');
            }
        }

        function editGroup(groupId) {
            currentEditingGroupId = groupId;
            const group = allGroups.find(g => g.id === groupId);

            if (!group) return;

            document.getElementById('groupName').value = group.name;
            document.getElementById('saveButtonText').textContent = 'Update Group';
            document.getElementById('cancelButton').style.display = 'inline-block';

            // Check employees in this group
            document.querySelectorAll('.employee-checkbox').forEach(cb => {
                cb.checked = group.employees.some(emp => emp.id == cb.value);
            });

            // Scroll to top of modal
            document.querySelector('.modal-content').scrollTop = 0;
        }

        async function deleteGroup(groupId, groupName) {
            if (!confirm(`Are you sure you want to delete group "${groupName}"?`)) return;

            try {
                const response = await fetch(`{{ url('/groups') }}/${groupId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    await loadGroups();
                    // Refresh group selector dropdown
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to delete group'));
                }
            } catch (error) {
                console.error('Error deleting group:', error);
                alert('Failed to delete group');
            }
        }

        function cancelGroupEdit() {
            currentEditingGroupId = null;
            document.getElementById('groupName').value = '';
            document.getElementById('saveButtonText').textContent = 'Create Group';
            document.getElementById('cancelButton').style.display = 'none';
            deselectAllEmployees();
            updateSelectedCount();
        }

        function selectAllEmployees() {
            document.querySelectorAll('.employee-checkbox-label').forEach(label => {
                if (label.style.display !== 'none') {
                    label.querySelector('.employee-checkbox').checked = true;
                }
            });
            updateSelectedCount();
        }

        function deselectAllEmployees() {
            document.querySelectorAll('.employee-checkbox').forEach(cb => cb.checked = false);
            updateSelectedCount();
        }

        function updateSelectedCount() {
            const count = document.querySelectorAll('.employee-checkbox:checked').length;
            document.getElementById('selectedCount').textContent = count + ' selected';
        }

        function filterEmployees() {
            const searchTerm = document.getElementById('searchEmployeeInput').value.toLowerCase();
            document.querySelectorAll('.employee-checkbox-label').forEach(label => {
                const name = label.getAttribute('data-name') || '';
                const number = label.getAttribute('data-number') || '';
                if (name.includes(searchTerm) || number.includes(searchTerm)) {
                    label.style.display = 'block';
                } else {
                    label.style.display = 'none';
                }
            });
        }

        function filterGroups() {
            const searchTerm = document.getElementById('searchGroupInput').value.toLowerCase();
            document.querySelectorAll('#groupsList > div').forEach(groupItem => {
                const groupName = groupItem.textContent.toLowerCase();
                if (groupName.includes(searchTerm)) {
                    groupItem.style.display = 'block';
                } else {
                    groupItem.style.display = 'none';
                }
            });
        }
    </script>
@endpush