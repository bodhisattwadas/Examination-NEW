@extends('layouts.app')

@section('title', 'Exam Calendar')
@section('page_header', 'Exam Calendar')

@section('content')
<div class="row animated-fade-in">
    <div class="col-12">
        <div class="glass-panel p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="m-0"><i class="fa-solid fa-calendar-days text-indigo me-2"></i>Exam Calendar</h5>
                    <small class="text-muted">All scheduled exam dates and assigned staff</small>
                </div>
                <a href="{{ route('duty.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus me-1"></i> Create Exam Duty
                </a>
            </div>

            @if($examCalendar->isEmpty())
                <div class="alert alert-light text-center py-4">
                    <i class="fa-solid fa-calendar-xmark fs-3 text-muted d-block mb-2"></i>
                    No exam dates scheduled yet.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped align-middle datatable" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>Exam</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Default Hrs</th>
                                <th>Assigned Staff</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($examCalendar as $entry)
                                <tr>
                                    <td class="fw-semibold">{{ $entry['exam_name'] }}</td>
                                    <td>{{ \Carbon\Carbon::parse($entry['exam_date'])->format('d-m-Y') }}</td>
                                    <td><span class="small">{{ $entry['exam_time'] }}</span></td>
                                    <td class="text-center">{{ number_format($entry['default_hours'], 1) }}</td>
                                    <td>
                                        @if($entry['assigned_staff']->isNotEmpty())
                                            <div class="d-flex flex-column gap-1">
                                                @foreach($entry['assigned_staff'] as $staff)
                                                    <div>
                                                        <span class="fw-semibold">{{ $staff['staff_name'] }}</span>
                                                        @if($staff['staff_code'])
                                                            <span class="text-muted small">({{ $staff['staff_code'] }})</span>
                                                        @endif
                                                        <span class="ms-1">
                                                            @foreach($staff['roles'] as $role)
                                                                <span class="badge rounded-pill bg-light text-dark border border-secondary-subtle me-1">{{ $role }}</span>
                                                            @endforeach
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted small">No staff assigned</span>
                                        @endif
                                    </td>
                                    <td class="small text-secondary">{{ $entry['remarks'] ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection