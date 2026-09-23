@extends('layouts.app')

@section('title', 'Detail Akun — Sistem Fasilitas Kampus')

@section('page-title', 'Detail Akun')

@section('page-actions')
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar Akun
    </a>
@endsection

@section('content')
@php
    /**
     * Label dan warna badge sama persis dengan A3
     * (resources/views/admin/users/index.blade.php), supaya satu status tidak
     * pernah terlihat berbeda di dua halaman.
     */
    $statusLabels = [
        'pending' => 'Menunggu',
        'verified' => 'Terverifikasi',
        'rejected' => 'Ditolak',
        'suspended' => 'Dinonaktifkan',
    ];

    $statusBadges = [
        'pending' => 'text-bg-warning',
        'verified' => 'text-bg-success',
        'rejected' => 'text-bg-danger',
        'suspended' => 'text-bg-secondary',
    ];

    $roleLabels = [
        'pengguna' => 'Pengguna',
        'petugas' => 'Petugas',
        'admin' => 'Admin',
    ];

    // C4 — nullable karena role; petugas dan admin tidak punya keduanya.
    $userTypeLabels = [
        'mahasiswa' => 'Mahasiswa',
        'dosen' => 'Dosen',
        'staf' => 'Staf',
    ];

    /*
     * Matriks transisi C2, dengan pembagian aksi mengikuti bagian 11 dokumen
     * route: verify dan reject sama-sama hanya melayani `pending`. Pemulihan
     * akses akun `rejected` dan `suspended` adalah pekerjaan activate, yang
     * belum dikerjakan — jadi keduanya untuk sementara tanpa tombol aksi.
     *
     * Penjagaan yang sesungguhnya tetap ada di controller; tombol yang tidak
     * dirender bukan penjaga.
     */
    $bolehVerifikasi = $user->status === 'pending';
    $bolehTolak = $user->status === 'pending';
@endphp

<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark">{{ $user->name }}</h6>
            </div>

            <div class="card-body p-4">
                <dl class="row mb-0">
                    <dt class="col-sm-4 fw-semibold text-muted py-2">Nama</dt>
                    <dd class="col-sm-8 py-2">{{ $user->name }}</dd>

                    <dt class="col-sm-4 fw-semibold text-muted py-2">Email</dt>
                    <dd class="col-sm-8 py-2">{{ $user->email }}</dd>

                    <dt class="col-sm-4 fw-semibold text-muted py-2">Role</dt>
                    <dd class="col-sm-8 py-2">{{ $roleLabels[$user->role] ?? $user->role }}</dd>

                    <dt class="col-sm-4 fw-semibold text-muted py-2">Status</dt>
                    <dd class="col-sm-8 py-2">
                        <span class="badge {{ $statusBadges[$user->status] ?? 'text-bg-light text-dark border' }}">
                            {{ $statusLabels[$user->status] ?? $user->status }}
                        </span>
                    </dd>

                    {{-- Skema 6.1: keduanya nullable karena role, bukan karena opsional (C4) --}}
                    <dt class="col-sm-4 fw-semibold text-muted py-2">NIM/NIP</dt>
                    <dd class="col-sm-8 py-2">{{ $user->identity_number ?? '—' }}</dd>

                    <dt class="col-sm-4 fw-semibold text-muted py-2">Tipe Pengguna</dt>
                    <dd class="col-sm-8 py-2">
                        {{ $user->user_type ? ($userTypeLabels[$user->user_type] ?? $user->user_type) : '—' }}
                    </dd>

                    <dt class="col-sm-4 fw-semibold text-muted py-2">Terdaftar</dt>
                    <dd class="col-sm-8 py-2">{{ $user->created_at?->format('d M Y') ?? '—' }}</dd>
                </dl>
            </div>

            {{-- Tombol aksi mengikuti matriks transisi C2 (peta 2.4).
                 Suspend, Aktifkan, dan Reset Password belum dikerjakan di
                 potongan ini, jadi sengaja tidak dirender sama sekali. --}}
            <div class="card-footer bg-white border-top py-3">
                @if ($bolehVerifikasi || $bolehTolak)
                    <div class="d-flex flex-wrap gap-2">
                        @if ($bolehVerifikasi)
                            <form method="POST" action="{{ route('admin.users.verify', $user) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="bi bi-check-lg me-1"></i>Verifikasi
                                </button>
                            </form>
                        @endif

                        @if ($bolehTolak)
                            {{-- Dari `rejected` tidak ada jalan kembali ke `pending` (C2),
                                 jadi penolakan dikonfirmasi lebih dulu. --}}
                            <form method="POST" action="{{ route('admin.users.reject', $user) }}"
                                  onsubmit="return confirm('Tolak registrasi {{ $user->name }}? Akun yang ditolak tidak dapat dikembalikan ke status menunggu.');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-x-lg me-1"></i>Tolak
                                </button>
                            </form>
                        @endif
                    </div>
                @else
                    <p class="text-muted mb-0" style="font-size:0.88rem;">
                        <i class="bi bi-info-circle me-1"></i>
                        Tidak ada aksi yang tersedia untuk akun berstatus
                        {{ $statusLabels[$user->status] ?? $user->status }}.
                        @if (in_array($user->status, ['rejected', 'suspended'], true))
                            Pemulihan akses memakai aksi Aktifkan, yang belum tersedia.
                        @endif
                    </p>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
