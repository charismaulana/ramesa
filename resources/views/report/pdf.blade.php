<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Tagihan Konsumsi Catering</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #0066cc;
            padding-bottom: 15px;
        }

        .header-title {
            font-size: 16px;
            font-weight: bold;
            color: #0066cc;
            margin-bottom: 5px;
        }

        .header-subtitle {
            font-size: 12px;
            color: #666;
        }

        .period {
            font-size: 11px;
            margin-top: 5px;
            color: #333;
        }

        .location-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .location-title {
            font-size: 13px;
            font-weight: bold;
            color: #0066cc;
            background: #e6f0ff;
            padding: 8px 12px;
            margin-bottom: 10px;
            border-left: 4px solid #0066cc;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 5px 8px;
            text-align: center;
        }

        th {
            background: #0066cc;
            color: white;
            font-weight: bold;
            font-size: 9px;
        }

        .status-header {
            background: #f0f0f0;
            font-weight: bold;
            text-align: left;
            color: #333;
        }

        .meal-header {
            background: #4a90d9;
        }

        .status-row td:first-child {
            text-align: left;
            font-weight: bold;
        }

        .total-row {
            background: #fff3cd;
            font-weight: bold;
        }

        .total-row td {
            color: #856404;
        }

        .grand-total-row {
            background: #0066cc;
            font-weight: bold;
        }

        .grand-total-row td {
            color: white;
        }

        .sub-total-row {
            background: #e9ecef;
            font-weight: bold;
        }

        .number {
            text-align: right;
        }

        .date-col {
            text-align: left;
            white-space: nowrap;
        }

        .footer {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            width: 200px;
            text-align: center;
        }

        .signature-box p {
            margin-bottom: 50px;
        }

        .signature-line {
            border-top: 1px solid #333;
            padding-top: 5px;
        }

        .page-break {
            page-break-before: always;
        }

        .company-header {
            float: right;
            text-align: right;
            font-size: 10px;
            color: #0066cc;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-header">
            <strong>PT PERTAMINA PEP</strong>
        </div>
        <div class="header-title">REKAPITULASI TAGIHAN KONSUMSI CATERING</div>
        <div class="header-subtitle">PT RAYYAN INDAH</div>
        <div class="period">
            Periode: {{ $dateFrom->format('d') }} - {{ $dateTo->format('d F Y') }}
        </div>
    </div>

    @if($reportType === 'summary')
        {{-- SUMMARY REPORT --}}
        @foreach($data as $location => $statuses)
            <div class="location-section">
                <div class="location-title">{{ $location }} CAMP</div>
                
                <table>
                    <thead>
                        <tr>
                            <th style="width: 25%">Status</th>
                            <th style="width: 12%">B'fast</th>
                            <th style="width: 12%">Lunch</th>
                            <th style="width: 12%">Dinner</th>
                            <th style="width: 12%">Supper</th>
                            <th style="width: 12%">Snack</th>
                            <th style="width: 15%">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $locationTotals = ['breakfast' => 0, 'lunch' => 0, 'dinner' => 0, 'supper' => 0, 'snack' => 0, 'total' => 0];
                        @endphp
                        
                        @foreach($statuses as $status => $meals)
                            <tr class="status-row">
                                <td>{{ $status }}</td>
                                <td class="number">{{ $meals['breakfast'] }}</td>
                                <td class="number">{{ $meals['lunch'] }}</td>
                                <td class="number">{{ $meals['dinner'] }}</td>
                                <td class="number">{{ $meals['supper'] }}</td>
                                <td class="number">{{ $meals['snack'] }}</td>
                                <td class="number"><strong>{{ $meals['total'] }}</strong></td>
                            </tr>
                            @php
                                foreach (['breakfast', 'lunch', 'dinner', 'supper', 'snack', 'total'] as $key) {
                                    $locationTotals[$key] += $meals[$key];
                                }
                            @endphp
                        @endforeach
                        
                        <tr class="total-row">
                            <td><strong>Sub Total</strong></td>
                            <td class="number"><strong>{{ $locationTotals['breakfast'] }}</strong></td>
                            <td class="number"><strong>{{ $locationTotals['lunch'] }}</strong></td>
                            <td class="number"><strong>{{ $locationTotals['dinner'] }}</strong></td>
                            <td class="number"><strong>{{ $locationTotals['supper'] }}</strong></td>
                            <td class="number"><strong>{{ $locationTotals['snack'] }}</strong></td>
                            <td class="number"><strong>{{ $locationTotals['total'] }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endforeach

        {{-- Grand Total --}}
        @php
            $grandTotals = ['breakfast' => 0, 'lunch' => 0, 'dinner' => 0, 'supper' => 0, 'snack' => 0, 'total' => 0];
            foreach ($data as $location => $statuses) {
                foreach ($statuses as $status => $meals) {
                    foreach (['breakfast', 'lunch', 'dinner', 'supper', 'snack', 'total'] as $key) {
                        $grandTotals[$key] += $meals[$key];
                    }
                }
            }
        @endphp
        
        <table>
            <tr class="grand-total-row">
                <td style="width: 25%; text-align: left;"><strong>TOTAL TAGIHAN</strong></td>
                <td style="width: 12%;" class="number"><strong>{{ $grandTotals['breakfast'] }}</strong></td>
                <td style="width: 12%;" class="number"><strong>{{ $grandTotals['lunch'] }}</strong></td>
                <td style="width: 12%;" class="number"><strong>{{ $grandTotals['dinner'] }}</strong></td>
                <td style="width: 12%;" class="number"><strong>{{ $grandTotals['supper'] }}</strong></td>
                <td style="width: 12%;" class="number"><strong>{{ $grandTotals['snack'] }}</strong></td>
                <td style="width: 15%;" class="number"><strong>{{ $grandTotals['total'] }}</strong></td>
            </tr>
        </table>

    @else
        {{-- DETAILED REPORT --}}
        @foreach($data as $location => $statuses)
            @foreach($statuses as $status => $statusData)
                <div class="location-section">
                    <div class="location-title">{{ $location }} CAMP - {{ $status }}</div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 15%">Tanggal</th>
                                <th style="width: 14%">B'fast</th>
                                <th style="width: 14%">Lunch</th>
                                <th style="width: 14%">Dinner</th>
                                <th style="width: 14%">Supper</th>
                                <th style="width: 14%">Snack</th>
                                <th style="width: 15%">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($statusData['daily'] as $day)
                                @php
                                    $dayTotal = $day['breakfast'] + $day['lunch'] + $day['dinner'] + $day['supper'] + $day['snack'];
                                @endphp
                                <tr>
                                    <td class="date-col">{{ $day['date']->format('d-M-y') }}</td>
                                    <td class="number">{{ $day['breakfast'] }}</td>
                                    <td class="number">{{ $day['lunch'] }}</td>
                                    <td class="number">{{ $day['dinner'] }}</td>
                                    <td class="number">{{ $day['supper'] }}</td>
                                    <td class="number">{{ $day['snack'] }}</td>
                                    <td class="number">{{ $dayTotal }}</td>
                                </tr>
                            @endforeach
                            
                            <tr class="total-row">
                                <td><strong>TOTAL</strong></td>
                                <td class="number"><strong>{{ $statusData['totals']['breakfast'] }}</strong></td>
                                <td class="number"><strong>{{ $statusData['totals']['lunch'] }}</strong></td>
                                <td class="number"><strong>{{ $statusData['totals']['dinner'] }}</strong></td>
                                <td class="number"><strong>{{ $statusData['totals']['supper'] }}</strong></td>
                                <td class="number"><strong>{{ $statusData['totals']['snack'] }}</strong></td>
                                <td class="number"><strong>{{ $statusData['totals']['total'] }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endforeach
        @endforeach
    @endif

    <div style="margin-top: 40px;">
        <table style="border: none; width: 100%;">
            <tr style="border: none;">
                <td style="border: none; width: 33%; text-align: center;">
                    <p>Prepared by,</p>
                    <br><br><br>
                    <p style="border-top: 1px solid #333; display: inline-block; padding-top: 5px;">Head Chef</p>
                </td>
                <td style="border: none; width: 33%; text-align: center;">
                    <p>Checked by,</p>
                    <br><br><br>
                    <p style="border-top: 1px solid #333; display: inline-block; padding-top: 5px;">Catering Supervisor</p>
                </td>
                <td style="border: none; width: 33%; text-align: center;">
                    <p>Approved by,</p>
                    <br><br><br>
                    <p style="border-top: 1px solid #333; display: inline-block; padding-top: 5px;">Asst Manager GS</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
