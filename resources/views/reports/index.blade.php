@extends('layouts.app')

@section('title', 'Annual Exam Duty Hours Calculation')
@section('page_header', 'Annual Exam Duty Hours Calculation')

@section('content')
<div class="row g-3 mb-4 no-print">
    <!-- Top KPI Cards -->
    <div class="col-md-2 col-sm-4 col-6">
        <div class="glass-panel p-3 text-center border-start border-primary border-4">
            <div class="text-secondary small fw-semibold text-uppercase">Exams Recorded</div>
            <div class="fs-3 fw-bold text-primary mt-1">{{ count($exams) }}</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="glass-panel p-3 text-center border-start border-success border-4">
            <div class="text-secondary small fw-semibold text-uppercase">Teaching Staff</div>
            <div class="fs-3 fw-bold text-success mt-1">{{ $grandTotals['teaching_staff'] }}</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="glass-panel p-3 text-center border-start border-info border-4">
            <div class="text-secondary small fw-semibold text-uppercase">Non-Teaching</div>
            <div class="fs-3 fw-bold text-info mt-1">{{ $grandTotals['non_teaching_staff'] }}</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="glass-panel p-3 text-center border-start border-purple border-4">
            <div class="text-secondary small fw-semibold text-uppercase">Total Duty Hours</div>
            <div class="fs-3 fw-bold text-dark mt-1">{{ number_format($grandTotals['hours'], 1) }} <span class="fs-6 fw-normal text-muted">hrs</span></div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="glass-panel p-3 text-center border-start border-warning border-4">
            <div class="text-secondary small fw-semibold text-uppercase">Total OIC Duties</div>
            <div class="fs-3 fw-bold text-warning mt-1">{{ $grandTotals['oic'] }}</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="glass-panel p-3 text-center border-start border-danger border-4">
            <div class="text-secondary small fw-semibold text-uppercase">Secretary Duties</div>
            <div class="fs-3 fw-bold text-danger mt-1">{{ $grandTotals['sec'] }}</div>
        </div>
    </div>
</div>

