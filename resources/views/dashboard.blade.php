@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_header', 'Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="row animated-fade-in mb-4">
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="glass-panel p-3 text-center">
            <div class="text-secondary small fw-semibold">TOTAL STAFF</div>
            <div class="fs-3 fw-bold text-dark mt-1">{{ $totalStaff }}</div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="glass-panel p-3 text-center">
            <div class="text-secondary small fw-semibold">TEACHING</div>
            <div class="fs-3 fw-bold text-success mt-1">{{ $teachingStaff }}</div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="glass-panel p-3 text-center">
            <div class="text-secondary small fw-semibold">NON-TEACHING</div>
            <div class="fs-3 fw-bold text-info mt-1">{{ $nonTeachingStaff }}</div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="glass-panel p-3 text-center">
            <div class="text-secondary small fw-semibold">DUTY ENTRIES</div>
            <div class="fs-3 fw-bold text-primary mt-1">{{ $totalDuties }}</div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="glass-panel p-3 text-center">
            <div class="text-secondary small fw-semibold">SECRETARY DUTIES</div>
            <div class="fs-3 fw-bold text-danger mt-1">{{ $totalSecretaryDuties }}</div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="glass-panel p-3 text-center">
            <div class="text-secondary small fw-semibold">TOTAL ALLOCATIONS</div>
            <div class="fs-3 fw-bold text-dark mt-1">{{ $totalAllocations }}</div>
        </div>
    </div>
</div>

<!-- Quick Action Shortcuts -->
<div class="row animated-fade-in mb-4">
    <div class="col-12">
        <div class="glass-panel p-3 bg-white border border-secondary border-opacity-15 shadow-sm rounded-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary p-2 fs-6"><i class="fa-solid fa-bolt"></i></span>
                    <div>
                        <h6 class="m-0 fw-semibold text-dark">Quick Actions</h6>
                        <small class="text-secondary">Intake staff records (Name only) or schedule exam duties</small>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('staff.upload') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fa-solid fa-paste me-1"></i> Paste Names (Text Area)
                    </a>
                    <a href="{{ route('staff.upload') }}" class="btn btn-outline-success btn-sm">
                        <i class="fa-solid fa-file-excel me-1"></i> Upload CSV / XLS
                    </a>
                    <a href="{{ route('staff.create') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-user-plus me-1"></i> Add Single Staff
                    </a>
                    <a href="{{ route('duty.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-calendar-plus me-1"></i> New Duty Entry
                    </a>
                    <a href="{{ route('report.index') }}" class="btn btn-outline-dark btn-sm">
                        <i class="fa-solid fa-calculator me-1"></i> Duty Calculations
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- All Duties Management -->
<div class="row animated-fade-in">
    <div class="col-12">
        <div class="glass-panel p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="m-0"><i class="fa-solid fa-list-check text-indigo me-2"></i>All Exam Duties</h5>
                    <small class="text-muted">{{ count($allDuties) }} total entries</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('duty.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fa-solid fa-list me-1"></i> View All Exams
                    </a>
                    <a href="{{ route('duty.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-plus me-1"></i> Create Exam
                    </a>
                </div>
            </div>

            @if($allDuties->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-striped datatable align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>Exam Name</th>
                                <th class="text-center">Staff Assigned</th>
                                <th class="text-center">Secretaries</th>
                                <th class="text-center">OIC Duties</th>
                                <th>Remarks</th>
                                <th class="text-center no-sort" style="min-width: 220px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allDuties as $duty)
                                <tr>
                                    <td class="fw-semibold text-dark">{{ $duty->exam_name }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary bg-opacity-10 text-primary border px-2 py-1">{{ $duty->staff_count }} staff</span>
                                    </td>
                                    <td class="text-center">
                                        @if($duty->secretary_count > 0)
                                            <span class="badge bg-danger bg-opacity-10 text-danger border px-2 py-1">{{ $duty->secretary_count }}</span>
                                        @else
                                            <span class="text-muted small">0</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($duty->oic_count > 0)
                                            <span class="badge bg-warning bg-opacity-10 text-warning border px-2 py-1">{{ $duty->oic_count }}</span>
                                        @else
                                            <span class="text-muted small">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="small text-secondary" title="{{ $duty->remarks }}">
                                            {{ $duty->remarks ? \Illuminate\Support\Str::limit($duty->remarks, 50, '...') : '—' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('duty.edit', $duty) }}" class="btn btn-sm btn-outline-primary" title="Edit / Resume Data Entry">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Edit / Resume
                                            </a>
                                            <a href="{{ route('duty.export-excel', $duty) }}" class="btn btn-sm btn-outline-success" title="Download Excel Report for {{ $duty->exam_name }}">
                                                <i class="fa-solid fa-file-excel"></i>
                                            </a>
                                            <a href="{{ route('duty.export-pdf', $duty) }}" class="btn btn-sm btn-outline-danger" title="Download PDF Report for {{ $duty->exam_name }}">
                                                <i class="fa-solid fa-file-pdf"></i>
                                            </a>
                                            <form action="{{ route('duty.destroy', $duty) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this exam entry and all its staff assignments? This cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-light text-center py-4">
                    <i class="fa-solid fa-inbox fs-3 text-muted d-block mb-2"></i>
                    No duty entries yet.<br>
                    <a href="{{ route('duty.create') }}" class="btn btn-sm btn-primary mt-2">Create First Duty</a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Quick Charts / Distribution (optional summary) -->
@endsection

@section('styles')
<style>
    .table th, .table td { vertical-align: middle; }
</style>
@endsection
