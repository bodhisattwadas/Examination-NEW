@extends('layouts.app')

@section('title', 'Sem-wise Summary Report')
@section('page_header', 'Sem-wise Summary')

@section('content')
<div class="row animated-fade-in no-print">
    <div class="col-12 mb-4">
        <div class="glass-panel p-4">
            <h5 class="mb-3"><i class="fa-solid fa-table text-indigo me-2"></i>Sem-wise Duty Hours + Secretary Matrix</h5>
            
            @if(!($isPdf ?? false))
            <div class="alert alert-light">
                <strong>Selected:</strong> {{ implode(', ', array_column($examResults ?? [], 'examName')) }}<br>
                <small class="text-muted">Matrix shows <strong>regular duty hours</strong> (SEC and OIC hours are excluded from all totals). "SEC"/"OIC" markers + separate counts in right column.</small>
            </div>
            @endif

            @if(!($isPdf ?? false))
            <div class="d-flex gap-2 mb-3">
                <a href="{{ route('report.index', $filters) }}" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Reports
                </a>
                <a href="{{ route('report.sem-wise-summary.export-pdf', $filters) }}" class="btn btn-danger btn-sm">
                    <i class="fa-solid fa-file-pdf me-1"></i> Export PDF
                </a>
                <button type="button" class="btn btn-secondary btn-sm text-info" onclick="window.print()">
                    <i class="fa-solid fa-print me-1"></i> Print
                </button>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="row animated-fade-in">
    <div class="col-12">
        @foreach(($examResults ?? []) as $result)
        <div class="glass-panel p-4 mb-4">
            <h6 class="mb-3">Duty Hours + Secretary Matrix — {{ $result['examName'] }}</h6>
            
            @if(!empty($result['staffMatrix']))
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle" style="min-width: 800px;">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 180px;">Staff Name</th>
                                @foreach($result['dates'] as $date)
                                    <th class="text-center" style="min-width: 85px;">
                                        {{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}<br>
                                    </th>
                                @endforeach
                                <th class="text-center bg-success text-white" style="min-width: 95px;">
                                    Total Hours<br>
                                    <small>SEC duties</small>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($result['staffMatrix'] as $row)
                                <tr>
                                    <td class="fw-medium">{{ $row['staff_name'] }}</td>
                                    @foreach($result['dates'] as $date)
                                        @php 
                                            $hours = $row['date_hours'][$date] ?? 0; 
                                            $secCount = $row['date_sec_counts'][$date] ?? 0;
                                            $oicCount = $row['date_oic_counts'][$date] ?? 0;
                                            if ($secCount > 0) {
                                                $cellClass = 'bg-danger-subtle text-danger fw-semibold';
                                            } elseif ($oicCount > 0) {
                                                $cellClass = 'bg-warning-subtle text-warning fw-semibold';
                                            } elseif ($hours == 0) {
                                                $cellClass = 'bg-light text-muted';
                                            } else {
                                                $cellClass = 'bg-info-subtle text-info fw-semibold';
                                            }
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
                                        <td class="text-center {{ $cellClass }}" style="line-height: 1.1; padding: 4px;">
                                            @if($hours > 0)
                                                {{ $hours == (int)$hours ? (int)$hours : number_format($hours, 1) }}
                                            @endif
                                            @if($secCount > 0)
                                                @if($hours > 0 || $oicCount > 0)<br>@endif
                                                <small class="fw-bold" style="font-size: 0.72em;">{{ $secLabel }}</small>
                                            @endif
                                            @if($oicCount > 0)
                                                @if($hours > 0 || $secCount > 0)<br>@endif
                                                <small class="fw-bold" style="font-size: 0.72em;">{{ $oicLabel }}</small>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="text-center fw-bold bg-light" style="line-height: 1.15; padding: 4px;">
                                        {{ $row['total_hours'] == (int)$row['total_hours'] ? (int)$row['total_hours'] : number_format($row['total_hours'], 1) }}
                                        @if($row['total_sec_duties'] > 0)
                                            <br><small class="text-danger fw-bold" style="font-size: 0.72em;">SEC: {{ $row['total_sec_duties'] }}</small>
                                        @endif
                                        @if($row['total_oic_duties'] > 0)
                                            <br><small class="text-warning fw-bold" style="font-size: 0.72em;">OIC: {{ $row['total_oic_duties'] }}</small>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <th class="text-end">Total Hours per Date</th>
                                @foreach($result['dates'] as $date)
                                    @php 
                                        $dateTotal = collect($result['staffMatrix'])->sum(function($s) use ($date) {
                                            return $s['date_hours'][$date] ?? 0;
                                        });
                                    @endphp
                                    <th class="text-center fw-semibold">{{ ($dt = $dateTotal) == (int)$dt ? (int)$dt : number_format($dt, 1) }}</th>
                                @endforeach
                                <th class="text-center fw-bold bg-light">{{ ($th = collect($result['staffMatrix'])->sum('total_hours')) == (int)$th ? (int)$th : number_format($th, 1) }}</th>
                            </tr>
                            <tr class="table-danger-subtle">
                                <th class="text-end">SEC duties per Date</th>
                                @foreach($result['dates'] as $date)
                                    @php 
                                        $secTotal = collect($result['staffMatrix'])->sum(function($s) use ($date) {
                                            return $s['date_sec_counts'][$date] ?? 0;
                                        });
                                    @endphp
                                    <th class="text-center fw-semibold text-danger">{{ $secTotal > 0 ? $secTotal : '-' }}</th>
                                @endforeach
                                <th class="text-center fw-bold bg-light text-danger">
                                    {{ collect($result['staffMatrix'])->sum('total_sec_duties') }}
                                </th>
                            </tr>
                            <tr class="table-warning-subtle">
                                <th class="text-end">OIC duties per Date</th>
                                @foreach($result['dates'] as $date)
                                    @php 
                                        $oicTotal = collect($result['staffMatrix'])->sum(function($s) use ($date) {
                                            return $s['date_oic_counts'][$date] ?? 0;
                                        });
                                    @endphp
                                    <th class="text-center fw-semibold text-warning">{{ $oicTotal > 0 ? $oicTotal : '-' }}</th>
                                @endforeach
                                <th class="text-center fw-bold bg-light text-warning">
                                    {{ collect($result['staffMatrix'])->sum('total_oic_duties') }}
                                </th>
                            </tr>
                            <tr>
                                <th class="text-end bg-success text-white">Grand Total Hours</th>
                                @foreach($result['dates'] as $date)
                                    @php 
                                        $dateTotal = collect($result['staffMatrix'])->sum(function($s) use ($date) {
                                            return $s['date_hours'][$date] ?? 0;
                                        });
                                    @endphp
                                    <th class="text-center bg-success text-white fw-bold">{{ ($dt = $dateTotal) == (int)$dt ? (int)$dt : number_format($dt, 1) }}</th>
                                @endforeach
                                <th class="text-center bg-success text-white fw-bold fs-6">{{ ($th = collect($result['staffMatrix'])->sum('total_hours')) == (int)$th ? (int)$th : number_format($th, 1) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div class="mt-3 small text-muted">
                    <strong>Legend:</strong> Cells show <strong>regular duty hours</strong> (SEC and OIC hours are <strong>never</strong> counted or added to any Total Hours figures).<br>
                    If the staff had any Exam Secretary assignment on that date, <span class="text-danger fw-bold">"SEC"</span> (or SEC×N) appears. Similarly for <span class="text-warning fw-bold">"OIC"</span>.<br>
                    Right column: <strong>Total (regular) Hours</strong> for the staff + separate <strong>SEC duty count</strong> and <strong>OIC duty count</strong> below it (only if >0).<br>
                    <span class="bg-light px-1">0.00</span> or blank = No regular hours &nbsp;
                    <span class="bg-info-subtle px-1">3.00</span> = Regular duty hours &nbsp;
                    <span class="bg-danger-subtle px-1 text-danger">3.00<br>SEC</span> = Regular hours + Secretary &nbsp;
                    <span class="bg-warning-subtle px-1 text-warning">3.00<br>OIC</span> = Regular hours + OIC duty that day
                </div>
            @else
                <div class="alert alert-warning">
                    No duty records found for the selected semester.
                </div>
            @endif
        </div>
        @endforeach

        @if(!empty($staffTotals))
        <div class="glass-panel p-4 mt-3">
            <h6 class="mb-3"><i class="fa-solid fa-table text-indigo me-2"></i>Summary - Totals Across Selected Exams</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle" style="min-width: 600px;">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 200px;">Staff Name</th>
                            <th class="text-center">Total Duty Hours</th>
                            <th class="text-center">Total SEC Duties</th>
                            <th class="text-center">Total OIC Duties</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($staffTotals as $name => $totals)
                            <tr>
                                <td class="fw-medium">{{ $name }}</td>
                                <td class="text-center fw-bold">{{ ($h = $totals['total_hours']) == (int)$h ? (int)$h : number_format($h, 1) }}</td>
                                <td class="text-center fw-bold text-danger">{{ $totals['total_sec_duties'] }}</td>
                                <td class="text-center fw-bold text-warning">{{ $totals['total_oic_duties'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('styles')
<style>
    .table-bordered th, .table-bordered td {
        vertical-align: top; /* better for multi-line "3.00<br>SEC" cells */
    }
    .table-bordered td {
        white-space: normal;
    }
    @media print {
        .no-print { display: none !important; }
        .glass-panel { border: 1px solid #ddd !important; box-shadow: none !important; }
    }
</style>
@endsection
