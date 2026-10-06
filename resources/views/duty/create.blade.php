@extends('layouts.app')

@section('title', 'Create New Exam Duty')
@section('page_header', 'Create Exam Duty')

@section('content')
<div class="row animated-fade-in">
    <div class="col-12 mb-4">
        <div class="glass-panel p-4 border border-secondary border-opacity-10 shadow-sm" style="background: #ffffff;">
            <!-- Top Header & Navigation -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 border-bottom border-secondary border-opacity-10 pb-3 gap-2">
                <div>
                    <h5 class="m-0 text-primary fw-bold">
                        <i class="fa-solid fa-calendar-plus me-2"></i>Create New Exam Duty Entry
                    </h5>
                    <p class="text-secondary small m-0 mt-1">Enter exam details and record duty hours, OIC duties, and secretary assignments</p>
                </div>
                <div>
                    <a href="{{ route('duty.index') }}" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back to Exam List
                    </a>
                </div>
            </div>

            <!-- Error Alerts -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <div class="fw-semibold mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> Please correct the errors below:</div>
                    <ul class="mb-0 small ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('duty.store') }}" method="POST" id="duty-form">
                @csrf

                <!-- Exam General Parameters -->
                <div class="row g-3 mb-4">
                    <div class="col-md-5">
                        <label class="form-label text-secondary small fw-semibold">Exam Name <span class="text-danger">*</span></label>
                        <input type="text" name="exam_name" id="exam_name" class="form-control form-control-lg @error('exam_name') is-invalid @enderror" value="{{ old('exam_name') }}" placeholder="e.g. End Semester Exam Nov 2026 / Mid Term" required autofocus>
                        @error('exam_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-secondary small fw-semibold">
                            <i class="fa-solid fa-calendar-days text-primary me-1"></i> Exam Date / Date Range
                        </label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white text-primary border-end-0">
                                <i class="fa-solid fa-calendar-week"></i>
                            </span>
                            <input type="text" name="exam_date_range" id="exam_date_range" class="form-control form-control-lg border-start-0 bg-white" placeholder="Select date or range..." value="{{ old('exam_date_range') }}" readonly style="cursor: pointer;">
                            <button class="btn btn-outline-secondary" type="button" id="btnClearDateRange" title="Clear Date Range">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <div class="text-muted" style="font-size: 0.72rem; margin-top: 3px;">
                            Click start and end date to select a range, or pick a single date
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-secondary small fw-semibold">Remarks</label>
                        <input type="text" name="remarks" id="remarks" class="form-control form-control-lg" value="{{ old('remarks') }}" placeholder="Special instructions / notes (optional)">
                    </div>
                </div>

                <!-- Guidance Info Box -->
                <div class="alert alert-info border-0 rounded-3 py-2 small mb-4" style="background:#f0f9ff; color:#0369a1; border-left: 4px solid #0284c7 !important;">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    <strong>Entry Rules:</strong>
                    Teachers can have <strong>Regular Hourly Duty</strong> + <strong>Total OIC Duty (count)</strong> + <strong>Secretary Duty</strong>.
                    Non-teaching staff can have <strong>Hourly Duty only</strong>.
                    Staff members with 0 / blank entries will not be recorded for this exam.
                </div>

                <!-- Staff Table Container -->
                <div class="border-top border-secondary border-opacity-10 pt-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="m-0"><i class="fa-solid fa-users text-primary me-2"></i>Staff Duty Hours</h5>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border">{{ count($staffs) }} Active Staff</span>
                        </div>
                        
                        <!-- Quick Category Filter Buttons -->
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary active filter-btn" data-filter="all">All ({{ count($staffs) }})</button>
                            <button type="button" class="btn btn-outline-success filter-btn" data-filter="Teaching">Teachers Only ({{ $staffs->where('staff_type', 'Teaching')->count() }})</button>
                            <button type="button" class="btn btn-outline-info filter-btn" data-filter="Non-teaching">Non-Teaching Only ({{ $staffs->where('staff_type', 'Non-teaching')->count() }})</button>
                        </div>
                    </div>

                    <!-- Staff Selection Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border" id="duty-staff-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;" class="text-center">#</th>
                                    <th style="width: 28%;">Staff Name</th>
                                    <th style="width: 14%;">Staff Type</th>
                                    <th style="width: 22%;" class="text-center">
                                        <i class="fa-solid fa-clock text-primary me-1"></i> Regular Duty Hours
                                        <div class="small text-muted fw-normal" style="font-size: 0.75rem;">(Teachers & Non-Teaching)</div>
                                    </th>
                                    <th style="width: 20%;" class="text-center">
                                        <i class="fa-solid fa-user-tie text-warning me-1"></i> Total Number of OIC Duty
                                        <div class="small text-muted fw-normal" style="font-size: 0.75rem;">(Teachers only - Count)</div>
                                    </th>
                                    <th style="width: 16%;" class="text-center">
                                        <i class="fa-solid fa-stamp text-danger me-1"></i> Exam Secretary
                                        <div class="small text-muted fw-normal" style="font-size: 0.75rem;">(Teachers only)</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($staffs as $staff)
                                    <tr data-staff-type="{{ $staff->staff_type }}">
                                        <td class="text-center text-muted small">{{ $loop->iteration }}</td>
                                        <td data-sort="{{ $staff->clean_name }}">
                                            <div class="fw-semibold text-dark">{{ $staff->clean_name }}</div>
                                            <div class="text-secondary small" style="font-size: 0.72rem;">{{ $staff->staff_code }}</div>
                                        </td>
                                        <td>
                                            @if($staff->staff_type === 'Teaching')
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">Teaching</span>
                                            @else
                                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1">Non-teaching</span>
                                            @endif
                                        </td>
                                        
                                        <!-- Regular Duty Hours -->
                                        <td class="text-center">
                                            <div class="input-group input-group-sm justify-content-center mx-auto" style="max-width: 140px;">
                                                <input type="number" 
                                                       step="0.5" 
                                                       min="0" 
                                                       max="500" 
                                                       name="duties[{{ $staff->id }}][duty_hours]" 
                                                       class="form-control text-center duty-hours-input" 
                                                       placeholder="0.0"
                                                       value="{{ old('duties.' . $staff->id . '.duty_hours', '') }}">
                                                <span class="input-group-text text-muted" style="font-size: 0.75rem;">hrs</span>
                                            </div>
                                        </td>

                                        <!-- Teacher Only: OIC Duty (Count) -->
                                        <td class="text-center">
                                            @if($staff->staff_type === 'Teaching')
                                                <div class="input-group input-group-sm justify-content-center mx-auto" style="max-width: 130px;">
                                                    <input type="number" 
                                                           step="1" 
                                                           min="0" 
                                                           max="100" 
                                                           name="duties[{{ $staff->id }}][oic_count]" 
                                                           class="form-control text-center oic-input" 
                                                           placeholder="0"
                                                           value="{{ old('duties.' . $staff->id . '.oic_count', '') }}">
                                                    <span class="input-group-text text-muted" style="font-size: 0.75rem;">times</span>
                                                </div>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>

                                        <!-- Teacher Only: Exam Secretary (Yes/No Switch) -->
                                        <td class="text-center">
                                            @if($staff->staff_type === 'Teaching')
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input secretary-switch" 
                                                           type="checkbox" 
                                                           name="duties[{{ $staff->id }}][is_exam_secretary]" 
                                                           value="1" 
                                                           id="sec_{{ $staff->id }}"
                                                           {{ old('duties.' . $staff->id . '.is_exam_secretary') ? 'checked' : '' }}
                                                           style="cursor: pointer; transform: scale(1.15);">
                                                </div>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="fa-solid fa-user-slash me-1"></i> No active staff found. Please add or upload staff records first.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Form Bottom Actions -->
                <div class="border-top border-secondary border-opacity-10 pt-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="text-secondary small">
                        <i class="fa-solid fa-clock-rotate-left me-1"></i> You can save and resume entering duty hours anytime later.
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" name="action" value="save_and_resume" class="btn btn-outline-primary px-3">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save & Continue Editing
                        </button>
                        <button type="submit" name="action" value="save_and_exit" class="btn btn-primary px-4">
                            <i class="fa-solid fa-check me-1"></i> Save & Exit to Exam List
                        </button>
                        <a href="{{ route('duty.index') }}" class="btn btn-secondary border-secondary border-opacity-25">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable with scrolling
        var table;
        if (!$.fn.DataTable.isDataTable('#duty-staff-table')) {
            table = $('#duty-staff-table').DataTable({
                "paging": false,
                "scrollY": "520px",
                "scrollCollapse": true,
                "order": [[1, 'asc']],  // Alphabetical by staff name
                "language": {
                    "search": "<i class='fa-solid fa-magnifying-glass text-secondary'></i>",
                    "searchPlaceholder": "Filter staff by name..."
                }
            });
        } else {
            table = $('#duty-staff-table').DataTable();
        }

        // Category filter buttons
        $('.filter-btn').on('click', function() {
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');

            var filter = $(this).data('filter');
            if (filter === 'all') {
                table.rows().nodes().to$().show();
            } else {
                table.rows().nodes().to$().each(function() {
                    var type = $(this).data('staff-type');
                    if (type === filter) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
        });

        // Initialize Range Datepicker
        var fp = flatpickr("#exam_date_range", {
            mode: "range",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d M Y",
            conjunction: " to ",
            allowInput: false
        });

        $('#btnClearDateRange').on('click', function() {
            fp.clear();
        });
    });
</script>
@endsection
