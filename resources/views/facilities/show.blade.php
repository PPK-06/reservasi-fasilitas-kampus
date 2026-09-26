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
        <div class="card border-0 shadow-sm mt-4" id="section-grid-ketersediaan">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-calendar3 me-2"></i>Ketersediaan Jadwal
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <form method="GET" action="{{ route('facilities.show', $facility) }}" class="d-flex align-items-center gap-2 mb-0">
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
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-p2-reset" title="Reset pilihan slot">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
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
                        <span class="d-flex align-items-center gap-1">
                            <span class="d-inline-block rounded bg-primary" style="width: 14px; height: 14px;"></span>
                            Dipilih
                        </span>
                    </div>
                </div>

                <div class="row row-cols-3 row-cols-sm-4 row-cols-md-6 g-2" id="p2-slot-grid-container">
                    @foreach ($slots as $slot)
                        @php
                            $isAvailable = $slot['is_available'] && $isActive;
                            $status = $isAvailable ? 'available' : ($slot['is_booked'] ? 'booked' : 'past_limit');
                        @endphp
                        <div class="col">
                            <button type="button"
                                    class="btn {{ $isAvailable ? 'btn-outline-primary' : 'btn-secondary' }} w-100 py-2 p2-slot-btn position-relative"
                                    data-index="{{ $loop->index }}"
                                    data-start="{{ $slot['start'] }}"
                                    data-end="{{ $slot['end'] }}"
                                    data-status="{{ $status }}"
                                    {{ ! $isAvailable ? 'disabled' : '' }}
                                    style="font-size: 0.83rem; font-weight: 500; {{ ! $isAvailable ? 'opacity: 0.65;' : '' }}">
                                <div class="slot-time fw-semibold">{{ $slot['start'] }}</div>
                                <small class="p2-slot-label d-block {{ $isAvailable ? 'text-primary' : 'text-white-50' }}" style="font-size: 0.65rem;">
                                    {{ $isAvailable ? 'Tersedia' : ($slot['is_booked'] ? 'Terisi' : 'Tidak Tersedia') }}
                                </small>
                            </button>
                        </div>
                    @endforeach
                </div>

                {{-- Action Bar Langsung di Bawah Grid --}}
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4 pt-3 border-top">
                    <div>
                        <div class="small fw-semibold text-dark" id="p2-selection-info">
                            Klik slot jam mulai lalu jam selesai di grid untuk memilih rentang waktu.
                        </div>
                        <div class="small text-muted" id="p2-selection-sub">
                            Tanggal pemesanan: <strong>{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d F Y') }}</strong>
                        </div>
                    </div>
                    <div>
                        @auth
                            @if (auth()->user()->role === 'pengguna')
                                <button type="button"
                                        class="btn btn-primary px-4 fw-semibold"
                                        id="btn-open-reserve-modal"
                                        disabled>
                                    <i class="bi bi-calendar-plus me-1"></i>Ajukan Reservasi
                                </button>
                            @else
                                <button type="button" class="btn btn-secondary px-4 fw-semibold" disabled title="Hanya akun pengguna yang dapat mengajukan reservasi">
                                    <i class="bi bi-lock me-1"></i>Ajukan Reservasi
                                </button>
                            @endif
                        @endauth

                        @guest
                            <a href="{{ route('login') }}" class="btn btn-primary px-4 fw-semibold">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Login untuk Reservasi
                            </a>
                        @endguest
                    </div>
                </div>
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

                        @if ($isActive)
                            <button type="button"
                                    class="btn btn-primary w-100 mb-2"
                                    id="btn-sidebar-reserve">
                                <i class="bi bi-calendar-plus me-1"></i>Ajukan Reservasi
                            </button>
                        @else
                            <button type="button" class="btn btn-secondary w-100 mb-2 disabled">
                                <i class="bi bi-calendar-plus me-1"></i>Ajukan Reservasi
                            </button>
                        @endif

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

{{-- ═══════════════════════════════════════════════════════
     MODAL AJUKAN RESERVASI (P2 Langsung di Grid)
     ═══════════════════════════════════════════════════════ --}}
@auth
    @if (auth()->user()->role === 'pengguna' && $isActive)
    <div class="modal fade" id="reservationModal" tabindex="-1" aria-labelledby="reservationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="reservationModalLabel">
                        <i class="bi bi-calendar-plus text-primary me-2"></i>Ajukan Reservasi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form method="POST" action="{{ route('reservations.store') }}">
                    @csrf
                    <div class="modal-body pt-3">
                        <input type="hidden" name="facility_id" value="{{ $facility->id }}">
                        <input type="hidden" name="date" id="modal_date" value="{{ $selectedDate }}">
                        <input type="hidden" name="start_slot" id="modal_start_slot" value="{{ old('start_slot') }}">
                        <input type="hidden" name="end_slot" id="modal_end_slot" value="{{ old('end_slot') }}">

                        {{-- Ringkasan Pilihan --}}
                        <div class="p-3 rounded-3 mb-3" style="background: #f8f9fa;">
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <span class="text-muted small">Fasilitas</span>
                                <span class="fw-semibold text-dark">{{ $facility->name }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <span class="text-muted small">Tanggal</span>
                                <span class="fw-semibold text-dark" id="modal-summary-date">{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d F Y') }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Waktu Pemakaian</span>
                                <div class="text-end">
                                    <span class="fw-bold text-primary" id="modal-summary-time">-</span>
                                    <span class="badge bg-primary ms-1" id="modal-summary-badge">-</span>
                                </div>
                            </div>
                        </div>

                        {{-- Alasan Penggunaan --}}
                        <div class="mb-2">
                            <label for="modal_purpose" class="form-label fw-semibold">
                                Alasan / Tujuan Penggunaan <span class="text-danger">*</span>
                            </label>
                            <textarea name="purpose"
                                      id="modal_purpose"
                                      rows="3"
                                      class="form-control @error('purpose') is-invalid @enderror"
                                      minlength="5"
                                      maxlength="255"
                                      placeholder="Tuliskan alasan atau keperluan penggunaan fasilitas..."
                                      required>{{ old('purpose') }}</textarea>
                            @error('purpose')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Minimal 5 karakter, maksimal 255 karakter.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-send me-1"></i>Kirim Pengajuan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endauth

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const startTimes = @json($startSlots ?? []);
    const endTimes = @json($endSlots ?? []);
    const slotButtons = document.querySelectorAll('.p2-slot-btn');
    const btnOpenModal = document.getElementById('btn-open-reserve-modal');
    const btnSidebarReserve = document.getElementById('btn-sidebar-reserve');
    const btnReset = document.getElementById('btn-p2-reset');
    const selectionInfo = document.getElementById('p2-selection-info');
    const modalStartSlot = document.getElementById('modal_start_slot');
    const modalEndSlot = document.getElementById('modal_end_slot');
    const modalSummaryTime = document.getElementById('modal-summary-time');
    const modalSummaryBadge = document.getElementById('modal-summary-badge');
    const modalEl = document.getElementById('reservationModal');
    const reserveModal = modalEl ? new bootstrap.Modal(modalEl) : null;

    let startIndex = null;
    let endIndex = null;
    let isPickingEnd = false;

    function checkSlotRangeValidity(startIdx, endIdx) {
        for (let i = startIdx; i <= endIdx; i++) {
            const btn = document.querySelector(`.p2-slot-btn[data-index="${i}"]`);
            if (btn && btn.dataset.status !== 'available') {
                return false;
            }
        }
        return true;
    }

    function updateHighlight() {
        slotButtons.forEach((btn, idx) => {
            const isAvailable = btn.dataset.status === 'available';
            const label = btn.querySelector('.p2-slot-label');

            btn.classList.remove('btn-primary', 'text-white', 'btn-outline-primary', 'btn-secondary');
            if (label) label.classList.remove('text-primary', 'text-white-50');

            if (!isAvailable) {
                btn.classList.add('btn-secondary');
                btn.disabled = true;
                if (label) {
                    label.classList.add('text-white-50');
                    label.textContent = btn.dataset.status === 'booked' ? 'Terisi' : 'Tidak Tersedia';
                }
            } else {
                btn.disabled = false;
                if (startIndex !== null && endIndex !== null && idx >= startIndex && idx <= endIndex) {
                    btn.classList.add('btn-primary', 'text-white');
                    if (label) {
                        label.classList.add('text-white-50');
                        label.textContent = (idx === startIndex) ? 'Mulai' : ((idx === endIndex) ? 'Selesai' : 'Dipilih');
                    }
                } else {
                    btn.classList.add('btn-outline-primary');
                    if (label) {
                        label.classList.add('text-primary');
                        label.textContent = 'Tersedia';
                    }
                }
            }
        });

        if (startIndex !== null && endIndex !== null) {
            const count = (endIndex - startIndex) + 1;
            const sTime = startTimes[startIndex];
            const eTime = endTimes[endIndex];

            if (btnOpenModal) btnOpenModal.disabled = false;
            if (modalStartSlot) modalStartSlot.value = sTime;
            if (modalEndSlot) modalEndSlot.value = eTime;
            if (modalSummaryTime) modalSummaryTime.textContent = `${sTime} – ${eTime}`;
            if (modalSummaryBadge) modalSummaryBadge.textContent = `${count} Slot (${(count * 0.5)} Jam)`;

            if (isPickingEnd) {
                selectionInfo.innerHTML = `Mulai: <strong>${sTime}</strong>. Klik slot jam selesai untuk blok rentang waktu.`;
            } else {
                selectionInfo.innerHTML = `Terpilih: <strong>${sTime}</strong> – <strong>${eTime}</strong> (${count} Slot / ${(count * 0.5)} Jam)`;
            }
        } else {
            if (btnOpenModal) btnOpenModal.disabled = true;
            if (modalStartSlot) modalStartSlot.value = '';
            if (modalEndSlot) modalEndSlot.value = '';
            selectionInfo.textContent = 'Klik slot jam mulai lalu jam selesai di grid untuk memilih rentang waktu.';
        }
    }

    slotButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            if (this.disabled) return;
            const clickedIdx = parseInt(this.dataset.index, 10);

            if (!isPickingEnd || startIndex === null) {
                startIndex = clickedIdx;
                endIndex = clickedIdx;
                isPickingEnd = true;
            } else {
                if (clickedIdx >= startIndex) {
                    if (checkSlotRangeValidity(startIndex, clickedIdx)) {
                        endIndex = clickedIdx;
                        isPickingEnd = false;
                    } else {
                        alert('Rentang slot melewati jadwal yang sudah terisi.');
                        return;
                    }
                } else {
                    startIndex = clickedIdx;
                    endIndex = clickedIdx;
                    isPickingEnd = true;
                }
            }
            updateHighlight();
        });

        btn.addEventListener('mouseenter', function () {
            if (!isPickingEnd || startIndex === null) return;
            const hoverIdx = parseInt(this.dataset.index, 10);
            if (hoverIdx >= startIndex && checkSlotRangeValidity(startIndex, hoverIdx)) {
                slotButtons.forEach((b, idx) => {
                    if (b.disabled) return;
                    const label = b.querySelector('.p2-slot-label');
                    if (idx >= startIndex && idx <= hoverIdx) {
                        b.classList.add('btn-primary', 'text-white');
                        b.classList.remove('btn-outline-primary');
                        if (label) {
                            label.classList.add('text-white-50');
                            label.classList.remove('text-primary');
                            label.textContent = idx === startIndex ? 'Mulai' : (idx === hoverIdx ? 'Selesai' : 'Dipilih');
                        }
                    } else if (idx < startIndex || idx > hoverIdx) {
                        b.classList.remove('btn-primary', 'text-white');
                        b.classList.add('btn-outline-primary');
                        if (label) {
                            label.classList.remove('text-white-50');
                            label.classList.add('text-primary');
                            label.textContent = 'Tersedia';
                        }
                    }
                });
            }
        });
    });

    document.getElementById('p2-slot-grid-container')?.addEventListener('mouseleave', function () {
        if (isPickingEnd) {
            updateHighlight();
        }
    });

    btnReset?.addEventListener('click', function () {
        startIndex = null;
        endIndex = null;
        isPickingEnd = false;
        updateHighlight();
    });

    function openModal() {
        if (!reserveModal) return;
        reserveModal.show();
        setTimeout(() => {
            document.getElementById('modal_purpose')?.focus();
        }, 300);
    }

    btnOpenModal?.addEventListener('click', openModal);

    btnSidebarReserve?.addEventListener('click', function () {
        if (startIndex !== null && endIndex !== null) {
            openModal();
        } else {
            const gridSection = document.getElementById('section-grid-ketersediaan');
            if (gridSection) {
                gridSection.scrollIntoView({ behavior: 'smooth' });
                selectionInfo.classList.add('text-primary');
                selectionInfo.innerHTML = '<i class="bi bi-hand-index me-1"></i>Silakan klik slot jam mulai lalu jam selesai di grid terlebih dahulu!';
            }
        }
    });

    @if ($errors->any() && old('facility_id') == $facility->id)
        if (reserveModal) {
            reserveModal.show();
        }
    @endif
});
</script>
@endpush
