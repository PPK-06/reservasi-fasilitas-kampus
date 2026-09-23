@extends('layouts.app')

@section('title', $facility->name . ' — Sistem Fasilitas Kampus')

@section('page-title', 'Detail Fasilitas')
@section('page-actions')
    <a href="{{ route('facilities.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar
    </a>
@endsection

@section('content')

@php
    $isActive   = $facility->status === 'active';
    $isMaint    = $facility->status === 'under_maintenance';
    $isInactive = $facility->status === 'inactive';
@endphp

{{-- ═══════════════════════════════════════════════════════
     BANNER D5 — Fasilitas dalam perbaikan / nonaktif
     ═══════════════════════════════════════════════════════ --}}
@if ($isMaint)
    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-tools fs-5 me-3"></i>
        <div>
            <strong>Fasilitas dalam perbaikan, reservasi baru ditutup.</strong>
            <div class="text-muted mt-1" style="font-size: 0.85rem;">
                Fasilitas ini sedang menjalani perawatan. Silakan cek kembali nanti.
            </div>
        </div>
    </div>
@elseif ($isInactive)
    <div class="alert alert-secondary border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-slash-circle fs-5 me-3"></i>
        <div>
            <strong>Fasilitas tidak tersedia.</strong>
            <div class="text-muted mt-1" style="font-size: 0.85rem;">
                Fasilitas ini sedang tidak aktif dan tidak menerima reservasi.
            </div>
        </div>
    </div>
@endif

