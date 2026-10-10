<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Wiyata · Reservasi Fasilitas Kampus Undip')</title>

    {{-- Meta, Favicon & Brand Manifest --}}
    @include('partials.head-meta')

    {{-- Google Fonts: Plus Jakarta Sans & Outfit --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    {{-- Bootstrap 5.3 CDN & Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            /* Identitas Warna Undip */
            --undip-navy-dark: #07172C;
            --undip-navy: #0B2545;
            --undip-navy-light: #134074;
            --undip-blue-surface: #F0F4F8;

            --undip-gold: #C89B3C;
            --undip-gold-hover: #AF842A;
            --undip-gold-subtle: #FDF9EE;

            /* Netral & Kanvas */
            --canvas-bg: #F8FAFC;
            --card-bg: #FFFFFF;
            --border-subtle: #E2E8F0;
            --border-medium: #CBD5E1;
            --text-heading: #0F172A;
            --text-body: #334155;
            --text-muted: #64748B;

            /* Status Semantik (WCAG AA Compliant) */
            --status-approved-text: #065F46;
            --status-approved-bg: #D1FAE5;
            --status-approved-border: #A7F3D0;

            --status-pending-text: #92400E;
            --status-pending-bg: #FEF3C7;
            --status-pending-border: #FDE68A;

            --status-maintenance-text: #B45309;
            --status-maintenance-bg: #FFFBEB;
            --status-maintenance-border: #FCD34D;

            --status-rejected-text: #991B1B;
            --status-rejected-bg: #FEE2E2;
            --status-rejected-border: #FECACA;

            /* Elevasi & Radius */
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 14px;
            --shadow-subtle: 0 1px 3px rgba(11, 37, 69, 0.04);
            --shadow-card: 0 4px 14px rgba(11, 37, 69, 0.05), 0 1px 3px rgba(11, 37, 69, 0.03);
            --shadow-card-hover: 0 10px 24px rgba(11, 37, 69, 0.09), 0 2px 6px rgba(11, 37, 69, 0.04);
        }

        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }

        body {
            font-family: 'Outfit', system-ui, -apple-system, sans-serif;
            background-color: var(--canvas-bg);
            color: var(--text-body);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            padding-left: env(safe-area-inset-left);
            padding-right: env(safe-area-inset-right);
            padding-bottom: env(safe-area-inset-bottom);
        }

        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            color: var(--text-heading);
            letter-spacing: -0.015em;
        }

        /* Focus visible accessibility */
        a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible {
            outline: 2px solid var(--undip-gold) !important;
            outline-offset: 2px !important;
        }

        /* ════════════════════════════════════════════
           NAVBAR UNDIP
           ════════════════════════════════════════════ */
        .navbar-undip {
            background-color: var(--undip-navy);
            border-bottom: 2px solid var(--undip-gold);
            padding-top: 0.65rem;
            padding-bottom: 0.65rem;
            transition: all 0.2s ease-in-out;
        }

        .navbar-brand-logo {
            width: 44px;
            height: 44px;
            object-fit: contain;
            background: transparent;
            padding: 0;
            border-radius: 0;
        }

        .navbar-brand-text {
            line-height: 1.15;
        }

        .navbar-brand-univ {
            font-size: 0.68rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--undip-gold);
            font-weight: 700;
            display: block;
        }

        .navbar-brand-title {
            font-size: 1.02rem;
            color: #ffffff;
            font-weight: 700;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .navbar-undip .nav-link {
            color: rgba(255, 255, 255, 0.82) !important;
            font-weight: 500;
            font-size: 0.88rem;
            padding: 0.42rem 0.85rem !important;
            border-radius: var(--radius-sm);
            transition: all 0.15s ease-out;
            position: relative;
        }

        .navbar-undip .nav-link:hover {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.09);
        }

        .navbar-undip .nav-link.active {
            color: #ffffff !important;
            background-color: rgba(200, 155, 60, 0.2);
            font-weight: 600;
        }

        .navbar-undip .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 2px;
            left: 50%;
            transform: translateX(-50%);
            width: 18px;
            height: 2px;
            background-color: var(--undip-gold);
            border-radius: 2px;
        }

        /* ════════════════════════════════════════════
           TOMBOL & KONTROL FORM (Touch-Target 44px & iOS Auto-Zoom Guard)
           ════════════════════════════════════════════ */
        .btn-undip-primary {
            background-color: var(--undip-navy);
            border-color: var(--undip-navy);
            color: #ffffff;
            font-weight: 600;
            padding: 0.5rem 1.25rem;
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-sm);
            transition: all 0.15s ease-out;
        }

        .btn-undip-primary:hover, .btn-undip-primary:focus {
            background-color: var(--undip-navy-light);
            border-color: var(--undip-navy-light);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(11, 37, 69, 0.2);
        }

        .btn-undip-gold {
            background-color: var(--undip-gold);
            border-color: var(--undip-gold);
            color: var(--undip-navy-dark);
            font-weight: 700;
            padding: 0.5rem 1.25rem;
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-sm);
            transition: all 0.15s ease-out;
        }

        .btn-undip-gold:hover, .btn-undip-gold:focus {
            background-color: var(--undip-gold-hover);
            border-color: var(--undip-gold-hover);
            color: var(--undip-navy-dark);
            box-shadow: 0 4px 12px rgba(200, 155, 60, 0.3);
        }

        .btn-outline-undip {
            background-color: transparent;
            border: 1.5px solid var(--undip-navy);
            color: var(--undip-navy);
            font-weight: 600;
            padding: 0.45rem 1.15rem;
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-sm);
            transition: all 0.15s ease-out;
        }

        .btn-outline-undip:hover {
            background-color: var(--undip-navy);
            color: #ffffff;
        }

        .navbar-toggler {
            min-width: 44px;
            min-height: 44px;
            padding: 0.5rem;
        }

        .form-control, .form-select {
            border: 1px solid var(--border-medium);
            border-radius: var(--radius-sm);
            padding: 0.52rem 0.85rem;
            font-size: 1rem; /* 16px mencegah auto-zoom pada perangkat iOS */
            min-height: 44px;
            color: var(--text-heading);
            background-color: #ffffff;
            transition: border-color 0.15s ease-out, box-shadow 0.15s ease-out;
        }

        .form-select {
            padding-right: 2.25rem;
        }

        .form-select-sm {
            padding-right: 2rem;
        }

        .modal-dialog {
            max-width: min(92vw, 600px);
            margin: 1.25rem auto;
        }

        .dropdown-menu {
            max-width: 90vw;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--undip-navy);
            box-shadow: 0 0 0 3px rgba(11, 37, 69, 0.12);
        }

        /* ════════════════════════════════════════════
           KARTU & ELEMEN TAMPILAN
           ════════════════════════════════════════════ */
        .card-undip {
            background-color: var(--card-bg);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
            transition: transform 0.18s ease-out, box-shadow 0.18s ease-out;
        }

        .card-undip-interactive:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-card-hover);
            border-color: var(--border-medium);
        }

        /* ════════════════════════════════════════════
           BADGE STATUS SISTEM
           ════════════════════════════════════════════ */
        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.65rem;
            font-size: 0.78rem;
            font-weight: 600;
            border-radius: 6px;
            letter-spacing: 0.01em;
        }

        .badge-status-approved {
            background-color: var(--status-approved-bg);
            color: var(--status-approved-text);
            border: 1px solid var(--status-approved-border);
        }

        .badge-status-pending {
            background-color: var(--status-pending-bg);
            color: var(--status-pending-text);
            border: 1px solid var(--status-pending-border);
        }

        .badge-status-maintenance {
            background-color: var(--status-maintenance-bg);
            color: var(--status-maintenance-text);
            border: 1px solid var(--status-maintenance-border);
        }

        .badge-status-rejected {
            background-color: var(--status-rejected-bg);
            color: var(--status-rejected-text);
            border: 1px solid var(--status-rejected-border);
        }

        /* ════════════════════════════════════════════
           HEADER HALAMAN
           ════════════════════════════════════════════ */
        .undip-page-header {
            padding: 2rem 0 0;
            margin-bottom: 0;
        }

        .undip-page-title {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 0;
            color: var(--undip-navy);
        }

        /* ════════════════════════════════════════════
           FLASH NOTIFIKASI
           ════════════════════════════════════════════ */
        .flash-wrapper {
            position: sticky;
            top: 56px;
            z-index: 1030;
        }

        .alert-undip {
            border-radius: var(--radius-sm);
            border: none;
            border-left: 4px solid;
            box-shadow: var(--shadow-card);
        }

        /* ════════════════════════════════════════════
           FOOTER INSTITUSIONAL
           ════════════════════════════════════════════ */
        .footer-undip {
            background-color: #ffffff;
            border-top: 1px solid var(--border-subtle);
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .footer-logo-img {
            width: 28px;
            height: 28px;
            object-fit: contain;
        }
    </style>
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

{{-- ════════════════════════════════════════════
     NAVBAR UTAMA KAMPUS UNDIP
     ════════════════════════════════════════════ --}}
<nav class="navbar navbar-expand-lg navbar-undip sticky-top shadow-sm">
    <div class="container">

        {{-- KIRI: Identitas Brand Undip --}}
        <a class="navbar-brand d-flex align-items-center gap-3 text-decoration-none"
           href="{{ url('/') }}">
            <img src="{{ asset('images/landing/logo-undip.webp') }}"
                 alt="Logo Universitas Diponegoro"
                 class="navbar-brand-logo"
                 onerror="this.style.display='none'">
            <div class="navbar-brand-text">
                <span class="navbar-brand-univ">Universitas Diponegoro</span>
                <span class="navbar-brand-title">
                    Wiyata
                </span>
            </div>
        </a>

        {{-- Toggler Menu Ponsel --}}
        <button class="navbar-toggler border-0 text-white" type="button"
                data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Buka menu navigasi">
            <i class="bi bi-list fs-3"></i>
        </button>

        {{-- KANAN: Menu Navigasi Berdasarkan Peran Pengguna --}}
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1 mt-3 mt-lg-0">

                {{-- PENGUNJUNG PUBLIK (GUEST) --}}
                @guest
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('/') ? 'active' : '' }}"
                           href="{{ url('/') }}">
                            Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('facilities.*') ? 'active' : '' }}"
                           href="{{ route('facilities.index') }}">
                            Daftar Fasilitas
                        </a>
                    </li>
                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <a href="{{ route('login') }}"
                           class="btn btn-undip-gold btn-sm px-3 shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                        </a>
                    </li>
                @endguest

                {{-- PENGGUNA TEROTENTIKASI (AUTH) --}}
                @auth
                    @php $role = auth()->user()->role; @endphp

                    @if ($role === 'pengguna')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('/') ? 'active' : '' }}"
                               href="{{ url('/') }}">Beranda</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('facilities.*') ? 'active' : '' }}"
                               href="{{ route('facilities.index') }}">Fasilitas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reservations.create') ? 'active' : '' }}"
                               href="{{ route('reservations.create') }}">Reservasi</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reservations.index', 'reservations.show') ? 'active' : '' }}"
                               href="{{ route('reservations.index') }}">Riwayat</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reports.create') ? 'active' : '' }}"
                               href="{{ route('reports.create') }}">Lapor Kerusakan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reports.index', 'reports.show') ? 'active' : '' }}"
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

                    {{-- Profil & Tombol Keluar --}}
                    <li class="nav-item d-flex align-items-center gap-2 ms-lg-3 mt-3 mt-lg-0">
                        <span class="badge bg-white text-dark d-none d-lg-inline-flex align-items-center gap-1 py-2 px-3 fw-semibold border"
                              style="font-size:0.8rem;">
                            <i class="bi bi-person-circle text-primary"></i>
                            {{ auth()->user()->name }}
                        </span>
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit"
                                    class="btn btn-outline-light btn-sm px-3 fw-semibold rounded-2"
                                    style="font-size:0.82rem;">
                                <i class="bi bi-box-arrow-right me-1"></i> Logout
                            </button>
                        </form>
                    </li>
                @endauth

            </ul>
        </div>
    </div>
