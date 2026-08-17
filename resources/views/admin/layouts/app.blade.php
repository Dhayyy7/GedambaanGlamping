@php
    $appSetting = $appSetting ?? \App\Models\Setting::getSetting();
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Admin Dashboard') - {{ $appSetting->homestay_name ?? 'Gedambaan Glamping' }}</title>

    <!-- Favicon Icon Tab Web -->
    @if(!empty($appSetting->logo) && file_exists(public_path(ltrim($appSetting->logo, '/'))))
        <link rel="icon" type="image/x-icon" href="/{{ ltrim($appSetting->logo, '/') }}">
        <link rel="shortcut icon" href="/{{ ltrim($appSetting->logo, '/') }}">
        <link rel="apple-touch-icon" href="/{{ ltrim($appSetting->logo, '/') }}">
    @endif

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- jQuery & Select2 CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        /* Select2 Custom Styles */
        .select2-container--default .select2-selection--single {
            height: 38px !important;
            border-radius: 10px !important;
            border: 1px solid #cbd5e1 !important;
            padding: 3px 6px !important;
            display: flex !important;
            align-items: center !important;
            font-size: 0.825rem !important;
            background-color: #ffffff !important;
            transition: all 0.2s ease !important;
        }

        .select2-container--default .select2-selection--single:focus,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #4f46e5 !important;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15) !important;
            outline: none !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1e293b !important;
            padding-left: 6px !important;
            padding-right: 20px !important;
            line-height: 36px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            right: 8px !important;
        }

        .select2-dropdown {
            border-radius: 10px !important;
            border: 1px solid #cbd5e1 !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
            font-size: 0.825rem !important;
            overflow: hidden !important;
            z-index: 9999 !important;
        }

        .select2-results__option {
            padding: 8px 12px !important;
            font-size: 0.825rem !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #4f46e5 !important;
            color: #ffffff !important;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #e0e7ff !important;
            color: #4338ca !important;
            font-weight: 600 !important;
        }

        :root {
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --sidebar-bg: #0f172a;
            --main-bg: #f8fafc;
            --card-bg: #ffffff;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--main-bg);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
        }

        /* Sidebar Navigation */
        .sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            color: #ffffff;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            transition: all 0.3s ease;
        }

        .sidebar-brand {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-brand-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: #ffffff;
        }

        .sidebar-brand-text {
            font-size: 1.15rem;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.02em;
        }

        .sidebar-menu {
            padding: 1.25rem 0.75rem;
            flex-grow: 1;
            overflow-y: auto;
        }

        .menu-category {
            font-size: 0.75rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.75rem 0.75rem 0.35rem 0.75rem;
            margin-top: 0.5rem;
        }

        .nav-item {
            list-style: none;
            margin-bottom: 0.25rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.75rem 1rem;
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            background-color: rgba(99, 102, 241, 0.15);
            color: #818cf8;
        }

        .nav-link.active {
            font-weight: 600;
        }

        .nav-link i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        /* Main Content Wrapper */
        .main-wrapper {
            margin-left: 260px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Top Header Navbar */
        .topbar {
            height: 70px;
            background-color: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .topbar-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.35rem 0.75rem;
            border-radius: 12px;
            background-color: #f1f5f9;
        }

        .avatar {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #6366f1, #3b82f6);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1.2;
        }

        .user-details {
            font-size: 0.75rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .badge-role {
            background-color: #e0e7ff;
            color: #4338ca;
            padding: 0.1rem 0.4rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.68rem;
            text-transform: uppercase;
        }

        .btn-logout {
            background-color: #fee2e2;
            color: #dc2626;
            border: none;
            padding: 0.55rem 1rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }

        .btn-logout:hover {
            background-color: #fca5a5;
            color: #991b1b;
        }

        /* Page Content Area */
        .content-area {
            padding: 2rem;
            flex-grow: 1;
        }

        /* Cards & Layout */
        .card {
            background-color: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Forms & Inputs */
        .form-group {
            margin-bottom: 1.15rem;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.4rem;
        }

        .form-input, .form-textarea, .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 0.9rem;
            outline: none;
            background-color: #ffffff;
            transition: border-color 0.2s;
        }

        .form-input:focus, .form-textarea:focus, .form-select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .btn-submit {
            background-color: #4f46e5;
            color: #ffffff;
            border: none;
            padding: 0.75rem 1.25rem;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background-color 0.2s;
        }

        .btn-submit:hover {
            background-color: #4338ca;
        }

        /* Data Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.9rem;
        }

        .data-table th {
            background-color: #f8fafc;
            padding: 0.875rem 1rem;
            font-weight: 600;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
        }

        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        /* Footer */
        .footer {
            padding: 1.25rem 2rem;
            border-top: 1px solid var(--border-color);
            background-color: var(--card-bg);
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        /* Mobile Sidebar & Toggle Styles */
        .sidebar-toggle-btn {
            display: none;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: var(--text-dark);
            width: 40px;
            height: 40px;
            border-radius: 10px;
            font-size: 1.1rem;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            margin-right: 0.75rem;
            transition: all 0.2s ease;
        }

        .sidebar-toggle-btn:hover {
            background: #e2e8f0;
            color: var(--primary);
        }

        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 95;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Responsive Breakpoints */
        @media (max-width: 992px) {
            .sidebar {
                left: -260px;
                box-shadow: 10px 0 30px rgba(0,0,0,0.25);
            }

            .sidebar.mobile-open {
                left: 0;
            }

            .main-wrapper {
                margin-left: 0;
            }

            .sidebar-toggle-btn {
                display: flex;
            }

            .topbar {
                padding: 0 1rem;
            }

            .content-area {
                padding: 1.25rem 1rem;
            }

            .user-info {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .topbar-title {
                font-size: 1.05rem;
            }

            .user-profile {
                padding: 0.25rem 0.5rem;
            }

            .btn-logout span {
                display: none;
            }

            .btn-logout {
                padding: 0.5rem;
            }

            .footer {
                padding: 1rem;
                font-size: 0.78rem;
            }
        }
    </style>
    @yield('styles')
</head>

<body>

    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        @php
            $appSetting = \App\Models\Setting::getSetting();
        @endphp
        <div class="sidebar-brand">
            @if(!empty($appSetting->logo) && file_exists(public_path(ltrim($appSetting->logo, '/'))))
                <div class="sidebar-brand-icon" style="background: #ffffff; padding: 3px; border: 1px solid rgba(255,255,255,0.2);">
                    <img src="/{{ ltrim($appSetting->logo, '/') }}" alt="Logo {{ $appSetting->homestay_name }}" style="width: 100%; height: 100%; object-fit: contain; border-radius: 6px;">
                </div>
            @else
                <div class="sidebar-brand-icon">
                    <i class="fa-solid fa-house-chimney"></i>
                </div>
            @endif
            <div class="sidebar-brand-text" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $appSetting->homestay_name ?? 'Gedambaan Glamping' }}</div>
        </div>

        <ul class="sidebar-menu">
            <div class="menu-category"> Utama </div>
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.expenses.index') }}" class="nav-link {{ request()->routeIs('admin.expenses.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-wallet"></i>
                    <span>Catatan Pengeluaran</span>
                </a>
            </li>

            <div class="menu-category"> Pengelolaan </div>
            @if(Auth::check() && Auth::user()->role_user === 'Super Admin')
            <li class="nav-item">
                <a href="{{ route('admin.rooms.index') }}" class="nav-link {{ request()->routeIs('admin.rooms.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-bed"></i>
                    <span>Kamar & Unit</span>
                </a>
            </li>
            @endif

            <li class="nav-item">
                <a href="{{ route('admin.rooms.details') }}" class="nav-link {{ request()->routeIs('admin.rooms.details') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-days"></i>
                    <span>Detail Kamar</span>
                </a>
            </li>

            @if(Auth::check() && Auth::user()->role_user === 'Super Admin')
            <li class="nav-item">
                <a href="{{ route('admin.facilities.index') }}" class="nav-link {{ request()->routeIs('admin.facilities.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-list-check"></i>
                    <span>Fasilitas</span>
                </a>
            </li>
            @endif

            <li class="nav-item">
                <a href="{{ route('admin.bookings.index') }}" class="nav-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Pemesanan</span>
                </a>
            </li>
            @if(Auth::check() && Auth::user()->role_user === 'Super Admin')
            <li class="nav-item">
                <a href="{{ route('admin.extra-facilities.index') }}" class="nav-link {{ request()->routeIs('admin.extra-facilities.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-square-plus"></i>
                    <span>Extra Fasilitas</span>
                </a>
            </li>
            @endif
            {{-- <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fa-solid fa-users"></i>
                    <span>Data Tamu</span>
                </a>
            </li> --}}

            <div class="menu-category"> Sistem </div>
            @if(Auth::check() && Auth::user()->role_user === 'Super Admin')
            <li class="nav-item">
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users-gear"></i>
                    <span>User</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-shield"></i>
                    <span>Role User</span>
                </a>
            </li>
            @endif
            @if(Auth::check() && Auth::user()->role_user === 'Super Admin')
            <li class="nav-item">
                <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-gear"></i>
                    <span>Pengaturan</span>
                </a>
            </li>
            @endif
        </ul>
    </aside>

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div class="sidebar-overlay" onclick="toggleMobileSidebar()"></div>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Top Navbar -->
        <header class="topbar">
            <div style="display: flex; align-items: center;">
                <button type="button" class="sidebar-toggle-btn" onclick="toggleMobileSidebar()" title="Buka Menu Sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h1 class="topbar-title">@yield('page_title', 'Dashboard Overview')</h1>
            </div>

            <div class="topbar-actions">
                <div class="user-profile">
                    <div class="avatar">
                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="user-info">
                        <span class="user-name">{{ Auth::user()->name ?? 'Admin User' }}</span>
                        <span class="user-details">
                            @ {{ Auth::user()->username ?? 'admin' }}
                            <span class="badge-role">{{ Auth::user()->role_user ?? 'Staff' }}</span>
                        </span>
                    </div>
                </div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-logout">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </header>

        <!-- Dynamic Content -->
        <main class="content-area">
            @if(session('success'))
            <div style="background-color: #dcfce7; color: #166534; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid #bbf7d0; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-circle-check"></i>
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div style="background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid #fca5a5; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-triangle-exclamation"></i>
                {{ session('error') }}
            </div>
            @endif

            @if($errors->any())
            <div style="background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid #fca5a5; font-size: 0.9rem;">
                <strong style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Terjadi Kesalahan:
                </strong>
                <ul style="margin-left: 1.5rem;">
                    @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="footer">
            &copy; {{ date('Y') }} {{ $appSetting->homestay_name ?? 'Gedambaan Glamping' }} - Panel Kontrol Admin.
        </footer>
    </div>

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function toggleMobileSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            if (sidebar && overlay) {
                sidebar.classList.toggle('mobile-open');
                overlay.classList.toggle('active');
            }
        }

        function confirmDelete(button, message = 'Apakah Anda yakin ingin menghapus data ini?') {
            const form = button.closest('form');
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="fa-solid fa-trash-can"></i> Ya, Hapus',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'swal2-rounded-popup'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        function confirmSubmit(form, message = 'Anda Yakin Akan Merubah Data ini?', title = 'Konfirmasi Perubahan') {
            Swal.fire({
                title: title,
                text: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="fa-solid fa-check"></i> Ya, Simpan',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'swal2-rounded-popup'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
            return false;
        }
    </script>

    @if(session('success'))
    <script>
        Swal.fire({
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            icon: 'success',
            confirmButtonColor: '#4f46e5',
            timer: 3500
        });
    </script>
    @endif

    @if(session('error'))
    <script>
        Swal.fire({
            title: 'Gagal!',
            text: "{{ session('error') }}",
            icon: 'error',
            confirmButtonColor: '#dc2626'
        });
    </script>
    @endif

    @yield('scripts')
</body>

</html>