<div class="row g-4">

    {{-- ═══════════════════════════════════════════════════════
         KOLOM KIRI — Informasi Fasilitas
         ═══════════════════════════════════════════════════════ --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-building me-2"></i>{{ $facility->name }}
                </h5>
                <span class="badge {{ $isActive ? 'text-bg-success' : ($isMaint ? 'text-bg-warning' : 'text-bg-secondary') }}"
                      style="font-size: 0.82rem;">
                    @if ($isMaint)
                        <i class="bi bi-tools me-1"></i>
                    @elseif ($isInactive)
                        <i class="bi bi-x-circle me-1"></i>
                    @else
                        <i class="bi bi-check-circle me-1"></i>
                    @endif
                    {{ $facility->statusLabel() }}
                </span>
            </div>

            <div class="card-body">
                {{-- Detail grid --}}
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background: #f8f9fa;">
                            <div class="d-flex align-items-center justify-content-center rounded-circle"
                                 style="width:42px; height:42px; background:#e8edf3; flex-shrink:0;">
                                <i class="bi bi-tag-fill text-primary"></i>
                            </div>
                            <div>
                                <div class="text-muted" style="font-size: 0.78rem; font-weight: 600;">TIPE</div>
                                <div class="fw-semibold">{{ $facility->type }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background: #f8f9fa;">
                            <div class="d-flex align-items-center justify-content-center rounded-circle"
                                 style="width:42px; height:42px; background:#e8edf3; flex-shrink:0;">
                                <i class="bi bi-geo-alt-fill text-primary"></i>
                            </div>
                            <div>
                                <div class="text-muted" style="font-size: 0.78rem; font-weight: 600;">LOKASI</div>
                                <div class="fw-semibold">{{ $facility->location }}</div>
                            </div>
                        </div>
                    </div>

                    @if ($facility->type !== 'Alat' && !is_null($facility->capacity))
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background: #f8f9fa;">
                            <div class="d-flex align-items-center justify-content-center rounded-circle"
                                 style="width:42px; height:42px; background:#e8edf3; flex-shrink:0;">
                                <i class="bi bi-people-fill text-primary"></i>
                            </div>
                            <div>
                                <div class="text-muted" style="font-size: 0.78rem; font-weight: 600;">KAPASITAS</div>
                                <div class="fw-semibold">{{ $facility->capacity }} orang</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background: #f8f9fa;">
                            <div class="d-flex align-items-center justify-content-center rounded-circle"
                                 style="width:42px; height:42px; background:{{ $isActive ? '#d4edda' : ($isMaint ? '#fff3cd' : '#e2e3e5') }}; flex-shrink:0;">
                                @if ($isActive)
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                @elseif ($isMaint)
                                    <i class="bi bi-tools text-warning"></i>
                                @else
                                    <i class="bi bi-x-circle-fill text-secondary"></i>
                                @endif
                            </div>
                            <div>
                                <div class="text-muted" style="font-size: 0.78rem; font-weight: 600;">STATUS</div>
                                <div class="fw-semibold">{{ $facility->statusLabel() }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Deskripsi --}}
                @if ($facility->description)
                    <div class="mb-0">
                        <h6 class="fw-bold text-dark mb-2">
                            <i class="bi bi-card-text me-1"></i>Deskripsi
                        </h6>
                        <p class="text-muted mb-0" style="line-height: 1.7;">
                            {{ $facility->description }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════
             PLACEHOLDER GRID KETERSEDIAAN
             Menunggu class Slot & query ketersediaan dari M3 (Dhimas)
             ═══════════════════════════════════════════════════════ --}}
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-calendar3 me-2"></i>Ketersediaan Jadwal
                </h6>
            </div>
            <div class="card-body text-center py-5">
                <i class="bi bi-calendar2-week fs-1 text-muted d-block mb-3"></i>
                <h6 class="fw-bold text-muted">Grid ketersediaan akan segera tersedia</h6>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">
                    Fitur ini sedang dalam pengembangan dan akan ditambahkan pada integrasi berikutnya.
                </p>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         KOLOM KANAN — Tombol Aksi + Info Ringkas
         ═══════════════════════════════════════════════════════ --}}
    <div class="col-lg-4">
        {{-- Tombol aksi — hanya untuk pengguna yang login sebagai 'pengguna' --}}
        @auth
            @if (auth()->user()->role === 'pengguna')
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-3">
                            <i class="bi bi-lightning me-1"></i>Aksi
                        </h6>

                        <a href="{{ route('reservations.create', ['facility' => $facility->id]) }}"
                           class="btn w-100 mb-2 {{ $isActive ? 'btn-primary' : 'btn-secondary disabled' }}">
                            <i class="bi bi-calendar-plus me-1"></i>Ajukan Reservasi
                        </a>

                        <a href="{{ route('reports.create', ['facility' => $facility->id]) }}"
                           class="btn btn-outline-danger w-100">
                            <i class="bi bi-exclamation-triangle me-1"></i>Laporkan Kerusakan
                        </a>

                        @if (!$isActive)
                            <div class="mt-3">
                                <small class="text-muted fst-italic">
                                    <i class="bi bi-info-circle me-1"></i>Reservasi tidak tersedia karena fasilitas
                                    {{ $isMaint ? 'sedang dalam perbaikan' : 'tidak aktif' }}.
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        @endauth

        @guest
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center py-4">
                    <i class="bi bi-person-circle fs-2 text-muted d-block mb-2"></i>
                    <p class="text-muted mb-3" style="font-size: 0.85rem;">
                        Silakan login untuk mengajukan reservasi atau melaporkan kerusakan.
                    </p>
                    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm px-4">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Login
                    </a>
                </div>
            </div>
        @endguest

        {{-- Info ringkas --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold text-dark mb-3">
                    <i class="bi bi-info-circle me-1"></i>Informasi
                </h6>
                <ul class="list-unstyled mb-0" style="font-size: 0.85rem;">
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Tipe</span>
                        <span class="fw-semibold">{{ $facility->type }}</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Lokasi</span>
                        <span class="fw-semibold">{{ $facility->location }}</span>
                    </li>
                    @if ($facility->type !== 'Alat' && !is_null($facility->capacity))
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Kapasitas</span>
                        <span class="fw-semibold">{{ $facility->capacity }} orang</span>
                    </li>
                    @endif
                    <li class="d-flex justify-content-between py-2">
                        <span class="text-muted">Status</span>
                        <span class="badge {{ $isActive ? 'text-bg-success' : ($isMaint ? 'text-bg-warning' : 'text-bg-secondary') }}">
                            {{ $facility->statusLabel() }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

</div>

@endsection