</nav>

{{-- ════════════════════════════════════════════
     FLASH NOTIFIKASI GLOBAL
     ════════════════════════════════════════════ --}}
<div class="flash-wrapper">
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-0 rounded-0 border-0 border-start border-4 border-success shadow-sm" role="alert">
        <div class="container d-flex align-items-center">
            <i class="bi bi-check-circle-fill fs-5 text-success me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0 border-0 border-start border-4 border-danger shadow-sm" role="alert">
        <div class="container d-flex align-items-center">
            <i class="bi bi-x-circle-fill fs-5 text-danger me-2"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    </div>
    @endif

    @if (session('warning'))
    <div class="alert alert-warning alert-dismissible fade show mb-0 rounded-0 border-0 border-start border-4 border-warning shadow-sm" role="alert">
        <div class="container d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-5 text-warning me-2"></i>
            <div>{{ session('warning') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    </div>
    @endif

    @if ($errors->any() && !request()->routeIs('admin.recap.*'))
    <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0 border-0 border-start border-4 border-danger shadow-sm" role="alert">
        <div class="container">
            <div class="d-flex align-items-start">
                <i class="bi bi-exclamation-octagon-fill fs-5 text-danger me-2 mt-1"></i>
                <div>
                    <div class="fw-semibold mb-1">Periksa kembali isian formulir berikut:</div>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- ════════════════════════════════════════════
     PAGE HEADER (Opsional: dipanggil via @section)
     ════════════════════════════════════════════ --}}
