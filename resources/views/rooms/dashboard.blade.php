@extends('layouts.app')

@section('content')
    <div class="page-header d-flex justify-between align-items-center">
        <div>
            <h1 class="page-title">POB KAMAR {{ strtoupper($location) }}</h1>
            <p class="page-subtitle">TANGGAL: {{ \Carbon\Carbon::parse($date)->format('d F Y') }}</p>
        </div>
        <div class="d-flex gap-1 align-items-center">
            <form action="{{ route('rooms.dashboard') }}" method="GET" class="d-flex gap-1 align-items-center">
                <select name="location" class="form-control" style="width: auto;" onchange="this.form.submit()">
                    @foreach($locations as $loc)
                        <option value="{{ $loc }}" {{ $location == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                    @endforeach
                </select>
                <input type="date" name="date" class="form-control" value="{{ $date }}" onchange="this.form.submit()">
            </form>
            <a href="{{ route('rooms.export', ['location' => $location, 'date' => $date]) }}"
                class="btn btn-primary btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
            <a href="{{ route('rooms.manage', ['location' => $location]) }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-gear"></i> Manage Rooms
            </a>
        </div>
    </div>

    {{-- Group Filter Checkboxes --}}
    <div class="group-filter-container mb-2">
        <span class="filter-label">Show Groups:</span>
        @foreach($groupNames as $index => $name)
            <label class="group-filter-item">
                <input type="checkbox" class="group-filter-checkbox" data-group-index="{{ $index }}" checked
                    onchange="toggleGroupVisibility()">
                <span>{{ $name }}</span>
            </label>
        @endforeach
    </div>

    @if(count($columns) > 0 && !empty($columns[0]))
        <div class="card" style="overflow: hidden;">
            <div style="overflow-x: auto;">
                <table class="room-table">
                    <thead>
                        <tr>
                            @for($i = 0; $i < count($columns); $i++)
                                <th class="room-header room-group-name group-col group-{{ $i }}" colspan="2">
                                    {{ $groupNames[$i] ?? 'GROUP' }}
                                </th>
                                <th class="room-header room-header-status group-col group-{{ $i }}">STS</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Totals row right after header --}}
                        <tr class="totals-row">
                            @for($col = 0; $col < count($columns); $col++)
                                <td class="room-footer group-col group-{{ $col }}" colspan="2">
                                    <div class="footer-counts">
                                        <span class="count-on">{{ $totals[$col]['on'] ?? 0 }} ON</span>
                                        <span class="count-off">{{ $totals[$col]['off'] ?? 0 }} OFF</span>
                                        <span class="count-vac">{{ $totals[$col]['vacant'] ?? 0 }} VAC</span>
                                    </div>
                                </td>
                                <td class="room-footer room-footer-sts group-col group-{{ $col }}"></td>
                            @endfor
                        </tr>
                        @php
                            $maxRows = max(array_map('count', $columns));
                            if ($maxRows < 5)
                                $maxRows = 5;
                        @endphp

                        @for($row = 0; $row < $maxRows; $row++)
                            <tr>
                                @for($col = 0; $col < count($columns); $col++)
                                    @php
                                        $cell = $columns[$col][$row] ?? null;
                                    @endphp
                                    @if($cell)
                                        @php
                                            $isVacant = $cell['is_vacant'] ?? (strtoupper($cell['name']) === 'VACANT');
                                            $isOn = $cell['status'] === 'ON';
                                            $isNonFillable = $cell['is_non_fillable'] ?? false;
                                            $isOverride = $cell['is_override'] ?? false;
                                            $employeeId = $cell['employee_id'] ?? null;
                                            $capacity = $cell['capacity'] ?? 1;
                                            $slot = $cell['slot'] ?? 0;
                                        @endphp
                                        @if($isNonFillable)
                                            <td class="room-cell room-cell-code room-non-fillable group-col group-{{ $col }}">
                                                {{ $cell['room'] }}
                                            </td>
                                            <td class="room-cell room-cell-name room-non-fillable group-col group-{{ $col }}" colspan="1">
                                                <i class="bi bi-lock" style="margin-right: 4px;"></i>{{ $cell['name'] }}
                                            </td>
                                            <td class="room-cell room-cell-status room-non-fillable group-col group-{{ $col }}">
                                                -
                                            </td>
                                        @else
                                            @if($slot === 0)
                                                <td class="room-cell room-cell-code group-col group-{{ $col }} {{ $isVacant ? 'room-vacant' : '' }}"
                                                    data-is-vacant="{{ $isVacant ? '1' : '0' }}" @if($capacity > 1) rowspan="{{ $capacity }}"
                                                    style="vertical-align: middle;" @endif>
                                                    {{ $cell['room'] }}
                                                </td>
                                            @endif
                                            <td
                                                class="room-cell room-cell-name group-col group-{{ $col }} {{ $isVacant ? 'room-vacant' : '' }} {{ $isOverride ? 'is-override' : '' }}">
                                                <div class="employee-info">
                                                    <span class="employee-name">
                                                        {{ $cell['name'] }}
                                                        @if($isOverride)
                                                            <span class="override-badge" title="Manually edited">✎</span>
                                                        @endif
                                                    </span>
                                                    @if(!$isVacant && isset($cell['department']))
                                                        <span class="employee-detail">{{ $cell['department'] }} |
                                                            {{ $cell['emp_status'] ?? 'Active' }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            @if($employeeId && !$isVacant)
                                                <td class="room-cell room-cell-status status-clickable group-col group-{{ $col }} {{ $isOn ? 'status-on' : 'status-off' }} {{ $isOverride ? 'is-override' : '' }}"
                                                    data-employee-id="{{ $employeeId }}" data-current-status="{{ $cell['status'] }}"
                                                    data-status="{{ $cell['status'] }}" onclick="toggleStatus(this)">
                                                    {{ $cell['status'] }}
                                                    @if($isOverride)
                                                        <span class="override-dot"></span>
                                                    @endif
                                                </td>
                                            @else
                                                <td class="room-cell room-cell-status group-col group-{{ $col }} {{ $isOn ? 'status-on' : 'status-off' }}"
                                                    data-status="{{ $cell['status'] }}" data-is-vacant="{{ $isVacant ? '1' : '0' }}">
                                                    {{ $cell['status'] }}
                                                </td>
                                            @endif
                                        @endif
                                    @else
                                        <td class="room-cell room-cell-empty group-col group-{{ $col }}"></td>
                                        <td class="room-cell room-cell-empty group-col group-{{ $col }}"></td>
                                        <td class="room-cell room-cell-empty group-col group-{{ $col }}"></td>
                                    @endif
                                @endfor
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row" style="margin-top: 1.5rem;">
            @php
                $totalOn = array_sum(array_column($totals, 'on'));
                $totalOff = array_sum(array_column($totals, 'off'));
                $totalVacant = array_sum(array_column($totals, 'vacant'));
            @endphp
            <div class="col-4">
                <div class="card summary-card summary-on">
                    <div class="summary-value" id="summary-on">{{ $totalOn }}</div>
                    <div class="summary-label">Total ON</div>
                </div>
            </div>
            <div class="col-4">
                <div class="card summary-card summary-off">
                    <div class="summary-value" id="summary-off">{{ $totalOff }}</div>
                    <div class="summary-label">Total OFF</div>
                </div>
            </div>
            <div class="col-4">
                <div class="card summary-card summary-vacant">
                    <div class="summary-value" id="summary-vacant">{{ $totalVacant }}</div>
                    <div class="summary-label">Total Vacant</div>
                </div>
            </div>
        </div>
    @else
        <div class="card" style="padding: 3rem; text-align: center;">
            <i class="bi bi-building" style="font-size: 4rem; color: var(--text-muted); opacity: 0.5;"></i>
            <h3 style="margin-top: 1rem; color: var(--text-secondary);">No Room Groups Defined</h3>
            <p style="color: var(--text-muted);">Set up room groups to display the POB dashboard for {{ $location }}.</p>
            <a href="{{ route('rooms.manage', ['location' => $location]) }}" class="btn btn-primary" style="margin-top: 1rem;">
                <i class="bi bi-plus-lg"></i> Setup Room Groups
            </a>
        </div>
    @endif

    <style>
        /* Group Filter */
        .group-filter-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            padding: 0.75rem 1rem;
            background: rgba(50, 50, 50, 0.5);
            border-radius: 8px;
        }

        .filter-label {
            color: var(--text-muted);
            font-weight: 600;
        }

        .group-filter-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            cursor: pointer;
            color: #fff;
            padding: 0.3rem 0.6rem;
            background: rgba(255, 165, 0, 0.2);
            border-radius: 4px;
            border: 1px solid rgba(255, 165, 0, 0.3);
        }

        .group-filter-item:hover {
            background: rgba(255, 165, 0, 0.3);
        }

        .group-filter-checkbox {
            accent-color: #FF6600;
        }

        .room-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        .room-header {
            background: #3d1a0a;
            color: #d4a84b;
            padding: 0.8rem 0.5rem;
            text-align: center;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.75rem;
        }

        .room-group-name {
            background: #3d1a0a;
            color: #d4a84b;
            font-size: 0.8rem;
            font-weight: 700;
            padding: 0.8rem 0.6rem;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .room-header-status {
            width: 50px;
            font-size: 0.7rem;
            background: #3d1a0a;
            color: #d4a84b;
        }

        .room-cell {
            padding: 0.6rem 0.5rem;
            background: transparent;
            border-bottom: 1px solid rgba(100, 70, 40, 0.3);
        }

        .room-cell-code {
            text-align: center;
            font-weight: 600;
            color: #ccc;
            width: 45px;
            font-size: 0.85rem;
        }

        .room-cell-name {
            color: #ddd;
        }

        .employee-info {
            display: flex;
            flex-direction: column;
            line-height: 1.3;
        }

        .employee-name {
            font-weight: 600;
            color: #fff;
        }

        .employee-detail {
            font-size: 0.7rem;
            color: #999;
            font-weight: normal;
        }

        .room-cell-status {
            text-align: center;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .room-cell-empty {
            background: transparent;
        }

        .room-vacant {
            background: transparent !important;
        }

        .room-vacant .employee-name,
        td.room-vacant {
            color: #888;
            font-style: italic;
        }

        .status-on {
            color: #4CAF50;
        }

        .status-off {
            color: #f44336;
        }

        .room-non-fillable {
            background: transparent !important;
            color: #666 !important;
            font-style: italic;
        }

        .room-footer,
        .totals-row td {
            background: #3d1a0a;
            color: #d4a84b;
            padding: 0.6rem 0.5rem;
            text-align: center;
            font-weight: 600;
            font-size: 0.75rem;
            border-bottom: none;
        }

        .room-footer-sts {
            width: 50px;
        }

        .footer-counts {
            display: flex;
            justify-content: center;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .footer-counts .count-on {
            color: #4CAF50;
            font-weight: 700;
        }

        .footer-counts .count-off {
            color: #f44336;
            font-weight: 700;
        }

        .footer-counts .count-vac {
            color: #888;
            font-weight: 700;
        }

        /* Summary Cards */
        .summary-card {
            padding: 1.5rem;
            text-align: center;
            border-radius: 12px;
        }

        .summary-value {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
        }

        .summary-label {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-top: 0.5rem;
        }

        .summary-on {
            background: linear-gradient(135deg, rgba(0, 255, 136, 0.1), rgba(0, 255, 136, 0.05));
            border: 1px solid rgba(0, 255, 136, 0.3);
        }

        .summary-on .summary-value {
            color: #00FF88;
        }

        .summary-off {
            background: linear-gradient(135deg, rgba(255, 68, 68, 0.1), rgba(255, 68, 68, 0.05));
            border: 1px solid rgba(255, 68, 68, 0.3);
        }

        .summary-off .summary-value {
            color: #FF4444;
        }

        .summary-total {
            background: linear-gradient(135deg, rgba(255, 165, 0, 0.1), rgba(255, 165, 0, 0.05));
            border: 1px solid rgba(255, 165, 0, 0.3);
        }

        .summary-total .summary-value {
            color: var(--accent);
        }

        .summary-vacant {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.15), rgba(255, 215, 0, 0.05));
            border: 1px solid rgba(255, 215, 0, 0.3);
        }

        .summary-vacant .summary-value {
            color: #FFD700;
        }

        /* Hover effects */
        .room-table tbody tr:hover .room-cell:not(.room-cell-empty) {
            background: rgba(255, 165, 0, 0.1);
        }

        /* Override / Edit indicators */
        .status-clickable {
            cursor: pointer;
            position: relative;
        }

        .status-clickable:hover {
            filter: brightness(1.2);
            box-shadow: 0 0 8px rgba(255, 165, 0, 0.5);
        }

        .is-override {
            position: relative;
        }

        .is-override::before {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 6px;
            height: 6px;
            background: #FFD700;
            border-radius: 50%;
        }

        .override-badge {
            font-size: 0.7rem;
            color: #FFD700;
            margin-left: 4px;
        }

        .override-dot {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 6px;
            height: 6px;
            background: #FFD700;
            border-radius: 50%;
        }
    </style>

    <script>
        function toggleStatus(cell) {
            const employeeId = cell.dataset.employeeId;
            const currentStatus = cell.dataset.currentStatus;
            const newStatus = currentStatus === 'ON' ? 'OFF' : 'ON';
            
            // Show loading
            cell.style.opacity = '0.5';
            
            fetch('{{ route("rooms.toggleOverride") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    employee_id: employeeId,
                    location: '{{ $location }}',
                    date: '{{ $date }}',
                    status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Failed to update status');
                    cell.style.opacity = '1';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to update status');
                cell.style.opacity = '1';
            });
        }

        function toggleGroupVisibility() {
            const checkboxes = document.querySelectorAll('.group-filter-checkbox');
            let totalOn = 0;
            let totalOff = 0;
            let totalVacant = 0;

            checkboxes.forEach(checkbox => {
                const groupIndex = checkbox.dataset.groupIndex;
                const isChecked = checkbox.checked;
                const groupCols = document.querySelectorAll('.group-' + groupIndex);
                
                groupCols.forEach(col => {
                    col.style.display = isChecked ? '' : 'none';
                });

                // Count totals for visible groups
                if (isChecked) {
                    const statusCells = document.querySelectorAll('.group-' + groupIndex + '.room-cell-status');
                    statusCells.forEach(cell => {
                        const status = cell.dataset.status;
                        const isVacant = cell.dataset.isVacant === '1';
                        if (isVacant) {
                            totalVacant++;
                        } else if (status === 'ON') {
                            totalOn++;
                        } else if (status === 'OFF') {
                            totalOff++;
                        }
                    });
                }
            });

            // Update summary
            document.getElementById('summary-on').textContent = totalOn;
            document.getElementById('summary-off').textContent = totalOff;
            document.getElementById('summary-vacant').textContent = totalVacant;
        }
    </script>
@endsection