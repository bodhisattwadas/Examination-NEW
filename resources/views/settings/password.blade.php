@extends('layouts.app')

@section('title', 'Admin Settings - Change Password')
@section('page_header', 'Account Settings')

@section('content')
<div class="row justify-content-center animated-fade-in">
    <div class="col-lg-7 col-md-9 mb-4">
        <!-- Account Info Card -->
        <div class="glass-panel p-4 mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; font-size: 1.5rem;">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold text-dark">{{ $user->name ?? 'Administrator' }}</h5>
                    <div class="text-secondary small">{{ $user->email }} &bull; <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Portal Admin</span></div>
                </div>
            </div>
        </div>

        <!-- Change Password Card -->
        <div class="glass-panel p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary border-opacity-10 pb-3">
                <div>
                    <h5 class="m-0"><i class="fa-solid fa-key text-primary me-2"></i>Change Admin Password</h5>
                    <p class="text-secondary small m-0 mt-1">Ensure your administrative account uses a strong, secure password</p>
                </div>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary border-secondary border-opacity-25 btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Dashboard
                </a>
            </div>

            <form action="{{ route('settings.password.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label text-secondary small fw-semibold">Current Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="Enter your current password" required autocomplete="current-password">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password', this)">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    @error('current_password')
                        <div class="text-danger small mt-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary small fw-semibold">New Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-key"></i></span>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimum 6 characters" required autocomplete="new-password">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password', this)">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="text-danger small mt-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label text-secondary small fw-semibold">Confirm New Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-check-double"></i></span>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Re-type new password" required autocomplete="new-password">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation', this)">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="alert alert-light border small text-secondary py-2 mb-4">
                    <i class="fa-solid fa-shield-halved text-success me-1"></i> After updating your password, use the new password for future logins.
                </div>

                <div class="d-flex justify-content-between align-items-center border-top border-secondary border-opacity-10 pt-3">
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary border-secondary border-opacity-25">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa-solid fa-save me-1"></i> Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function togglePassword(inputId, btn) {
        var input = document.getElementById(inputId);
        var icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection
