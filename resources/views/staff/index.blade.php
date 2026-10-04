@extends('layouts.app')

@section('title', 'Staff Directory')
@section('page_header', 'Staff Directory')

@section('content')
<div class="row animated-fade-in">
    <div class="col-12 mb-4">
        <div class="glass-panel p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h5 class="m-0"><i class="fa-solid fa-users text-indigo me-2"></i>Manage Staff Records</h5>
                    <p class="text-secondary small m-0 mt-1">Add, edit, toggle status, and import staff lists</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('staff.upload') }}" class="btn btn-secondary border-secondary border-opacity-25">
                        <i class="fa-solid fa-users-gear me-1"></i> Bulk Intake (CSV / XLS / Paste)
                    </a>
                    <a href="{{ route('staff.create') }}" class="btn btn-primary">
                        <i class="fa-solid fa-user-plus me-1"></i> Add Staff
                    </a>
                </div>
            </div>

            <!-- Filters form -->
            <form action="{{ route('staff.index') }}" method="GET" class="row g-3 mb-4 border border-secondary border-opacity-10 pb-4 rounded">
                <div class="col-md-3">
                    <label class="form-label text-secondary small">Staff Type</label>
                    <select name="staff_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="Teaching" {{ request('staff_type') == 'Teaching' ? 'selected' : '' }}>Teaching</option>
                        <option value="Non-teaching" {{ request('staff_type') == 'Non-teaching' ? 'selected' : '' }}>Non-teaching</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-secondary small">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ request('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                    @if(request()->anyFilled(['staff_type', 'status']))
                        <a href="{{ route('staff.index') }}" class="btn btn-secondary"><i class="fa-solid fa-rotate-left"></i></a>
                    @endif
                </div>
            </form>

            <!-- Staff List Table -->
            <div class="table-responsive">
                <table class="table table-striped datatable align-middle">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>Staff Name</th>
                            <th>Staff Type</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staffs as $staff)
                            <tr>
                                <td class="text-center small text-secondary" style="width: 50px;">{{ $loop->iteration }}</td>
                                <td>{{ $staff->name }}</td>
                                <td>
                                    @if($staff->staff_type == 'Teaching')
                                        <span class="badge rounded bg-success bg-opacity-10 text-success border border-success border-opacity-20">{{ $staff->staff_type }}</span>
                                    @else
                                        <span class="badge rounded bg-info bg-opacity-10 text-info border border-info border-opacity-20">{{ $staff->staff_type }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($staff->status == 'Active')
                                        <span class="badge-active"><i class="fa-solid fa-circle-check me-1"></i> Active</span>
                                    @else
                                        <span class="badge-inactive"><i class="fa-solid fa-circle-xmark me-1"></i> Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('staff.edit', $staff->id) }}" class="btn btn-sm btn-secondary" title="Edit Staff">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('staff.toggle-status', $staff->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('POST')
                                            <button type="submit" class="btn btn-sm {{ $staff->status == 'Active' ? 'btn-outline-danger' : 'btn-outline-success' }}" title="{{ $staff->status == 'Active' ? 'Deactivate' : 'Activate' }}">
                                                @if($staff->status == 'Active')
                                                    <i class="fa-solid fa-user-slash"></i>
                                                @else
                                                    <i class="fa-solid fa-user-check"></i>
                                                @endif
                                            </button>
                                        </form>

                                        <form action="{{ route('staff.destroy', $staff->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Delete staff \'{{ addslashes($staff->name) }}\'? This action will remove the staff record and any assigned duty records.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Staff">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <!-- Empty state will be handled by DataTables or standard fallback -->
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
