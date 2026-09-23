@extends('layouts.app')

@section('title', 'Ketersediaan Fasilitas — Sistem Fasilitas Kampus')

@section('page-title', 'Ketersediaan Fasilitas')

@section('content')

{{-- ═══════════════════════════════════════════════════════
     FILTER — tipe, lokasi, status, pencarian nama
     Dropdown diisi dari konstanta PHP (F6 / D1 / D2 / D3)
     ═══════════════════════════════════════════════════════ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('officer.facilities.index') }}" id="officerFilterForm">
            <div class="row g-2 align-items-end">

                {{-- Pencarian nama --}}
                <div class="col-lg-3 col-md-6">
                    <label for="filterSearch" class="form-label fw-semibold mb-1" style="font-size:0.82rem;">
                        <i class="bi bi-search me-1"></i>Cari nama
                    </label>
                    <input type="text" name="search" id="filterSearch"
                           class="form-control form-control-sm"
                           value="{{ request('search') }}"
                           placeholder="Nama fasilitas…">
                </div>

                {{-- Tipe — F6: loop Facility::TYPES --}}
                <div class="col-lg-2 col-md-6">
                    <label for="filterType" class="form-label fw-semibold mb-1" style="font-size:0.82rem;">
                        <i class="bi bi-tag me-1"></i>Tipe
                    </label>
                    <select name="type" id="filterType" class="form-select form-select-sm">
                        <option value="">Semua Tipe</option>
                        @foreach (\App\Models\Facility::TYPES as $value => $label)
                            <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Lokasi — F6: loop Facility::LOCATIONS --}}
                <div class="col-lg-2 col-md-6">
                    <label for="filterLocation" class="form-label fw-semibold mb-1" style="font-size:0.82rem;">
                        <i class="bi bi-geo-alt me-1"></i>Lokasi
                    </label>
                    <select name="location" id="filterLocation" class="form-select form-select-sm">
                        <option value="">Semua Lokasi</option>
                        @foreach (\App\Models\Facility::LOCATIONS as $value => $label)
                            <option value="{{ $value }}" {{ request('location') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status — D3: loop Facility::STATUSES --}}
                <div class="col-lg-2 col-md-6">
                    <label for="filterStatus" class="form-label fw-semibold mb-1" style="font-size:0.82rem;">
                        <i class="bi bi-circle-half me-1"></i>Status
                    </label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        @foreach (\App\Models\Facility::STATUSES as $value => $label)
                            <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tombol --}}
                <div class="col-lg-3 col-md-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    <a href="{{ route('officer.facilities.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     TABEL DAFTAR FASILITAS
     ═══════════════════════════════════════════════════════ --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-bold text-dark">
            <i class="bi bi-building me-1"></i>Daftar Ketersediaan Fasilitas
        </h6>
        <span class="text-muted" style="font-size: 0.82rem;">
            {{ $facilities->total() }} data
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background-color: #0d3880; color: #fff;">
                    <tr>
                        <th class="ps-3" style="width:45px">#</th>
                        <th>Nama</th>
                        <th>Tipe</th>
                        <th>Lokasi</th>
                        <th class="text-center">Kapasitas</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($facilities as $facility)
                        @php
                            $isActive   = $facility->status === 'active';
                            $isMaint    = $facility->status === 'under_maintenance';
                            $isInactive = $facility->status === 'inactive';
                            $upcoming   = $upcomingReservations->get($facility->id, collect());
                        @endphp
                        <tr>
                            {{-- # --}}
                            <td class="ps-3 text-muted">
                                {{ $facilities->firstItem() + $loop->index }}
                            </td>

                            {{-- Nama --}}
                            <td>
                                <span class="fw-semibold {{ $isInactive ? 'text-muted' : '' }}">
                                    {{ $facility->name }}
                                </span>
                                @if ($facility->description)
                                    <br>
                                    <small class="text-muted" style="font-size:0.78rem;">
                                        {{ Str::limit($facility->description, 50) }}
                                    </small>
                                @endif
                            </td>

                            {{-- Tipe --}}
                            <td>
                                <span class="text-muted" style="font-size:0.85rem;">{{ $facility->type }}</span>
                            </td>

                            {{-- Lokasi --}}
                            <td>
                                <span class="text-muted" style="font-size:0.85rem;">
                                    <i class="bi bi-geo-alt-fill me-1"></i>{{ $facility->location }}
                                </span>
                            </td>

                            {{-- Kapasitas --}}
                            <td class="text-center">
                                @if ($facility->type !== 'Alat' && !is_null($facility->capacity))
                                    <span style="font-size:0.85rem;">{{ $facility->capacity }}</span>
                                @else
                                    <span class="text-muted" style="font-size:0.78rem;">—</span>
                                @endif
                            </td>

                            {{-- Badge status (D3) --}}
                            <td class="text-center">
                                @if ($isActive)
                                    <span class="badge text-bg-success">
                                        <i class="bi bi-check-circle me-1"></i>Aktif
                                    </span>
                                @elseif ($isMaint)
                                    <span class="badge text-bg-warning">
                                        <i class="bi bi-tools me-1"></i>Dalam Perbaikan
                                    </span>
                                @else
                                    <span class="badge text-bg-secondary">
                                        <i class="bi bi-x-circle me-1"></i>Nonaktif
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="text-end pe-3">
                                <div class="d-flex gap-1 justify-content-end">
                                    {{-- Ubah status: active ↔ under_maintenance (D3, officer only) --}}
                                    @if ($isActive)
                                        {{-- Aktif → Dalam Perbaikan --}}
                                        <button type="button"
                                                class="btn btn-outline-warning btn-sm"
                                                title="Tandai Dalam Perbaikan"
                                                data-bs-toggle="modal"
                                                data-bs-target="#statusModal-{{ $facility->id }}"
                                                data-action="{{ route('officer.facilities.status', $facility) }}"
                                                data-name="{{ $facility->name }}"
                                                data-new-status="under_maintenance">
                                            <i class="bi bi-tools"></i> Perbaikan
                                        </button>
                                    @elseif ($isMaint)
                                        {{-- Dalam Perbaikan → Aktif --}}
                                        <button type="button"
                                                class="btn btn-outline-success btn-sm"
                                                title="Selesai Perbaikan (Aktifkan)"
                                                data-bs-toggle="modal"
                                                data-bs-target="#statusModal-{{ $facility->id }}"
                                                data-action="{{ route('officer.facilities.status', $facility) }}"
                                                data-name="{{ $facility->name }}"
                                                data-new-status="active">
                                            <i class="bi bi-check-circle"></i> Selesai
                                        </button>
                                    @else
                                        {{-- inactive: petugas tidak bisa kelola fasilitas nonaktif --}}
                                        <button type="button"
                                                class="btn btn-outline-secondary btn-sm"
                                                disabled
                                                title="Status 'Nonaktif' dikelola admin">
                                            <i class="bi bi-lock"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-building fs-1 d-block mb-2"></i>
                                <h6 class="fw-bold text-muted">Tidak ada fasilitas ditemukan</h6>
                                <p class="text-muted mb-0" style="font-size: 0.85rem;">
                                    Coba ubah filter atau
                                    <a href="{{ route('officer.facilities.index') }}" class="text-decoration-none">reset semua filter</a>.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($facilities->hasPages())
        <div class="card-footer bg-white border-top d-flex justify-content-end py-2">
            {{ $facilities->links() }}
        </div>
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════
     MODAL UBAH STATUS — satu modal per fasilitas
     D4: kalau fasilitas punya reservasi approved mendatang,
         modal menampilkan peringatan berisi daftar reservasi.
     ═══════════════════════════════════════════════════════ --}}
@foreach ($facilities as $facility)
    @php
        $upcoming = $upcomingReservations->get($facility->id, collect());
        $isActive = $facility->status === 'active';
        $isMaint  = $facility->status === 'under_maintenance';
    @endphp

    {{-- Hanya render modal untuk fasilitas yang punya tombol status (active / under_maintenance) --}}
    @if ($isActive || $isMaint)
    <div class="modal fade" id="statusModal-{{ $facility->id }}" tabindex="-1"
         aria-labelledby="statusModalLabel-{{ $facility->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered {{ $upcoming->isNotEmpty() && $isActive ? 'modal-lg' : '' }}">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="statusModalLabel-{{ $facility->id }}">
                        @if ($isActive)
                            <i class="bi bi-tools text-warning me-2"></i>Tandai Perbaikan Fasilitas
                        @else
                            <i class="bi bi-check-circle-fill text-success me-2"></i>Selesaikan Perbaikan
                        @endif
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body pt-2">
                    @if ($isActive)
                        <p class="text-muted">
                            Kamu yakin ingin menandai fasilitas
                            <strong>{{ $facility->name }}</strong> dalam perbaikan?
                            Pengguna tidak akan bisa mengajukan reservasi baru.
                        </p>

                        {{-- D4: Peringatan reservasi terdampak --}}
                        @if ($upcoming->isNotEmpty())
                            <div class="alert alert-warning mb-0">
                                <div class="fw-semibold mb-2">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Terdapat {{ $upcoming->count() }} reservasi yang sudah disetujui
                                    dan belum dilaksanakan:
                                </div>
                                <div class="table-responsive" style="max-height: 220px; overflow-y: auto;">
                                    <table class="table table-sm table-borderless mb-0" style="font-size: 0.82rem;">
                                        <thead>
                                            <tr class="text-muted">
                                                <th>Tanggal</th>
                                                <th>Waktu</th>
                                                <th>Pemohon</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($upcoming as $res)
                                                <tr>
                                                    <td>{{ $res->start_time->format('d M Y') }}</td>
                                                    <td>{{ $res->start_time->format('H:i') }} – {{ $res->end_time->format('H:i') }}</td>
                                                    <td>{{ $res->user->name ?? '-' }}</td>
                                                    <td>
                                                        {{-- Link ke O3 (officer.reservations.show) --}}
                                                        @if (Route::has('officer.reservations.show'))
                                                            <a href="{{ route('officer.reservations.show', $res->id) }}"
                                                               class="text-primary text-decoration-none"
                                                               title="Lihat di antrian petugas">
                                                                <i class="bi bi-box-arrow-up-right"></i>
                                                            </a>
                                                        @else
                                                            <span class="text-muted" title="Route petugas belum terdaftar">
                                                                <i class="bi bi-box-arrow-up-right"></i>
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Reservasi ini <strong>tidak dibatalkan otomatis</strong>. Pembatalan
                                    dilakukan lewat halaman antrian (US 10).
                                </small>
                            </div>
                        @endif

                    @else
                        <p class="text-muted mb-0">
                            Kamu yakin perbaikan fasilitas
                            <strong>{{ $facility->name }}</strong> sudah selesai?
                            Fasilitas akan kembali aktif dan dapat dipesan oleh pengguna.
                        </p>
                    @endif
                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-arrow-left me-1"></i>Batal
                    </button>
                    <form method="POST" action="{{ route('officer.facilities.status', $facility) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="{{ $isActive ? 'under_maintenance' : 'active' }}">
                        @if ($isActive)
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-tools me-1"></i>Ya, Tandai Perbaikan
                            </button>
                        @else
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i>Ya, Selesai Perbaikan
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach

@endsection
