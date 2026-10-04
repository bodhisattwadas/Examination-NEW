<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Exam Duty Portal') - Examination Duty Management Portal</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome 6 for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    
    <!-- DataTables Bootstrap 5 CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Flatpickr Date Picker CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <!-- Custom Stylesheet -->
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    
    @yield('styles')
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar d-flex flex-column no-print">
        <div class="sidebar-logo">
            <span class="logo-text"><i class="fa-solid fa-graduation-cap me-2"></i>ExamDuty Portal</span>
        </div>
        <ul class="nav flex-column flex-grow-1">
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'dashboard' ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="fa-solid fa-chart-pie"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'staff.index' ? 'active' : '' }}" href="{{ route('staff.index') }}">
                    <i class="fa-solid fa-users"></i> Staff Directory
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('duty.*') ? 'active' : '' }}" href="{{ route('duty.index') }}">
                    <i class="fa-solid fa-calendar-check"></i> Exam Duties
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'report.index' ? 'active' : '' }}" href="{{ route('report.index') }}">
                    <i class="fa-solid fa-calculator"></i> Annual Duty Calculation
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'settings.password' ? 'active' : '' }}" href="{{ route('settings.password') }}">
                    <i class="fa-solid fa-gear"></i> Settings / Password
                </a>
            </li>
        </ul>
        <div class="p-3 text-center text-secondary border-top border-secondary border-opacity-10 mt-auto" style="font-size: 0.8rem;">
            @auth
            <div class="fw-semibold text-dark mb-1">{{ Auth::user()->name ?? 'Administrator' }}</div>
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger w-100 py-1" style="font-size: 0.78rem;">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                </button>
            </form>
            @else
            <div class="text-success fw-semibold"><i class="fa-solid fa-shield-halved me-1"></i> Examination Cell</div>
            @endauth
            <div class="mt-2 text-muted" style="font-size: 0.72rem;"><span>v1.0.0 &copy; {{ date('Y') }}</span></div>
        </div>
    </div>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
        
        <!-- Topbar -->
        <div class="topbar no-print animated-fade-in" style="position: relative; z-index: 1050;">
            <div class="topbar-title">
                <h4 class="m-0 font-weight-600">@yield('page_header', 'Dashboard')</h4>
            </div>
            <div class="topbar-actions d-flex align-items-center gap-3">
                <div class="dropdown" style="position: relative; z-index: 1055;">
                    <a href="#" class="d-flex align-items-center gap-2 text-decoration-none text-dark dropdown-toggle" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                        <div class="text-end d-none d-sm-block">
                            <div class="small fw-semibold text-dark">{{ Auth::user()->name ?? 'Administrator' }}</div>
                            <div class="text-secondary" style="font-size: 0.72rem;">{{ Auth::user()->email ?? 'admin@example.com' }}</div>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-secondary border-opacity-10 mt-2" aria-labelledby="userMenuDropdown" style="z-index: 1060;">
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('settings.password') }}">
                                <i class="fa-solid fa-key text-primary me-2"></i> Change Password
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 text-danger">
                                    <i class="fa-solid fa-right-from-bracket me-2"></i> Log Out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Session Status & Flash Messages -->
        <div class="container-fluid p-0 animated-fade-in">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 mb-4 shadow-sm" style="background: #ecfdf5; color: #166534; border-left: 4px solid #10b981;" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 mb-4 shadow-sm" style="background: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444;" role="alert">
                    <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(isset($errors) && $errors->any() && !session('preview_errors'))
                <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 mb-4 shadow-sm" style="background: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444;" role="alert">
                    <i class="fa-solid fa-circle-exclamation me-2"></i> <strong>Validation Errors:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Flatpickr Date Picker JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <script>
        $(document).ready(function() {
            // Apply standard DataTable styling if a data-table exists
            if ($('.datatable').length) {
                $('.datatable').each(function() {
                    if (!$.fn.DataTable.isDataTable(this)) {
                        $(this).DataTable({
                            "pageLength": 10,
                            "order": [],  // respect server-side ordering
                            "language": {
                                "search": "<i class='fa-solid fa-magnifying-glass text-secondary'></i>",
                                "searchPlaceholder": "Search records..."
                            }
                        });
                    }
                });
            }
        });
    </script>
    
    @yield('scripts')
</body>
</html>
