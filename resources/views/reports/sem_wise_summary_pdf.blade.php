<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sem-wise Duty Summary</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #222;
            font-size: 8.5px;
            line-height: 1.25;
        }
        .header {
            text-align: center;
            border-bottom: 1px solid #666;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 14px;
            margin: 0 0 2px 0;
            color: #000;
            font-weight: bold;
        }
        .header h2 {
            font-size: 11px;
            margin: 0 0 2px 0;
            color: #333;
            font-weight: 500;
        }
        .header p {
            font-size: 8px;
            color: #555;
            margin: 0;
            font-style: italic;
        }
        .section-title {
            font-size: 10px;
            font-weight: bold;
            color: #000;
            margin-bottom: 4px;
            border-bottom: 1px solid #999;
            padding-bottom: 2px;
            page-break-after: avoid;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 7.5px;
            page-break-inside: avoid;
        }
        th {
            background-color: #ddd;
            color: #000;
            font-weight: bold;
            text-align: center;
            padding: 3px 2px;
            border: 1px solid #999;
            vertical-align: middle;
        }
        td {
            padding: 2px 3px;
            border: 1px solid #ccc;
            vertical-align: middle;
            text-align: center;
        }
        tr:nth-child(even) {
            background-color: #f0f0f0;
        }
        .staff-name {
            text-align: left;
            font-weight: 500;
            padding-left: 4px;
        }
        .total-col {
            background-color: #e8e8e8;
            font-weight: bold;
        }
        .grand-total-row th,
        .grand-total-row td {
            background-color: #ccc;
            color: #000;
            font-weight: bold;
            border-color: #999;
        }
        .matrix-zero {
            background-color: #f5f5f5;
            color: #666;
        }
        .matrix-one {
            background-color: #fff;
        }
        .matrix-many {
            background-color: #e8e8e8;
        }
        .matrix-sec {
            background-color: #ffeeee;
            color: #c00;
            font-weight: bold;
        }
        .matrix-oic {
            background-color: #ffeeee;
            color: #c00;
            font-weight: bold;
        }
        .oic-note {
            font-size: 6.5px;
            color: #c00;
            font-weight: bold;
            line-height: 1.0;
        }
        .sec-row th {
            background-color: #ffeeee;
            color: #c00;
            font-weight: bold;
            border-color: #ffcccc;
        }
        .oic-row th {
            background-color: #ffeeee;
            color: #c00;
            font-weight: bold;
            border-color: #ffcccc;
        }
        .sec-note {
            font-size: 6.5px;
            color: #c00;
            font-weight: bold;
            line-height: 1.0;
        }
        .page-break {
            page-break-after: always;
        }
        .page-break-before {
            page-break-before: always;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Examination Duty Management Portal</h1>
        <h2>Sem-wise Duty Hours + Secretary Summary</h2>
        <p>
            Generated on: {{ date('d-m-Y H:i:s') }}
        </p>
    </div>

    @foreach(($examResults ?? []) as $result)
    @if($loop->index > 0)
    <div style="page-break-before: always;"></div>
    @endif
    <div class="section-title">Duty Hours + Exam Secretary Matrix — {{ $result['examName'] }} ({{ count($result['staffMatrix']) }} staff members)</div>

    <table>
        <thead>
            <tr>
                <th style="width: 18%; text-align: left; padding-left: 6px;">Staff Name</th>
                @foreach($result['dates'] as $date)
                    <th style="width: {{ 60 / max(count($result['dates']), 1) }}%;">
                        {{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}
                    </th>
                @endforeach
                <th style="width: 10%;" class="total-col">Total Hours<br><span style="font-size:7px;font-weight:normal;">SEC / OIC duties</span></th>
            </tr>
        </thead>
        <tbody>
            @forelse($result['staffMatrix'] as $row)
                <tr>
                    <td class="staff-name">{{ $row['staff_name'] }}</td>
                    @foreach($result['dates'] as $date)
                        @php 
                            $hours = $row['date_hours'][$date] ?? 0; 
                            $secCount = $row['date_sec_counts'][$date] ?? 0;
                            $oicCount = $row['date_oic_counts'][$date] ?? 0;
                            $secLabel = '';
                            if ($secCount > 0) {
                                $secLabel = 'SEC';
                                if ($secCount > 1) {
                                    $secLabel .= '×' . $secCount;
                                }
                            }
                            $oicLabel = '';
                            if ($oicCount > 0) {
                                $oicLabel = 'OIC';
                                if ($oicCount > 1) {
                                    $oicLabel .= '×' . $oicCount;
                                }
                            }
                        @endphp
                        <td class="{{ $secCount > 0 ? 'matrix-sec' : ($oicCount > 0 ? 'matrix-oic' : ($hours == 0 ? 'matrix-zero' : 'matrix-one')) }}" style="font-size: 8px; line-height: 1.05; padding: 2px 3px;">
                            @if($hours > 0)
                                {{ $hours == (int)$hours ? (int)$hours : number_format($hours, 1) }}
                            @endif
                            @if($secCount > 0)
                                @if($hours > 0 || $oicCount > 0)<br>@endif
                                <span class="sec-note">{{ $secLabel }}</span>
                            @endif
                            @if($oicCount > 0)
                                @if($hours > 0 || $secCount > 0)<br>@endif
                                <span class="sec-note oic-note">{{ $oicLabel }}</span>
                            @endif
                        </td>
                    @endforeach
                    <td class="total-col" style="font-size: 8px; line-height: 1.05; padding: 2px 3px;">
                        {{ $row['total_hours'] == (int)$row['total_hours'] ? (int)$row['total_hours'] : number_format($row['total_hours'], 1) }}
                        @if($row['total_sec_duties'] > 0)
                            <br><span class="sec-note">SEC: {{ $row['total_sec_duties'] }}</span>
                        @endif
                        @if($row['total_oic_duties'] > 0)
                            <br><span class="sec-note oic-note">OIC: {{ $row['total_oic_duties'] }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($result['dates']) + 2 }}" style="text-align: center;">No duty records found for {{ $result['examName'] }}.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th style="text-align: right;">Total Hours per Date</th>
                @foreach($result['dates'] as $date)
                    @php
                        $dateTotal = collect($result['staffMatrix'])->sum(function($s) use ($date) {
                            return $s['date_hours'][$date] ?? 0;
                        });
                    @endphp
                    <th>{{ ($dt = $dateTotal) == (int)$dt ? (int)$dt : number_format($dt, 1) }}</th>
                @endforeach
                <th>{{ ($th = collect($result['staffMatrix'])->sum('total_hours')) == (int)$th ? (int)$th : number_format($th, 1) }}</th>
            </tr>
            <tr class="sec-row">
                <th style="text-align: right;">SEC duties per Date</th>
                @foreach($result['dates'] as $date)
                    @php
                        $secTotal = collect($result['staffMatrix'])->sum(function($s) use ($date) {
                            return $s['date_sec_counts'][$date] ?? 0;
                        });
                    @endphp
                    <th>{{ $secTotal > 0 ? $secTotal : '-' }}</th>
                @endforeach
                <th>{{ collect($result['staffMatrix'])->sum('total_sec_duties') }}</th>
            </tr>
            <tr class="oic-row">
                <th style="text-align: right;">OIC duties per Date</th>
                @foreach($result['dates'] as $date)
                    @php
                        $oicTotal = collect($result['staffMatrix'])->sum(function($s) use ($date) {
                            return $s['date_oic_counts'][$date] ?? 0;
                        });
                    @endphp
                    <th>{{ $oicTotal > 0 ? $oicTotal : '-' }}</th>
                @endforeach
                <th>{{ collect($result['staffMatrix'])->sum('total_oic_duties') }}</th>
            </tr>
            <tr class="grand-total-row">
                <th style="text-align: right;">Grand Total Hours</th>
                @foreach($result['dates'] as $date)
                    @php
                        $dateTotal = collect($result['staffMatrix'])->sum(function($s) use ($date) {
                            return $s['date_hours'][$date] ?? 0;
                        });
                    @endphp
                    <th>{{ ($dt = $dateTotal) == (int)$dt ? (int)$dt : number_format($dt, 1) }}</th>
                @endforeach
                <th>{{ ($th = collect($result['staffMatrix'])->sum('total_hours')) == (int)$th ? (int)$th : number_format($th, 1) }}</th>
            </tr>
        </tfoot>
    </table>
    @endforeach

    @if(!empty($staffTotals))
    <div style="page-break-before: always;"></div>
    <div class="section-title" style="margin-top: 15px;">Summary - Totals Across Selected Exams</div>
    <table>
        <thead>
            <tr>
                <th style="width: 40%; text-align: left; padding-left: 6px;">Staff Name</th>
                <th style="width: 20%;">Total Duty Hours</th>
                <th style="width: 20%;">Total SEC Duties</th>
                <th style="width: 20%;">Total OIC Duties</th>
            </tr>
        </thead>
        <tbody>
            @foreach($staffTotals as $name => $totals)
                <tr>
                    <td class="staff-name">{{ $name }}</td>
                    <td>{{ ($h = $totals['total_hours']) == (int)$h ? (int)$h : number_format($h, 1) }}</td>
                    <td>{{ $totals['total_sec_duties'] }}</td>
                    <td>{{ $totals['total_oic_duties'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif

</body>
</html>
