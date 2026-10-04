@extends('layouts.app')

@section('title', 'Import Preview')
@section('page_header', 'Staff Import Preview')

@section('content')
<div class="row animated-fade-in">
    <div class="col-12 mb-4">
        <div class="glass-panel p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary border-opacity-10 pb-3">
                <div>
                    <h5 class="m-0"><i class="fa-solid fa-list-check text-indigo me-2"></i>Review Validated Records</h5>
                    <p class="text-secondary small m-0 mt-1">Check row-wise errors. Only valid records can be saved.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('staff.upload') }}" class="btn btn-secondary border-secondary border-opacity-25">
                        <i class="fa-solid fa-rotate-left me-1"></i> Re-upload File
                    </a>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border border-secondary border-opacity-25 rounded-3">
                        <div class="card-body py-3 d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-secondary small">Total Processed Rows</div>
                                <div class="fs-3 fw-bold mt-1">{{ count($validRows) + count($invalidRows) }}</div>
                            </div>
                            <div class="metrics-icon bg-primary bg-opacity-10 text-primary rounded-circle" style="width: 42px; height: 42px;">
                                <i class="fa-solid fa-calculator"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3">
                        <div class="card-body py-3 d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-success small">Valid Rows (Will be imported)</div>
                                <div class="fs-3 fw-bold text-success mt-1">{{ count($validRows) }}</div>
                            </div>
                            <div class="metrics-icon bg-success bg-opacity-20 text-success rounded-circle" style="width: 42px; height: 42px;">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-3">
                        <div class="card-body py-3 d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-danger small">Invalid Rows (Will be skipped)</div>
                                <div class="fs-3 fw-bold text-danger mt-1">{{ count($invalidRows) }}</div>
                            </div>
                            <div class="metrics-icon bg-danger bg-opacity-20 text-danger rounded-circle" style="width: 42px; height: 42px;">
                                <i class="fa-solid fa-circle-xmark"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload format note -->
            <div class="alert alert-info border-0 rounded-3 py-2 small mb-4" style="background:#e0f2fe; color:#0369a1;">
                <i class="fa-solid fa-info-circle me-1"></i>
                <strong>Simplified import:</strong> Staff codes were auto-generated (Txxx for Teaching, NTxxx for Non-teaching). 
                All records are set to <strong>Active</strong>. Department, Mobile and Email are left blank — you can edit individual staff records later to fill them.
            </div>

            <!-- Import Action Form -->
            @if(count($validRows) > 0)
                <div class="p-3 bg-light border border-secondary border-opacity-25 rounded-3 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <span class="fw-semibold">Ready to import?</span>
                        <p class="text-secondary small m-0 mt-1">All database constraints are validated. Click the confirm button to finalize.</p>
                    </div>
                    <form action="{{ route('staff.import-commit') }}" method="POST">
                        @csrf
                        <!-- Use base64 so the JSON survives Blade escaping and form POST roundtrip reliably -->
                        <input type="hidden" name="valid_data" value="{{ base64_encode(json_encode($validRows)) }}">
                        <button type="submit" class="btn btn-success"><i class="fa-solid fa-cloud-arrow-up me-1"></i> Confirm Import ({{ count($validRows) }} Rows)</button>
                    </form>
                </div>
            @else
                <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-4" style="background: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444;">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i> No valid rows found in the uploaded file. Please fix the errors listed below and try uploading again.
                </div>
            @endif

            <!-- Invalid Rows Details -->
            @if(count($invalidRows) > 0)
                <div class="mb-5">
                    <h5 class="text-danger border-bottom border-danger border-opacity-25 pb-2 mb-3"><i class="fa-solid fa-triangle-exclamation me-2"></i>Row-wise Validation Errors ({{ count($invalidRows) }})</h5>
                    <div class="row g-3">
                        @foreach($invalidRows as $row)
                            <div class="col-12">
                                <div class="card error-card border border-danger border-opacity-20 rounded-3">
                                    <div class="card-body py-3">
                                        <div class="d-flex justify-content-between align-items-center border-bottom border-secondary border-opacity-10 pb-2 mb-2">
                                            <span class="badge bg-danger">Row {{ $row['row_num'] }}</span>
                                            <span class="text-secondary small">Code: <strong>{{ $row['staff_code'] ?: 'N/A' }}</strong> | Name: <strong>{{ $row['name'] ?: 'N/A' }}</strong></span>
                                        </div>
                                        <ul class="text-danger small mb-0 ps-3">
                                            @foreach($row['errors'] as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Valid Rows Preview Table -->
            @if(count($validRows) > 0)
                <div>
                    <h5 class="border-bottom border-secondary border-opacity-25 pb-2 mb-3"><i class="fa-solid fa-table-list text-indigo me-2"></i>Valid Rows Preview ({{ count($validRows) }})</h5>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">Row</th>
                                    <th>Staff Code <span class="text-secondary small">(auto)</span></th>
                                    <th>Staff Name</th>
                                    <th>Staff Type</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($validRows as $row)
                                    <tr>
                                        <td><span class="badge bg-secondary border border-secondary border-opacity-10">{{ $row['row_num'] }}</span></td>
                                        <td class="fw-semibold font-monospace">{{ $row['staff_code'] }}</td>
                                        <td>
                                            <span class="fw-semibold">{{ $row['name'] }}</span>
                                            @if(!empty($row['is_db_duplicate']))
                                                <span class="badge bg-warning bg-opacity-25 text-dark border border-warning border-opacity-50 ms-2 small">
                                                    <i class="fa-solid fa-triangle-exclamation text-warning me-1"></i> Already in Database
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($row['staff_type'] == 'Teaching')
                                                <span class="badge rounded bg-success bg-opacity-10 text-success border border-success border-opacity-20">{{ $row['staff_type'] }}</span>
                                            @else
                                                <span class="badge rounded bg-info bg-opacity-10 text-info border border-info border-opacity-20">{{ $row['staff_type'] }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($row['status'] == 'Active')
                                                <span class="badge-active">Active</span>
                                            @else
                                                <span class="badge-inactive">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="text-secondary small mt-2 mb-0">Other fields (Department, Mobile, Email) are empty and can be updated individually after import.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
