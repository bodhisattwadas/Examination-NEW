<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $duty->exam_name }} - Exam Duty Report</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.2cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 10px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0 0 4px 0;
            color: #1e3a8a;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 13px;
            margin: 0 0 4px 0;
            color: #334155;
            font-weight: 600;
        }
        .header p {
            font-size: 9px;
            color: #64748b;
            margin: 0;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.data-table th {
            background-color: #1e3a8a;
            color: white;
            font-weight: bold;
            font-size: 9px;
            padding: 7px 5px;
            border: 1px solid #0f172a;
            text-align: center;
        }
        table.data-table td {
            padding: 5px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            font-size: 9px;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge-sec {
            color: #dc2626;
            font-weight: bold;
        }
        .badge-oic {
            color: #d97706;
            font-weight: bold;
        }
        tfoot tr {
            background-color: #e2e8f0 !important;
            font-weight: bold;
        }
        tfoot td {
            border: 1px solid #94a3b8 !important;
        }
        .footer {
            margin-top: 20px;
            font-size: 8px;
            color: #94a3b8;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Examination Duty Management Portal</h1>
        <h2>Exam Duty Report: {{ $duty->exam_name }}</h2>
        <p>
            @if($duty->remarks)
                <strong>Remarks:</strong> {{ $duty->remarks }} &nbsp;|&nbsp;
            @endif
            <strong>Generated on:</strong> {{ now()->format('d M Y, h:i A') }}
        </p>
    </div>


    <!-- Staff Assignments Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th style="text-align: left; min-width: 170px;">Staff Name</th>
                <th style="width: 90px;">Category</th>
                <th style="width: 90px;">Duty Hours</th>
                <th style="width: 80px;">OIC Duties</th>
                <th style="width: 80px;">Secretary</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assignments as $a)
                @php $staff = $a->staff; @endphp
                <tr>
                    <td style="text-align: center; color: #64748b;">{{ $loop->iteration }}</td>
                    <td style="font-weight: bold;">{{ $staff ? $staff->name : 'Staff #' . $a->staff_id }}</td>
                    <td style="text-align: center;">{{ $staff ? $staff->staff_type : '—' }}</td>
                    <td style="text-align: center; font-weight: bold; color: #1e3a8a;">
                        {{ number_format((float)$a->duty_hours, 1) }} hrs
                    </td>
                    <td style="text-align: center;">
                        @if($staff && $staff->staff_type === 'Teaching')
                            @if($a->oic_count > 0)
                                <span class="badge-oic">{{ $a->oic_count }}</span>
                            @else
                                <span style="color: #94a3b8;">0</span>
                            @endif
                        @else
                            <span style="color: #94a3b8;">N/A</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($staff && $staff->staff_type === 'Teaching')
                            @if($a->is_exam_secretary)
                                <span class="badge-sec">YES (SEC)</span>
                            @else
                                <span style="color: #94a3b8;">No</span>
                            @endif
                        @else
                            <span style="color: #94a3b8;">N/A</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #64748b;">
                        No staff duty assignments recorded for this exam yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(count($assignments) > 0)
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right; text-transform: uppercase;">Total:</td>
                    <td style="text-align: center; color: #1e3a8a;">{{ number_format($totalHours, 1) }} hrs</td>
                    <td style="text-align: center; color: #d97706;">{{ $totalOic }}</td>
                    <td style="text-align: center; color: #dc2626;">{{ $totalSec }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        Examination Duty Management Portal &bull; Single Exam Report &bull; Page 1 of 1
    </div>

</body>
</html>
