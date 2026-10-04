<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Annual Exam Duty Hours Calculation Report</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 10px;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0 0 3px 0;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header h2 {
            font-size: 12px;
            margin: 0 0 3px 0;
            color: #475569;
            font-weight: 600;
        }
        .header p {
            font-size: 9px;
            color: #64748b;
            margin: 0;
            font-style: italic;
        }

        table.matrix {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.matrix th {
            background-color: #1e3a8a;
            color: white;
            font-weight: bold;
            font-size: 9px;
            padding: 6px 4px;
            border: 1px solid #0f172a;
            text-align: center;
        }
        table.matrix td {
            padding: 4px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            font-size: 9px;
        }
        table.matrix tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .tot-hours-header {
            background-color: #2563eb !important;
        }
        .tot-oic-header {
            background-color: #d97706 !important;
        }
        .tot-sec-header {
            background-color: #dc2626 !important;
        }
        .tot-hours-col {
            background-color: #eff6ff;
            font-weight: bold;
            color: #1d4ed8;
            text-align: center;
        }
        .tot-oic-col {
            background-color: #fffbeb;
            font-weight: bold;
            color: #b45309;
            text-align: center;
        }
        .tot-sec-col {
            background-color: #fef2f2;
            font-weight: bold;
            color: #b91c1c;
            text-align: center;
        }
        .badge-sec {
            color: #dc2626;
            font-weight: bold;
            font-size: 8px;
        }
        .badge-oic {
            color: #d97706;
            font-weight: bold;
            font-size: 8px;
        }
        tfoot tr {
            background-color: #e2e8f0 !important;
            font-weight: bold;
        }
        tfoot td {
            border: 1px solid #94a3b8 !important;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Examination Duty Management Portal</h1>
        <h2>Annual Exam Duty Hours Calculation Report</h2>
        <p>
            Generated on: {{ now()->format('d M Y, h:i A') }}
            @if(!empty($filters['staff_type']))
                | Staff Category: {{ $filters['staff_type'] }}
            @endif
        </p>
    </div>


    <table class="matrix">
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th style="text-align: left; min-width: 150px;">Staff Name</th>
                <th style="width: 85px;">Type</th>
                <th style="width: 90px;" class="tot-hours-header">Total Regular Hours</th>
                <th style="width: 70px;" class="tot-oic-header">Total OIC</th>
                <th style="width: 70px;" class="tot-sec-header">Total SEC</th>
            </tr>
        </thead>
        <tbody>
            @forelse($matrix as $row)
                <tr>
                    <td style="text-align: center; color: #64748b;">{{ $loop->iteration }}</td>
                    <td style="font-weight: bold;">{{ $row['staff']->name }}</td>
                    <td style="text-align: center;">{{ $row['staff']->staff_type }}</td>
                    <td class="tot-hours-col" style="text-align: center;">{{ number_format($row['total_hours'], 1) }} hrs</td>
                    <td class="tot-oic-col" style="text-align: center;">{{ $row['staff']->staff_type == 'Teaching' ? $row['total_oic'] : '-' }}</td>
                    <td class="tot-sec-col" style="text-align: center;">{{ $row['staff']->staff_type == 'Teaching' ? $row['total_sec'] : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 15px;">No staff records found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right; text-transform: uppercase;">Total:</td>
                <td style="text-align: center; color: #1e3a8a;">{{ number_format($grandTotals['hours'], 1) }} hrs</td>
                <td style="text-align: center; color: #b45309;">{{ $grandTotals['oic'] }}</td>
                <td style="text-align: center; color: #b91c1c;">{{ $grandTotals['sec'] }}</td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
