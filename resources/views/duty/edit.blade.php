@extends('layouts.app')

@section('title', 'Edit Exam Duty Hours')
@section('page_header', 'Edit Exam Duty Hours')

@section('content')
<div class="row animated-fade-in">
    <div class="col-12 mb-4">
        <div class="glass-panel p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary border-opacity-10 pb-3">
                <div>
                    <h5 class="m-0"><i class="fa-solid fa-pen-to-square text-indigo me-2"></i>Edit Duty Hours: {{ $duty->exam_name }}</h5>
                    <p class="text-secondary small m-0 mt-1">Update duty hours, OIC duties, and secretary assignments for this exam</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('duty.export-excel', $duty) }}" class="btn btn-outline-success btn-sm">
                        <i class="fa-solid fa-file-excel me-1"></i> Download Excel
                    </a>
                    <a href="{{ route('duty.export-pdf', $duty) }}" class="btn btn-outline-danger btn-sm">
                        <i class="fa-solid fa-file-pdf me-1"></i> Download PDF
                    </a>
                    <a href="{{ route('duty.index') }}" class="btn btn-secondary border-secondary border-opacity-25 btn-sm">
                        <i class="fa-solid fa-arrow-left me-1"></i> Exam List
                    </a>
                </div>
            </div>

            <form action="{{ route('duty.update', $duty) }}" method="POST" id="duty-form">
                @csrf
                @method('PUT')

                <!-- Exam General Parameters -->
                <div class="row g-3 mb-4">
                    <div class="col-md-5">
                        <label class="form-label text-secondary small fw-semibold">Exam Name <span class="text-danger">*</span></label>
                        <input type="text" name="exam_name" id="exam_name" class="form-control form-control-lg @error('exam_name') is-invalid @enderror" value="{{ old('exam_name', $duty->exam_name) }}" placeholder="e.g. End Semester Exam Nov 2026" required>
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
                            <input type="text" name="exam_date_range" id="exam_date_range" class="form-control form-control-lg border-start-0 bg-white" placeholder="Select date or range..." value="{{ old('exam_date_range', $duty->date_range_picker_value) }}" readonly style="cursor: pointer;">
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
                        <input type="text" name="remarks" id="remarks" class="form-control form-control-lg" value="{{ old('remarks', $duty->remarks) }}" placeholder="Special instructions / notes (optional)">
                    </div>
                </div>

                <!-- Guidance Info Box -->
                <div class="alert alert-info border-0 rounded-3 py-2 small mb-4" style="background:#f0f9ff; color:#0369a1; border-left: 4px solid #0284c7 !important;">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    <strong>Entry Rules:</strong>
                    Teachers can have <strong>Regular Hourly Duty</strong> + <strong>Total OIC Duty (count)</strong> + <strong>Secretary Duty</strong>.
                    Non-teaching staff can have <strong>Hourly Duty only</strong>.
                    Setting hours and OIC to 0 removes that staff assignment from this exam.
                </div>

                <!-- Staff Table Container -->
                <div class="border-top border-secondary border-opacity-10 pt-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="m-0"><i class="fa-solid fa-users text-indigo me-2"></i>Staff Duty Hours</h5>
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
                                        <div class="small text-muted fw-normal" style="font-size: 0.75rem;">(Teachers only)</div>
                                    </th>
                                    <th style="width: 16%;" class="text-center">
                                        <i class="fa-solid fa-user-shield text-danger me-1"></i> Exam Secretary?
                                        <div class="small text-muted fw-normal" style="font-size: 0.75rem;">(Teachers only)</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($staffs as $staff)
                                    @php
                                        $assignment = $currentAssignments->get($staff->id);
                                        $hoursVal = old('duties.' . $staff->id . '.duty_hours', $assignment ? (float)$assignment->duty_hours : '');
                                        $oicVal = old('duties.' . $staff->id . '.oic_count', $assignment ? (int)$assignment->oic_count : '');
                                        $isSecVal = old('duties.' . $staff->id . '.is_exam_secretary', $assignment ? $assignment->is_exam_secretary : false);
                                    @endphp
                                    <tr class="staff-row" data-type="{{ $staff->staff_type }}">
                                        <td class="text-center text-muted small">{{ $loop->iteration }}</td>
                                        <td class="fw-semibold text-dark">{{ $staff->name }}</td>
                                        <td>
                                            @if($staff->staff_type == 'Teaching')
                                                <span class="badge rounded bg-success bg-opacity-10 text-success border border-success border-opacity-20"><i class="fa-solid fa-chalkboard-user me-1"></i> Teaching</span>
                                            @else
                                                <span class="badge rounded bg-info bg-opacity-10 text-info border border-info border-opacity-20"><i class="fa-solid fa-user-gear me-1"></i> Non-teaching</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm justify-content-center">
                                                <input type="number" 
                                                       name="duties[{{ $staff->id }}][duty_hours]" 
                                                       value="{{ $hoursVal !== '' && $hoursVal > 0 ? $hoursVal : '' }}" 
                                                       step="0.5" min="0" max="500" 
                                                       placeholder="0"
                                                       class="form-control text-center duty-hours-input"
                                                       style="max-width: 100px;">
                                                <span class="input-group-text text-secondary small">hrs</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($staff->staff_type == 'Teaching')
                                                <div class="input-group input-group-sm justify-content-center">
                                                    <input type="number" 
                                                           name="duties[{{ $staff->id }}][oic_count]" 
                                                           value="{{ $oicVal !== '' && $oicVal > 0 ? $oicVal : '' }}" 
                                                           step="1" min="0" max="50" 
                                                           placeholder="0"
                                                           class="form-control text-center oic-input"
                                                           style="max-width: 90px;">
                                                    <span class="input-group-text text-secondary small">duties</span>
                                                </div>
                                            @else
                                                <span class="badge bg-light text-muted border px-2 py-1">N/A</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($staff->staff_type == 'Teaching')
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input secretary-switch" 
                                                           type="checkbox" 
                                                           name="duties[{{ $staff->id }}][is_exam_secretary]" 
                                                           value="1" 
                                                           id="sec-{{ $staff->id }}" 
                                                           {{ $isSecVal ? 'checked' : '' }}
                                                           style="cursor: pointer; transform: scale(1.2);">
                                                    <label class="form-check-label small ms-1" for="sec-{{ $staff->id }}">Secretary</label>
                                                </div>
                                            @else
                                                <span class="badge bg-light text-muted border px-2 py-1">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                    <div class="text-secondary small">
                        <i class="fa-solid fa-check text-success me-1"></i> Saving will update the annual matrix calculation totals.
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" name="action" value="save_and_resume" class="btn btn-outline-primary px-3">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save & Continue Editing
                        </button>
                        <button type="submit" name="action" value="save_and_exit" class="btn btn-primary px-4">
                            <i class="fa-solid fa-check me-1"></i> Save Changes & Exit
                        </button>
                        <a href="{{ route('duty.index') }}" class="btn btn-secondary border-secondary border-opacity-25">Cancel</a>
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
                "scrollY": "500px",
                "scrollCollapse": true,
                "order": [],  // keep server ordering
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
                table.column(2).search('').draw();
            } else {
                table.column(2).search(filter).draw();
            }
        });

        // Ensure all rows are in DOM when submitting form so filtered rows aren't dropped
        $('form').on('submit', function() {
            table.search('').columns().search('').draw();
        });

        // Initialize Range Datepicker with existing dates
        var fp = flatpickr("#exam_date_range", {
            mode: "range",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d M Y",
            conjunction: " to ",
            defaultDate: "{{ old('exam_date_range', $duty->date_range_picker_value) }}",
            allowInput: false
        });

        $('#btnClearDateRange').on('click', function() {
            fp.clear();
        });
    });
</script>
@endsection