<!-- Tabs and Action Bar -->
<div class="glass-panel p-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3 border-bottom pb-3 no-print">
        <!-- Navigation Pills -->
        <ul class="nav nav-pills gap-2" id="reportTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-semibold px-3 py-2" id="matrix-tab" data-bs-toggle="pill" data-bs-target="#matrix-pane" type="button" role="tab">
                    <i class="fa-solid fa-calculator me-1 text-primary"></i> Annual Duty Calculation
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold px-3 py-2" id="exams-tab" data-bs-toggle="pill" data-bs-target="#exams-pane" type="button" role="tab">
                    <i class="fa-solid fa-clipboard-list me-1 text-info"></i> Recorded Exams Directory ({{ count($allExamsList) }})
                </button>
            </li>
        </ul>

        <!-- Export & Action Buttons -->
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('duty.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus me-1"></i> Record Exam Duty
            </a>
            <a href="{{ route('report.export-excel', request()->all()) }}" class="btn btn-success">
                <i class="fa-solid fa-file-excel me-1"></i> Export Excel
            </a>
            <a href="{{ route('report.export-pdf', request()->all()) }}" class="btn btn-danger">
                <i class="fa-solid fa-file-pdf me-1"></i> Export PDF
            </a>
            <button type="button" onclick="window.print()" class="btn btn-secondary border-secondary border-opacity-25">
                <i class="fa-solid fa-print me-1"></i> Print
            </button>
        </div>
    </div>

    <!-- Filter Bar (no-print) -->
    <div class="bg-light p-3 rounded-3 mb-4 border border-secondary border-opacity-10 no-print">
        <form method="GET" action="{{ route('report.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-secondary small fw-semibold mb-1">Staff Category</label>
                <select name="staff_type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Staff (Teaching & Non-Teaching)</option>
                    <option value="Teaching" {{ request('staff_type') == 'Teaching' ? 'selected' : '' }}>Teaching Only</option>
                    <option value="Non-teaching" {{ request('staff_type') == 'Non-teaching' ? 'selected' : '' }}>Non-teaching Only</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label text-secondary small fw-semibold mb-1">Filter by Specific Exam(s)</label>
                <select name="exam_ids[]" class="form-select form-select-sm" multiple size="1" style="min-height: 31px;" onchange="this.form.submit()">
                    @foreach($allExamsList as $ex)
                        <option value="{{ $ex->id }}" {{ in_array($ex->id, (array)request('exam_ids', [])) ? 'selected' : '' }}>
                            {{ $ex->exam_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-secondary small fw-semibold mb-1">Search Staff Name</label>
                <input type="text" name="staff_name" value="{{ request('staff_name') }}" class="form-control form-control-sm" placeholder="Type staff name...">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="fa-solid fa-filter me-1"></i> Filter
                </button>
                @if(request()->hasAny(['staff_type', 'exam_ids', 'staff_name']))
                    <a href="{{ route('report.index') }}" class="btn btn-secondary border-secondary border-opacity-25 btn-sm" title="Clear Filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tab Content -->
    <div class="tab-content" id="reportTabsContent">
        <!-- TAB 1: Annual Duty Matrix -->
        <div class="tab-pane fade show active" id="matrix-pane" role="tabpanel">
            @if(count($exams) === 0)
                <div class="text-center py-5">
                    <div class="fs-1 text-muted mb-3"><i class="fa-solid fa-clipboard-question"></i></div>
                    <h5 class="fw-bold text-dark">No Exam Duty Records Found</h5>
                    <p class="text-secondary small">Start recording exam duties to automatically calculate duty hours throughout the year.</p>
                    <a href="{{ route('duty.create') }}" class="btn btn-primary mt-2">
                        <i class="fa-solid fa-plus me-1"></i> Record First Exam Duty
                    </a>
                </div>
            @else
                <div class="sticky-table-wrapper">
                    <table class="table table-bordered table-hover align-middle mb-0" id="annual-matrix-table" style="font-size: 0.9rem;">
                        <thead>
                            <tr class="text-center">
                                <th style="width: 50px;" class="text-center">#</th>
                                <th style="min-width: 220px;" class="text-start">Staff Name</th>
                                <th style="min-width: 130px;" class="text-center">Category</th>
                                <th style="min-width: 170px;" class="text-center bg-primary text-white">
                                    <i class="fa-solid fa-clock me-1"></i> Total Regular Hours
                                </th>
                                <th style="min-width: 140px;" class="text-center bg-warning text-dark">
                                    <i class="fa-solid fa-user-tie me-1"></i> Total OIC Duties
                                </th>
                                <th style="min-width: 140px;" class="text-center bg-danger text-white">
                                    <i class="fa-solid fa-user-shield me-1"></i> Total Secretary Duties
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($matrix as $row)
                                <tr>
                                    <td class="text-center text-muted small">{{ $loop->iteration }}</td>
                                    <td class="fw-semibold text-dark">{{ $row['staff']->name }}</td>
                                    <td class="text-center">
                                        @if($row['staff']->staff_type == 'Teaching')
                                            <span class="badge rounded bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-2 py-1">
                                                Teaching
                                            </span>
                                        @else
                                            <span class="badge rounded bg-info bg-opacity-10 text-info border border-info border-opacity-20 px-2 py-1">
                                                Non-teaching
                                            </span>
                                        @endif
                                    </td>
                                    <!-- Total Columns -->
                                    <td class="text-center bg-primary bg-opacity-10 fw-bold fs-6 text-primary">
                                        {{ number_format($row['total_hours'], 1) }} <span class="small fw-normal">hrs</span>
                                    </td>
                                    <td class="text-center bg-warning bg-opacity-10 fw-semibold text-dark">
                                        @if($row['staff']->staff_type == 'Teaching')
                                            {{ $row['total_oic'] > 0 ? $row['total_oic'] : '0' }}
                                        @else
                                            <span class="text-muted small">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-center bg-danger bg-opacity-10 fw-semibold text-dark">
                                        @if($row['staff']->staff_type == 'Teaching')
                                            {{ $row['total_sec'] > 0 ? $row['total_sec'] : '0' }}
                                        @else
                                            <span class="text-muted small">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        No staff records found matching the filter criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="text-center">
                                <td colspan="3" class="text-end text-uppercase px-3 fw-bold">Column Total:</td>
                                <td class="text-center bg-primary text-white fs-6 fw-bold">
                                    {{ number_format($grandTotals['hours'], 1) }} hrs
                                </td>
                                <td class="text-center bg-warning text-dark fs-6 fw-bold">
                                    {{ $grandTotals['oic'] }}
                                </td>
                                <td class="text-center bg-danger text-white fs-6 fw-bold">
                                    {{ $grandTotals['sec'] }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>

        <!-- TAB 2: Recorded Exams Directory -->
        <div class="tab-pane fade" id="exams-pane" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="text-secondary small">
                    All exam duty events created throughout the year. You can edit entries to update hours or delete an entry if created by mistake.
                </div>
                <a href="{{ route('duty.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus me-1"></i> Add New Exam
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;" class="text-center">#</th>
                            <th>Exam Name</th>
                            <th>Remarks / Notes</th>
                            <th class="text-center">Staff Assigned</th>
                            <th>Date Recorded</th>
                            <th class="text-end" style="min-width: 220px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allExamsList as $ex)
                            <tr>
                                <td class="text-center text-muted small">{{ $loop->iteration }}</td>
                                <td class="fw-semibold text-dark">{{ $ex->exam_name }}</td>
                                <td class="text-secondary small">{{ $ex->remarks ?: '—' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-secondary px-2 py-1">{{ $ex->duty_assignments_count }} staff</span>
                                </td>
                                <td class="small text-muted">{{ $ex->created_at ? $ex->created_at->format('d M Y') : '—' }}</td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('duty.edit', $ex->id) }}" class="btn btn-sm btn-outline-primary" title="Edit / Resume Data Entry">
                                            <i class="fa-solid fa-pen-to-square me-1"></i> Edit / Resume
                                        </a>
                                        <a href="{{ route('duty.export-excel', $ex->id) }}" class="btn btn-sm btn-outline-success" title="Download Excel Report for {{ $ex->exam_name }}">
                                            <i class="fa-solid fa-file-excel"></i>
                                        </a>
                                        <a href="{{ route('duty.export-pdf', $ex->id) }}" class="btn btn-sm btn-outline-danger" title="Download PDF Report for {{ $ex->exam_name }}">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </a>
                                        <form action="{{ route('duty.destroy', $ex->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete exam entry \'{{ $ex->exam_name }}\' and all its assigned hours?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Delete exam entry">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No exam duty entries created yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
/* Sticky Header & Table Styling */
.sticky-table-wrapper {
    position: relative;
    max-height: 68vh;
    overflow-y: auto;
    overflow-x: auto;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #ffffff;
    -webkit-overflow-scrolling: touch;
}

.sticky-table-wrapper table {
    border-collapse: separate !important;
    border-spacing: 0 !important;
    width: 100%;
}

.sticky-table-wrapper thead th {
    position: sticky !important;
    top: 0 !important;
    z-index: 30 !important;
    background-color: #0f172a !important; /* solid deep dark slate */
    color: #ffffff !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    font-size: 0.8rem !important;
    letter-spacing: 0.04em !important;
    padding: 12px 10px !important;
    border-top: none !important;
    border-bottom: 2px solid #334155 !important;
    box-shadow: 0 3px 5px -1px rgba(0, 0, 0, 0.25) !important;
}

.sticky-table-wrapper thead th.bg-primary {
    background-color: #1e3a8a !important;
    color: #ffffff !important;
}

.sticky-table-wrapper thead th.bg-warning {
    background-color: #b45309 !important;
    color: #ffffff !important;
}

.sticky-table-wrapper thead th.bg-danger {
    background-color: #991b1b !important;
    color: #ffffff !important;
}

.sticky-table-wrapper tfoot td {
    position: sticky !important;
    bottom: 0 !important;
    z-index: 25 !important;
    background-color: #f1f5f9 !important;
    font-weight: 700 !important;
    border-top: 2px solid #0f172a !important;
    box-shadow: 0 -3px 5px -1px rgba(0, 0, 0, 0.15) !important;
}

@media print {
    .no-print, nav, .sidebar, .topbar, .btn, .nav-pills, form {
        display: none !important;
    }
    body {
        background: #fff !important;
        font-size: 11pt !important;
    }
    .glass-panel {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
    table {
        width: 100% !important;
        border-collapse: collapse !important;
    }
    th, td {
        border: 1px solid #333 !important;
        padding: 4px 6px !important;
    }
}
</style>
@endsection
