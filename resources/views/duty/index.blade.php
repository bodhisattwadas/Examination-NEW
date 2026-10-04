@extends('layouts.app')

@section('title', 'Exam Duty Records')
@section('page_header', 'Exam Duty Records')

@section('content')
<div class="row animated-fade-in">
    <!-- Top Action Bar -->
    <div class="col-12 mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h4 class="fw-bold text-dark m-0">Exam Duty Records</h4>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">{{ count($allExams) }} Total</span>
                </div>
                <p class="text-secondary small m-0 mt-1">Manage recorded exams, download individual reports, or create a new exam duty entry</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('duty.create') }}" class="btn btn-primary shadow-sm">
                    <i class="fa-solid fa-plus-circle me-1"></i> Create Exam
                </a>
                <a href="{{ route('report.index') }}" class="btn btn-outline-primary border-secondary border-opacity-25 shadow-sm">
                    <i class="fa-solid fa-calculator me-1"></i> Annual Matrix Calculation
                </a>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="col-12 mb-3">
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="fa-solid fa-circle-check fs-5 me-2"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    @if(session('info'))
        <div class="col-12 mb-3">
            <div class="alert alert-info alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="fa-solid fa-circle-info fs-5 me-2"></i>
                <div>{{ session('info') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="col-12 mb-3">
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="fa-solid fa-circle-exclamation fs-5 me-2"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <!-- List of All Exams Card -->
    <div class="col-12">
        <div class="glass-panel p-4">
            @if(count($allExams) > 0)
                <div class="table-responsive">
                    <table class="table table-striped align-middle" id="exams-table" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 45px;" class="text-center">#</th>
                                <th>Exam Name</th>
                                <th class="text-center">Staff Assigned</th>
                                <th class="text-center">Total Duty Hours</th>
                                <th class="text-center">Secretaries</th>
                                <th class="text-center">OIC Duties</th>
                                <th>Remarks</th>
                                <th class="text-center no-sort" style="min-width: 220px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allExams as $duty)
                                <tr>
                                    <td class="text-center text-muted small">{{ $loop->iteration }}</td>
                                    <td class="fw-semibold text-dark">
                                        <a href="{{ route('duty.edit', $duty) }}" class="text-decoration-none text-dark fw-bold hover-primary">
                                            {{ $duty->exam_name }}
                                        </a>
                                        @if($duty->formatted_date_range)
                                            <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                                                <i class="fa-solid fa-calendar-days text-primary me-1"></i> {{ $duty->formatted_date_range }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary bg-opacity-10 text-primary border px-2 py-1">{{ $duty->staff_count }} staff</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-dark">{{ number_format($duty->total_hours, 1) }}</span> <span class="small text-muted">hrs</span>
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
                                            {{ $duty->remarks ? \Illuminate\Support\Str::limit($duty->remarks, 45, '...') : '—' }}
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
                                            <form action="{{ route('duty.destroy', $duty) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete exam duty entry for \'{{ addslashes($duty->exam_name) }}\' and all its staff assignments? This cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-secondary text-danger" title="Delete Exam Entry">
                                                    <i class="fa-solid fa-trash-can"></i>
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
                <div class="text-center py-5">
                    <div class="mb-3 text-secondary opacity-50">
                        <i class="fa-solid fa-calendar-xmark fa-3x"></i>
                    </div>
                    <h5 class="fw-bold text-dark">No Exam Duty Records Found</h5>
                    <p class="text-muted small">No exam duty records have been created yet. Click below to create your first exam entry.</p>
                    <a href="{{ route('duty.create') }}" class="btn btn-primary mt-2">
                        <i class="fa-solid fa-plus-circle me-1"></i> Create First Exam Duty
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        if ($('#exams-table').length && !$.fn.DataTable.isDataTable('#exams-table')) {
            $('#exams-table').DataTable({
                "pageLength": 25,
                "order": [[0, "asc"]],
                "language": {
                    "search": "<i class='fa-solid fa-magnifying-glass text-secondary'></i>",
                    "searchPlaceholder": "Search exams..."
                },
                "columnDefs": [
                    { "orderable": false, "targets": "no-sort" }
                ]
            });
        }
    });
</script>
@endsection
