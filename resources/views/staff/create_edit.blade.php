@extends('layouts.app')

@php
    $isEdit = isset($staff);
@endphp

@section('title', $isEdit ? 'Edit Staff' : 'Add Staff')
@section('page_header', $isEdit ? 'Edit Staff Details' : 'Add New Staff')

@section('content')
<div class="row justify-content-center animated-fade-in">
    <div class="col-md-8 mb-4">
        <div class="glass-panel p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary border-opacity-10 pb-3">
                <div>
                    <h5 class="m-0"><i class="fa-solid {{ $isEdit ? 'fa-user-pen text-amber' : 'fa-user-plus text-indigo' }} me-2"></i>{{ $isEdit ? 'Update Staff Record' : 'Create Staff Record' }}</h5>
                    <p class="text-secondary small m-0 mt-1">Enter basic staff information for duty assignment</p>
                </div>
                <a href="{{ route('staff.index') }}" class="btn btn-secondary border-secondary border-opacity-25">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Directory
                </a>
            </div>

            <form action="{{ $isEdit ? route('staff.update', $staff->id) : route('staff.store') }}" method="POST">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <!-- Staff Name -->
                    <div class="col-12">
                        <label class="form-label text-secondary small fw-semibold">Staff Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $isEdit ? $staff->name : '') }}" placeholder="Full Name" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Staff Type -->
                    <div class="col-md-6">
                        <label class="form-label text-secondary small fw-semibold">Staff Type <span class="text-danger">*</span></label>
                        <select name="staff_type" class="form-select @error('staff_type') is-invalid @enderror" required>
                            <option value="">Select Type</option>
                            <option value="Teaching" {{ old('staff_type', $isEdit ? $staff->staff_type : '') == 'Teaching' ? 'selected' : '' }}>Teaching</option>
                            <option value="Non-teaching" {{ old('staff_type', $isEdit ? $staff->staff_type : '') == 'Non-teaching' ? 'selected' : '' }}>Non-teaching</option>
                        </select>
                        @error('staff_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="col-md-6">
                        <label class="form-label text-secondary small fw-semibold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="Active" {{ old('status', $isEdit ? $staff->status : 'Active') == 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ old('status', $isEdit ? $staff->status : '') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Save Staff Record</button>
                        <a href="{{ route('staff.index') }}" class="btn btn-secondary border-secondary border-opacity-25">Cancel</a>
                    </div>

                    @if($isEdit)
                        <button type="button" class="btn btn-outline-danger btn-sm"
                                onclick="if(confirm('Delete staff \'{{ addslashes($staff->name) }}\'? This will remove the staff record and any assigned duty records.')) { document.getElementById('delete-staff-form').submit(); }">
                            <i class="fa-solid fa-trash me-1"></i> Delete Staff
                        </button>
                    @endif
                </div>
            </form>

            @if($isEdit)
                <form id="delete-staff-form" action="{{ route('staff.destroy', $staff->id) }}" method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