@hasSection('page-title')
<div class="undip-page-header">
    <div class="container d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h1 class="undip-page-title">@yield('page-title')</h1>
            @hasSection('page-subtitle')
                <p class="text-muted small mb-0 mt-1">@yield('page-subtitle')</p>
            @endif
        </div>
        <div class="d-flex align-items-center gap-2">
            @yield('page-actions')
        </div>
    </div>
</div>
@endif

{{-- ════════════════════════════════════════════
     KONTEN UTAMA
     ════════════════════════════════════════════ --}}
<main class="flex-grow-1">
    @hasSection('full-width-content')
        @yield('full-width-content')
    @else
        <div class="container py-4">
            @yield('content')
        </div>
    @endif
</main>

{{-- ════════════════════════════════════════════
     FOOTER INSTITUSIONAL KAMPUS
     ════════════════════════════════════════════ --}}
<footer class="footer-undip py-4 mt-auto">
    <div class="container">
        <div class="row g-4 align-items-center justify-content-between">
            <div class="col-md-7">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <img src="{{ asset('images/landing/logo-undip.webp') }}"
                         alt="Logo Undip"
                         class="footer-logo-img"
                         onerror="this.style.display='none'">
                    <span class="fw-bold text-dark font-heading">WIYATA · UNIVERSITAS DIPONEGORO</span>
                </div>
                <p class="mb-1 text-muted small">
                    Sistem Reservasi dan Pelaporan Fasilitas Kampus Tembalang.
                    Jl. Prof. Sudarto, S.H., Tembalang, Semarang, Jawa Tengah 50275.
                </p>
            </div>
            <div class="col-md-5 text-md-end">
                <div class="fw-semibold text-dark small mb-1">Tim Pengembang PPK 06</div>
                <div class="text-muted small">© {{ date('Y') }} Universitas Diponegoro. Hak cipta dilindungi.</div>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const flashAlerts = document.querySelectorAll('.flash-wrapper .alert');
        flashAlerts.forEach(function (alertEl) {
            setTimeout(function () {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
                if (bsAlert) {
                    bsAlert.close();
                }
            }, 5000);
        });
    });
</script>
@stack('scripts')
</body>
</html>
