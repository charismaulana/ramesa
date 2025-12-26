@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h1 class="page-title">MANUAL ENTRY</h1>
        <p class="page-subtitle">Record meal attendance for delivered meals</p>
    </div>

    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Record Attendance</h2>
                </div>

                <form action="{{ route('scan.storeManual') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label class="form-label" for="employee_search">Employee</label>
                        <input type="text" id="employee_search" class="form-control"
                            placeholder="Type employee name or ID..." autocomplete="off">
                        <input type="hidden" name="employee_id" id="employee_id" required>
                        <div id="employee-suggestions" class="employee-suggestions"></div>
                        <div id="selected-employee" class="selected-employee" style="display: none;">
                            <span id="selected-employee-text"></span>
                            <button type="button" onclick="clearEmployee()" class="clear-btn">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="meal_type">Meal Type</label>
                        <select name="meal_type" id="meal_type" class="form-control" required>
                            <option value="breakfast" {{ old('meal_type') == 'breakfast' ? 'selected' : '' }}>Breakfast
                            </option>
                            <option value="lunch" {{ old('meal_type') == 'lunch' ? 'selected' : '' }}>Lunch</option>
                            <option value="dinner" {{ old('meal_type') == 'dinner' ? 'selected' : '' }}>Dinner</option>
                            <option value="supper" {{ old('meal_type') == 'supper' ? 'selected' : '' }}>Supper</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="recorded_by">Recorded By</label>
                        <input type="text" name="recorded_by" id="recorded_by" class="form-control"
                            value="{{ old('recorded_by') }}" placeholder="Catering staff name..." required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="scanned_at">Date & Time (Optional)</label>
                        <input type="datetime-local" name="scanned_at" id="scanned_at" class="form-control"
                            value="{{ old('scanned_at') }}">
                        <p style="color: var(--text-muted); margin-top: 0.5rem; font-size: 0.875rem;">
                            Leave empty to use current time
                        </p>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="location">Meal Location *</label>
                        <select name="location" id="location" class="form-control" required>
                            <option value="Ramba" {{ old('location') == 'Ramba' ? 'selected' : '' }}>Ramba</option>
                            <option value="Bentayan" {{ old('location') == 'Bentayan' ? 'selected' : '' }}>Bentayan</option>
                            <option value="Mangunjaya" {{ old('location') == 'Mangunjaya' ? 'selected' : '' }}>Mangunjaya
                            </option>
                            <option value="Keluang" {{ old('location') == 'Keluang' ? 'selected' : '' }}>Keluang</option>
                        </select>
                    </div>

                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i>
                            Record Attendance
                        </button>
                        <a href="{{ route('scan.index') }}" class="btn btn-secondary">
                            <i class="bi bi-qr-code-scan"></i>
                            Go to Scan Station
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Quick Reference</h2>
                </div>

                <div style="color: var(--text-secondary);">
                    <h4 style="color: var(--accent); margin-bottom: 1rem;">When to use Manual Entry:</h4>
                    <ul style="padding-left: 1.5rem; line-height: 1.8;">
                        <li>When meals are delivered to employee locations</li>
                        <li>When QR scanner is not available</li>
                        <li>For backdated attendance records</li>
                        <li>For employees who forgot to scan</li>
                    </ul>

                    <h4 style="color: var(--accent); margin: 1.5rem 0 1rem;">Meal Times:</h4>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                        <div
                            style="padding: 1rem; background: var(--card-bg); border-radius: 8px; border: 1px solid var(--card-border);">
                            <i class="bi bi-sunrise" style="color: var(--accent);"></i>
                            <strong>Breakfast</strong>
                            <p style="margin: 0; font-size: 0.875rem;">05:00 - 09:00</p>
                        </div>
                        <div
                            style="padding: 1rem; background: var(--card-bg); border-radius: 8px; border: 1px solid var(--card-border);">
                            <i class="bi bi-sun" style="color: var(--secondary);"></i>
                            <strong>Lunch</strong>
                            <p style="margin: 0; font-size: 0.875rem;">11:00 - 14:00</p>
                        </div>
                        <div
                            style="padding: 1rem; background: var(--card-bg); border-radius: 8px; border: 1px solid var(--card-border);">
                            <i class="bi bi-sunset" style="color: var(--primary);"></i>
                            <strong>Dinner</strong>
                            <p style="margin: 0; font-size: 0.875rem;">17:00 - 20:00</p>
                        </div>
                        <div
                            style="padding: 1rem; background: var(--card-bg); border-radius: 8px; border: 1px solid var(--card-border);">
                            <i class="bi bi-moon-stars" style="color: var(--primary-light);"></i>
                            <strong>Supper</strong>
                            <p style="margin: 0; font-size: 0.875rem;">21:00 - 03:00</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .employee-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #1a0a0a;
            border: 2px solid var(--primary);
            border-radius: 8px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 9999;
            display: none;
            margin-top: 4px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.8);
        }

        .employee-suggestions.show {
            display: block;
        }

        .suggestion-item {
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid var(--card-border);
            transition: background 0.2s;
        }

        .suggestion-item:hover,
        .suggestion-item.active {
            background: rgba(255, 69, 0, 0.15);
        }

        .suggestion-item:last-child {
            border-bottom: none;
        }

        .suggestion-item .emp-number {
            font-family: 'Orbitron', monospace;
            color: var(--accent);
            font-size: 0.9rem;
        }

        .suggestion-item .emp-name {
            color: var(--text-primary);
            font-weight: 500;
        }

        .suggestion-item .emp-dept {
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .selected-employee {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid var(--success);
            border-radius: 8px;
            margin-top: 0.5rem;
            color: var(--success);
        }

        .clear-btn {
            background: none;
            border: none;
            color: var(--error);
            cursor: pointer;
            font-size: 1.2rem;
            padding: 0;
            line-height: 1;
        }

        .clear-btn:hover {
            color: #ff6666;
        }

        .form-group {
            position: relative;
        }
    </style>
@endpush

@push('scripts')
    <script>
        const employees = @json($employees);
        const searchInput = document.getElementById('employee_search');
        const suggestionsBox = document.getElementById('employee-suggestions');
        const employeeIdInput = document.getElementById('employee_id');
        const selectedEmployeeDiv = document.getElementById('selected-employee');
        const selectedEmployeeText = document.getElementById('selected-employee-text');

        let selectedIndex = -1;
        let filteredEmployees = [];

        // Auto-select meal based on time
        document.addEventListener('DOMContentLoaded', function () {
            const hour = new Date().getHours();
            let autoMeal = 'lunch';
            // Breakfast: 05:00 - 09:00
            if (hour >= 5 && hour < 10) autoMeal = 'breakfast';
            // Lunch: 11:00 - 15:00
            else if (hour >= 11 && hour < 15) autoMeal = 'lunch';
            // Dinner: 17:00 - 21:00
            else if (hour >= 17 && hour < 21) autoMeal = 'dinner';
            // Supper: 21:00 - 03:00
            else if (hour >= 21 || hour < 3) autoMeal = 'supper';

            document.getElementById('meal_type').value = autoMeal;
        });

        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            selectedIndex = -1;

            if (query.length < 1) {
                hideSuggestions();
                return;
            }

            filteredEmployees = employees.filter(emp =>
                emp.name.toLowerCase().includes(query) ||
                emp.employee_number.toLowerCase().includes(query)
            ).slice(0, 10); // Limit to 10 results

            if (filteredEmployees.length > 0) {
                showSuggestions();
            } else {
                suggestionsBox.innerHTML = '<div class="suggestion-item" style="color: var(--text-muted);">No employees found</div>';
                suggestionsBox.classList.add('show');
            }
        });

        searchInput.addEventListener('keydown', function (e) {
            const items = suggestionsBox.querySelectorAll('.suggestion-item[data-id]');

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                updateActiveItem(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex = Math.max(selectedIndex - 1, 0);
                updateActiveItem(items);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (selectedIndex >= 0 && items[selectedIndex]) {
                    selectEmployee(filteredEmployees[selectedIndex]);
                }
            } else if (e.key === 'Escape') {
                hideSuggestions();
            }
        });

        function updateActiveItem(items) {
            items.forEach((item, i) => {
                item.classList.toggle('active', i === selectedIndex);
            });
        }

        function showSuggestions() {
            suggestionsBox.innerHTML = filteredEmployees.map((emp, index) => `
                                        <div class="suggestion-item" data-id="${emp.id}" onclick="selectEmployee(employees.find(e => e.id == ${emp.id}))">
                                            <div class="emp-number">${emp.employee_number}</div>
                                            <div class="emp-name">${emp.name}</div>
                                            <div class="emp-dept">${emp.department || ''} • ${emp.employee_status || ''}</div>
                                        </div>
                                    `).join('');
            suggestionsBox.classList.add('show');
        }

        function hideSuggestions() {
            suggestionsBox.classList.remove('show');
            filteredEmployees = [];
        }

        function selectEmployee(emp) {
            employeeIdInput.value = emp.id;
            searchInput.value = '';
            searchInput.style.display = 'none';

            let displayText = `<strong>${emp.employee_number}</strong> - ${emp.name}`;
            if (emp.department) {
                displayText += ` <span style="color: var(--text-muted);">(${emp.department} • ${emp.employee_status || ''})</span>`;
            }
            selectedEmployeeText.innerHTML = displayText;
            selectedEmployeeDiv.style.display = 'flex';

            hideSuggestions();
        }

        function clearEmployee() {
            employeeIdInput.value = '';
            searchInput.value = '';
            searchInput.style.display = 'block';
            selectedEmployeeDiv.style.display = 'none';
            searchInput.focus();
        }

        // Hide suggestions when clicking outside
        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                hideSuggestions();
            }
        });
    </script>
@endpush