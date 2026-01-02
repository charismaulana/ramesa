<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Meal Recap - {{ $location }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #000;
            margin: 50px;
        }
        h1 {
            font-size: 14px;
            text-align: center;
            margin-bottom: 5px;
        }
        .header {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .info {
            font-size: 12px;
            margin-bottom: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .status-header {
            background-color: #d3d3d3;
            font-weight: bold;
            text-align: center;
        }
        .total-row {
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            font-size: 10px;
        }
        .signature {
            display: inline-block;
            width: 20%;
            vertical-align: top;
        }
        .signature-name {
            margin-top: 40px;
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>TOTAL MEAL</h1>
    
    <div class="header">Provider : {{ $companyHeader }}</div>
    <div class="info">Location : {{ $location }}</div>
    <div class="info">{{ $dateLabel }}</div>

    @foreach($sortedGrouped as $status => $statusDepartments)
        <table>
            <thead>
                <tr>
                    <th colspan="6" class="status-header">{{ strtoupper($status) }}</th>
                </tr>
                <tr>
                    <th rowspan="2" style="vertical-align: middle;">Department</th>
                    <th>Breakfast</th>
                    <th>Lunch</th>
                    <th>Dinner</th>
                    <th>Supper</th>
                    <th>Snack</th>
                </tr>
                <tr>
                    <th>Rp {{ number_format($prices['breakfast'], 0, ',', '.') }}</th>
                    <th>Rp {{ number_format($prices['lunch'], 0, ',', '.') }}</th>
                    <th>Rp {{ number_format($prices['dinner'], 0, ',', '.') }}</th>
                    <th>Rp {{ number_format($prices['supper'], 0, ',', '.') }}</th>
                    <th>Rp {{ number_format($prices['snack'], 0, ',', '.') }}</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $statusTotals = [
                        'breakfast_count' => 0,
                        'lunch_count' => 0,
                        'dinner_count' => 0,
                        'supper_count' => 0,
                        'snack_count' => 0,
                        'breakfast_price' => 0,
                        'lunch_price' => 0,
                        'dinner_price' => 0,
                        'supper_price' => 0,
                        'snack_price' => 0,
                    ];
                @endphp

                @foreach($statusDepartments as $dept => $deptAttendances)
                    @php
                        $counts = [
                            'breakfast' => $deptAttendances->where('meal_type', 'breakfast')->count(),
                            'lunch' => $deptAttendances->where('meal_type', 'lunch')->count(),
                            'dinner' => $deptAttendances->where('meal_type', 'dinner')->count(),
                            'supper' => $deptAttendances->where('meal_type', 'supper')->count(),
                            'snack' => $deptAttendances->where('meal_type', 'snack')->count(),
                        ];

                        if (array_sum($counts) === 0) continue;

                        $statusTotals['breakfast_count'] += $counts['breakfast'];
                        $statusTotals['lunch_count'] += $counts['lunch'];
                        $statusTotals['dinner_count'] += $counts['dinner'];
                        $statusTotals['supper_count'] += $counts['supper'];
                        $statusTotals['snack_count'] += $counts['snack'];
                        $statusTotals['breakfast_price'] += $counts['breakfast'] * $prices['breakfast'];
                        $statusTotals['lunch_price'] += $counts['lunch'] * $prices['lunch'];
                        $statusTotals['dinner_price'] += $counts['dinner'] * $prices['dinner'];
                        $statusTotals['supper_price'] += $counts['supper'] * $prices['supper'];
                        $statusTotals['snack_price'] += $counts['snack'] * $prices['snack'];
                    @endphp

                    <tr>
                        <td>{{ $dept }}</td>
                        <td>{{ $counts['breakfast'] ?: '-' }}</td>
                        <td>{{ $counts['lunch'] ?: '-' }}</td>
                        <td>{{ $counts['dinner'] ?: '-' }}</td>
                        <td>{{ $counts['supper'] ?: '-' }}</td>
                        <td>{{ $counts['snack'] ?: '-' }}</td>
                    </tr>
                @endforeach

                <tr class="total-row">
                    <td>Total Person</td>
                    <td>{{ $statusTotals['breakfast_count'] ?: '-' }}</td>
                    <td>{{ $statusTotals['lunch_count'] ?: '-' }}</td>
                    <td>{{ $statusTotals['dinner_count'] ?: '-' }}</td>
                    <td>{{ $statusTotals['supper_count'] ?: '-' }}</td>
                    <td>{{ $statusTotals['snack_count'] ?: '-' }}</td>
                </tr>
                <tr class="total-row">
                    <td>Total Price</td>
                    <td>{{ $statusTotals['breakfast_price'] ? 'Rp ' . number_format($statusTotals['breakfast_price'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $statusTotals['lunch_price'] ? 'Rp ' . number_format($statusTotals['lunch_price'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $statusTotals['dinner_price'] ? 'Rp ' . number_format($statusTotals['dinner_price'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $statusTotals['supper_price'] ? 'Rp ' . number_format($statusTotals['supper_price'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $statusTotals['snack_price'] ? 'Rp ' . number_format($statusTotals['snack_price'], 0, ',', '.') : '-' }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach

    <div class="footer">
        <div class="signature">
            <div>Prepared By:</div>
            <div class="signature-name">{{ $preparedBy }}</div>
            <div>{{ $preparedPosition }}</div>
        </div>
        <div class="signature" style="float: right; text-align: left;">
            <div>Checked By:</div>
            <div class="signature-name">{{ $checkedBy }}</div>
            <div>{{ $checkedPosition }}</div>
        </div>
    </div>
</body>
</html>
