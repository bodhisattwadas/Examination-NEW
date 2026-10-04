<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Examination Duty Management Portal</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.35);
            max-width: 440px;
            width: 100%;
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: #ffffff;
            padding: 32px 24px;
            text-align: center;
        }
        .login-body {
            padding: 32px 28px;
        }
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.15);
        }
        .btn-primary {
            background-color: #1e3a8a;
            border-color: #1e3a8a;
            padding: 10px 16px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <div class="mb-2">
                <span class="rounded-circle bg-white bg-opacity-20 d-inline-flex align-items-center justify-content-center" style="width: 56px; height: 56px; font-size: 1.7rem;">
                    <i class="fa-solid fa-graduation-cap"></i>
                </span>
            </div>
            <h4 class="fw-bold mb-1">ExamDuty Portal</h4>
            <div class="small opacity-75">Examination Duty Management & Calculation</div>
        </div>

        <div class="login-body">
            @if(session('status'))
                <div class="alert alert-info py-2 small mb-3">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger py-2 small mb-3">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email Address -->
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-semibold">Admin Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', 'admin@example.com') }}" placeholder="admin@example.com" required autofocus autocomplete="username">
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" id="login-password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" required autocomplete="current-password">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility()">
                            <i class="fa-solid fa-eye" id="toggle-icon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="mb-4 form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember" checked>
                    <label class="form-check-label text-secondary small" for="remember">Keep me signed in</label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="fa-solid fa-right-to-bracket me-1"></i> Sign In to Portal
                </button>

                <!-- Default Credentials Helper Card -->
                <div class="bg-light p-2 rounded text-center border" style="font-size: 0.78rem;">
                    <span class="text-muted">Default Admin:</span> <strong class="text-dark">admin@example.com</strong> &bull;
                    <span class="text-muted">Password:</span> <strong class="text-dark">password</strong>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            var passInput = document.getElementById('login-password');
            var icon = document.getElementById('toggle-icon');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
