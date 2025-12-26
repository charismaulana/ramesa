@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h1 class="page-title">BULK ATTENDANCE INPUT</h1>
        <p class="page-subtitle">Input multiple employee meals at once (max 50 entries)</p>
    </div>

    <form action="{{ route('bulk.store') }}" method="POST" id="bulk-form">
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
                <div class="d-flex gap-1">
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
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid rgba(0, 255, 136, 0.3);
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
    </style>
@endpush

@push('scripts')
    <script>
        const employees = @json($employees);
        let entryCount = 0;

        function addEntry() {
            if (entryCount >= 50) {
                alert('Maximum 50 entries allowed');
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
                `<strong>${number}</strong> - ${name}` + (department ? ` <span style="color: var(--text-muted);">(${department} • ${employeeStatus || ''})</span>` : '');
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
    </script>
@endpush