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
             GRID KETERSEDIAAN JADWAL (P2 / US 1 / F4 / D5)
             ═══════════════════════════════════════════════════════ --}}
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-calendar3 me-2"></i>Ketersediaan Jadwal
                </h6>
                <form method="GET" action="{{ route('facilities.show', $facility) }}" class="d-flex align-items-center gap-2">
                    <label for="p2-date" class="form-label small fw-semibold mb-0 text-muted">Tanggal:</label>
                    <input type="date"
                           name="date"
                           id="p2-date"
                           class="form-control form-control-sm"
                           value="{{ $selectedDate }}"
                           min="{{ now()->addDay()->toDateString() }}"
                           max="{{ now()->addDays(30)->toDateString() }}"
                           onchange="this.form.submit()">
                </form>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-2 border-bottom">
                    <div class="small text-muted">
                        <i class="bi bi-clock me-1"></i>Jam operasional: <strong>07:00 – 20:00</strong> (26 slot, @ 30 menit)
                    </div>
                    <div class="d-flex align-items-center gap-3" style="font-size: 0.8rem;">
                        <span class="d-flex align-items-center gap-1">
                            <span class="d-inline-block rounded border border-primary bg-white" style="width: 14px; height: 14px;"></span>
                            Tersedia
                        </span>
                        <span class="d-flex align-items-center gap-1">
                            <span class="d-inline-block rounded bg-secondary" style="width: 14px; height: 14px;"></span>
                            Tidak Tersedia
                        </span>
                    </div>
                </div>

                <div class="row row-cols-3 row-cols-sm-4 row-cols-md-6 g-2">
                    @foreach ($slots as $slot)
                        @php
                            $canBook = $slot['is_available'] && $isActive;
                        @endphp
                        <div class="col">
                            @if ($canBook)
                                <a href="{{ route('reservations.create', ['facility' => $facility->id, 'date' => $selectedDate, 'start_slot' => $slot['start']]) }}"
                                   class="btn btn-outline-primary w-100 py-2 text-decoration-none"
                                   style="font-size: 0.83rem; font-weight: 500;"
                                   title="Pesan slot {{ $slot['start'] }} - {{ $slot['end'] }}">
                                    <div class="fw-semibold">{{ $slot['start'] }}</div>
                                    <small class="d-block" style="font-size: 0.65rem;">Tersedia</small>
                                </a>
                            @else
                                <button type="button"
                                        class="btn btn-secondary w-100 py-2"
                                        disabled
                                        style="font-size: 0.83rem; font-weight: 500; opacity: 0.65;"
                                        title="Slot {{ $slot['start'] }} - {{ $slot['end'] }} tidak tersedia">
                                    <div class="fw-semibold">{{ $slot['start'] }}</div>
                                    <small class="d-block" style="font-size: 0.65rem;">
                                        {{ $slot['is_booked'] ? 'Terisi' : 'Tidak Tersedia' }}
                                    </small>
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if ($isActive)
                    <div class="text-muted mt-3 text-center" style="font-size: 0.8rem;">
                        <i class="bi bi-info-circle me-1"></i>Klik pada slot yang tersedia (biru outline) untuk langsung mengajukan reservasi.
                    </div>
                @endif
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
