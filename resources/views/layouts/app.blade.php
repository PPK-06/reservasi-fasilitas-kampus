<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Fasilitas Kampus')</title>

    {{-- Bootstrap 5.3 CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: #f5f6f8;
            color: #1a1d23;
        }

        /*Navbar background*/
        .navbar-campus { background-color: #1a202c; }

        /*Divider vertikal brand*/
        .brand-divider {
            width: 1.5px;
            height: 24px;
            background: rgba(255,255,255,0.25);
        }

        /*Nav link hover/active state*/
        .navbar-campus .nav-link {
            color: rgba(255,255,255,0.8) !important;
            font-weight: 600;
            font-size: 0.88rem;
            padding: 0.4rem 0.85rem;
            border-radius: 6px;
            transition: background 0.15s, color 0.15s;
        }
        .navbar-campus .nav-link:hover,
        .navbar-campus .nav-link.active {
            color: #ffffff !important;
            background: rgba(255,255,255,0.1);
        }

        /*Flash messages*/
        .flash-wrapper {
            position: sticky;
            top: 56px;
            z-index: 1020;
        }

        /*Page header*/
        .page-header {
            background: #ffffff;
            border-bottom: 1px solid #e3e6ed;
            padding: 1.25rem 0;
        }

        /*Footer*/
        footer {
            font-size: 0.85rem;
            color: #8f95a3;
            border-top: 1px solid #e3e6ed;
            background-color: #ffffff;
        }
    </style>
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

{{-- ════════════════════════════════════════════
     NAVBAR — Corporate Style
     ════════════════════════════════════════════ --}}
<nav class="navbar navbar-expand-lg navbar-campus sticky-top py-2 shadow-sm">
    <div class="container">

        {{--KIRI: Branding--}}
        <a class="navbar-brand d-flex align-items-center gap-2 me-4 text-decoration-none"
           href="{{ route('facilities.index') }}">
            {{-- Sub-teks --}}
            <span class="d-none d-md-block text-white-50" style="font-size:0.72rem; line-height:1.25;">
                Sistem Reservasi<br>&amp; Pelaporan
            </span>
            {{-- Divider --}}
            <span class="brand-divider d-none d-md-block"></span>
            {{-- Nama utama --}}
            <span class="d-flex align-items-center gap-2 text-white fw-bold" style="font-size:1.05rem; letter-spacing:0.3px;">
                <i class="bi bi-building" style="font-size:1.1rem;"></i> Fasilitas
            </span>
        </a>

        {{-- Toggler Mobile --}}
        <button class="navbar-toggler border-0" type="button"
                data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list text-white fs-4"></i>
        </button>

        {{--KANAN: Menu + Tombol--}}
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1 mt-3 mt-lg-0">

                {{--GUEST--}}
                @guest
                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <a href="{{ route('login') }}"
                           class="btn btn-outline-light rounded-pill px-4 fw-semibold"
                           style="font-size:0.83rem;">
                            Login
                        </a>
                    </li>
                @endguest

                {{--AUTH--}}
                @auth
                    @php $role = auth()->user()->role; @endphp

                    @if ($role === 'pengguna')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('facilities.*') ? 'active' : '' }}"
                               href="{{ route('facilities.index') }}">Fasilitas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reservations.create') ? 'active' : '' }}"
                               href="{{ route('reservations.create') }}">Reservasi</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reservations.index') ? 'active' : '' }}"
                               href="{{ route('reservations.index') }}">Riwayat</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"
                               href="{{ route('reports.index') }}">Laporan</a>
                        </li>

                    @elseif ($role === 'petugas')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('officer.dashboard') ? 'active' : '' }}"
                               href="{{ route('officer.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('officer.reservations.*') ? 'active' : '' }}"
                               href="{{ route('officer.reservations.index') }}">Antrian</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('officer.reports.*') ? 'active' : '' }}"
                               href="{{ route('officer.reports.index') }}">Laporan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('officer.facilities.*') ? 'active' : '' }}"
                               href="{{ route('officer.facilities.index') }}">Ketersediaan</a>
                        </li>

                    @elseif ($role === 'admin')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.facilities.*') ? 'active' : '' }}"
                               href="{{ route('admin.facilities.index') }}">Master Fasilitas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                               href="{{ route('admin.users.index') }}">Master Akun</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.recap.*') ? 'active' : '' }}"
                               href="{{ route('admin.recap.index') }}">Rekap Data</a>
                        </li>
                    @endif

                    {{-- User badge + Logout pill --}}
                    <li class="nav-item d-flex align-items-center gap-2 ms-lg-3 mt-3 mt-lg-0">
                        <span class="text-white-50 d-none d-lg-inline" style="font-size:0.82rem;">
                            <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
                        </span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="btn btn-outline-light rounded-pill px-4 fw-semibold"
                                    style="font-size:0.83rem;">
                                Logout
                            </button>
                        </form>
                    </li>
                @endauth

            </ul>
        </div>
    </div>
</nav>

{{-- ════════════════════════════════════════════
     FLASH MESSAGES (global — dari controller)
     ════════════════════════════════════════════ --}}
<div class="flash-wrapper">
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-0 rounded-0 border-0 border-start border-4 border-success" role="alert">
        <div class="container d-flex align-items-center">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0 border-0 border-start border-4 border-danger" role="alert">
        <div class="container d-flex align-items-center">
            <i class="bi bi-x-circle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    @if (session('warning'))
    <div class="alert alert-warning alert-dismissible fade show mb-0 rounded-0 border-0 border-start border-4 border-warning" role="alert">
        <div class="container d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif
</div>

{{-- ════════════════════════════════════════════
     PAGE HEADER (opsional — yield dari view anak)
     ════════════════════════════════════════════ --}}
@hasSection('page-title')
<div class="page-header shadow-sm">
    <div class="container d-flex align-items-center justify-content-between">
        <h4 class="mb-0 fw-bold" style="color: #4a5568;">@yield('page-title')</h4>
        <div>@yield('page-actions')</div>
    </div>
</div>
@endif

{{-- ════════════════════════════════════════════
     MAIN CONTENT
     ════════════════════════════════════════════ --}}
<main class="flex-grow-1">
    <div class="container py-4">
        @yield('content')
    </div>
</main>

{{-- ════════════════════════════════════════════
     FOOTER
     ════════════════════════════════════════════ --}}
<footer class="py-4 mt-auto">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
        <div class="fw-bold" style="color: #8c979d;">FASILITAS KAMPUS</div>
        <div>© {{ date('Y') }} — Dikembangkan oleh Tim PPK 06</div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>