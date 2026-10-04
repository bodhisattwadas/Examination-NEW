@extends('layouts.app')

@section('title', 'Exam Times Configuration')
@section('page_header', 'Exam Times Configuration')

@section('content')
<div class="row animated-fade-in">
    <div class="col-12 mb-4">
        <div class="glass-panel p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="m-0"><i class="fa-solid fa-clock text-indigo me-2"></i>Manage Exam Time Slots</h5>
                    <p class="text-secondary small m-0 mt-1">Add, edit or remove the time options that appear in duty entry dropdowns.</p>
                </div>
            </div>

            <!-- Add / Edit Form -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3">
                        <span id="form-mode-title">Add New Time Slot</span>
                    </h6>

                    <form id="time-form" action="{{ route('config.exam-times.store') }}" method="POST" class="row g-3">
                        @csrf

                        <div class="col-md-4">
                            <label class="form-label small text-secondary">Label (shown in dropdown)</label>
                            <input type="text" name="label" id="label" class="form-control" 
                                   value="{{ old('label') }}"
                                   placeholder="e.g. Morning (09:00 AM - 12:00 PM)" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small text-secondary">Value (stored in records)</label>
                            <input type="text" name="value" id="value" class="form-control" 
                                   value="{{ old('value') }}"
                                   placeholder="e.g. 09:00 AM - 12:00 PM" required>
                        </div>

                        <div class="col-md-1">
                            <label class="form-label small text-secondary">Order</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control" 
                                   value="{{ old('sort_order', 0) }}" min="0">
                        </div>

                        <div class="col-md-2">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active_check" 
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label small text-secondary" for="is_active_check">
                                    Active (show in dropdowns)
                                </label>
                            </div>
                        </div>

                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" id="submit-btn" class="btn btn-primary w-100">
                                <i class="fa-solid fa-plus me-1"></i>
                                <span id="submit-text">Add</span>
                            </button>

                            <button type="button" id="cancel-edit-btn" class="btn btn-secondary" style="display: none;">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Current Time Slots -->
            <h6 class="mb-3">Current Time Slots</h6>

            @if($examTimes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th style="width: 60px;">Order</th>
                                <th>Label</th>
                                <th>Value</th>
                                <th style="width: 140px;">Active</th>
                                <th class="text-end" style="width: 160px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($examTimes as $time)
                                <tr>
                                    <td><span class="badge bg-secondary">{{ $time->sort_order }}</span></td>
                                    <td><strong>{{ $time->label }}</strong></td>
                                    <td><code>{{ $time->value }}</code></td>
                                    <td>
                                        @if($time->is_active)
                                            <span class="badge-active"><i class="fa-solid fa-check me-1"></i> Active</span>
                                        @else
                                            <span class="badge-inactive">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button" 
                                                    class="btn btn-sm btn-secondary edit-time-btn"
                                                    data-id="{{ $time->id }}"
                                                    data-label="{{ $time->label }}"
                                                    data-value="{{ $time->value }}"
                                                    data-sort_order="{{ $time->sort_order }}"
                                                    data-is_active="{{ $time->is_active ? '1' : '0' }}"
                                                    title="Edit">
                                                <i class="fa-solid fa-edit"></i>
                                            </button>

                                            <form action="{{ route('config.exam-times.toggle-active', $time->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-sm {{ $time->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}" 
                                                        title="{{ $time->is_active ? 'Deactivate (hide from dropdowns)' : 'Activate (show in dropdowns)' }}">
                                                    @if($time->is_active)
                                                        <i class="fa-solid fa-eye-slash"></i>
                                                    @else
                                                        <i class="fa-solid fa-eye"></i>
                                                    @endif
                                                </button>
                                            </form>

                                            <form action="{{ route('config.exam-times.destroy', $time->id) }}" method="POST" 
                                                  onsubmit="return confirm('Delete this time slot?')" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
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
                    <i class="fa-solid fa-clock fa-2x text-secondary mb-2 d-block"></i>
                    No exam time slots configured yet.<br>
                    Add your first one using the form above.
                </div>
            @endif

            <div class="mt-3 text-muted small">
                <strong>Note:</strong> This is the single configuration for all Exam Time options. 
                Active slots populate the dropdowns in "Assign Duty", duty editing, and report filters.
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('time-form');
        const submitBtn = document.getElementById('submit-btn');
        const submitText = document.getElementById('submit-text');
        const modeTitle = document.getElementById('form-mode-title');
        const cancelBtn = document.getElementById('cancel-edit-btn');

        const storeUrl = "{{ route('config.exam-times.store') }}";

        // Handle edit buttons in the table
        document.querySelectorAll('.edit-time-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const label = this.dataset.label;
                const value = this.dataset.value;
                const sortOrder = this.dataset.sort_order;
                const isActive = this.dataset.is_active === '1';

                // Populate the single form
                form.querySelector('#label').value = label;
                form.querySelector('#value').value = value;
                form.querySelector('#sort_order').value = sortOrder;
                form.querySelector('#is_active_check').checked = isActive;

                // Switch to edit mode: change action + method
                form.action = "{{ url('config/exam-times') }}/" + id;
                
                // Remove any existing _method
                let methodInput = form.querySelector('input[name="_method"]');
                if (methodInput) methodInput.remove();
                
                // Add PUT method for update
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PUT';
                form.appendChild(methodInput);

                // Update UI
                modeTitle.textContent = 'Edit Time Slot';
                submitText.textContent = 'Update';
                submitBtn.querySelector('i').className = 'fa-solid fa-save me-1';
                cancelBtn.style.display = 'inline-block';

                // Focus label
                form.querySelector('#label').focus();
            });
        });

        // Cancel edit - reset to add mode
        cancelBtn.addEventListener('click', function() {
            form.action = storeUrl;
            
            // Remove _method if present
            let methodInput = form.querySelector('input[name="_method"]');
            if (methodInput) methodInput.remove();

            // Clear fields
            form.querySelector('#label').value = '';
            form.querySelector('#value').value = '';
            form.querySelector('#sort_order').value = '0';
            form.querySelector('#is_active_check').checked = true;

            // Reset UI
            modeTitle.textContent = 'Add New Time Slot';
            submitText.textContent = 'Add';
            submitBtn.querySelector('i').className = 'fa-solid fa-plus me-1';
            cancelBtn.style.display = 'none';
        });
    });
</script>
@endsection