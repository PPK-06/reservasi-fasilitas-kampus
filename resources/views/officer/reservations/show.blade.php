@extends('layouts.app')

@section('title', 'Detail Reservasi Petugas · Wiyata')
@section('page-title', 'Detail Reservasi Petugas')
@section('page-subtitle', 'Pemeriksaan kelayakan permohonan peminjaman ruangan dan aksi validasi')
@section('page-actions')
    <a href="{{ route('officer.reservations.index') }}" class="btn btn-outline-secondary btn-sm px-3">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Antrian
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-undip border shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-calendar-check text-primary fs-5"></i>
                    <h6 class="mb-0 fw-bold font-heading text-dark">Data Pengajuan Reservasi</h6>
                </div>
                <div>
                    @php $s = $reservation->status; @endphp
                    @if ($s === 'pending' && $reservation->start_time->lte(now()))
                        <span class="badge-status badge-status-rejected">
                            <i class="bi bi-clock"></i> Terlewat
                        </span>
                    @elseif ($s === 'pending')
                        <span class="badge-status badge-status-pending">
                            <i class="bi bi-hourglass-split"></i> Menunggu
                        </span>
                    @elseif ($s === 'approved')
                        <span class="badge-status badge-status-approved">
                            <i class="bi bi-check-circle-fill"></i> Disetujui
                        </span>
                    @elseif ($s === 'rejected')
                        <span class="badge-status badge-status-rejected">
                            <i class="bi bi-x-circle-fill"></i> Ditolak
                        </span>
                    @else
                        <span class="badge-status badge-status-rejected" style="background:#F1F5F9; color:#475569; border-color:#CBD5E1;">
                            <i class="bi bi-slash-circle"></i> Dibatalkan
                        </span>
                    @endif
                </div>
            </div>

            <div class="card-body p-4">
                <dl class="row mb-0 g-3">
                    <dt class="col-sm-4 text-muted small fw-semibold">Fasilitas</dt>
                    <dd class="col-sm-8 fw-semibold text-dark mb-2">
                        {{ $reservation->facility->name }}
                        <div class="text-muted small fw-normal">{{ $reservation->facility->type }} | {{ $reservation->facility->location }}</div>
                    </dd>

                    <dt class="col-sm-4 text-muted small fw-semibold">Pemohon</dt>
                    <dd class="col-sm-8 text-dark mb-2">
                        <strong>{{ $reservation->user->name }}</strong>
                        <div class="text-muted small">NIM/NIP: {{ $reservation->user->identity_number ?? '-' }} | Email: {{ $reservation->user->email }}</div>
                    </dd>

                    <dt class="col-sm-4 text-muted small fw-semibold">Tanggal</dt>
                    <dd class="col-sm-8 text-dark mb-2">{{ $reservation->start_time->format('d M Y') }}</dd>

                    <dt class="col-sm-4 text-muted small fw-semibold">Waktu</dt>
                    <dd class="col-sm-8 text-dark mb-2">
                        {{ $reservation->start_time->format('H:i') }} – {{ $reservation->end_time->format('H:i') }}
                    </dd>

                    <dt class="col-sm-4 text-muted small fw-semibold">Tujuan</dt>
                    <dd class="col-sm-8 text-dark mb-2">{{ $reservation->purpose }}</dd>

                    @if ($reservation->status_reason)
                    <dt class="col-sm-4 text-muted small fw-semibold">Alasan</dt>
                    <dd class="col-sm-8 text-danger mb-2">{{ $reservation->status_reason }}</dd>
                    @endif

                    <dt class="col-sm-4 text-muted small fw-semibold">Diajukan</dt>
                    <dd class="col-sm-8 text-muted small mb-0">{{ $reservation->created_at->format('d M Y, H:i') }}</dd>
                </dl>
            </div>

            @php
                $isExpired = $reservation->status === 'pending' && $reservation->start_time->lte(now());
            @endphp

            @if ($reservation->status === 'pending' && ! $isExpired)
            <div class="card-footer bg-white border-top d-flex gap-2 justify-content-end py-3 px-4">
                {{-- Setujui --}}
                <form method="POST" action="{{ route('officer.reservations.approve', $reservation) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success btn-sm px-3 shadow-sm">
                        <i class="bi bi-check-lg me-1"></i>Setujui
                    </button>
                </form>

                {{-- Tolak --}}
                <button type="button" class="btn btn-danger btn-sm px-3 shadow-sm"
                        data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="bi bi-x-circle me-1"></i>Tolak
                </button>
            </div>
            @elseif ($reservation->status === 'approved')
            <div class="card-footer bg-white border-top d-flex gap-2 justify-content-end py-3 px-4">
                {{-- Batalkan (petugas) --}}
                <button type="button" class="btn btn-outline-danger btn-sm px-3"
                        data-bs-toggle="modal" data-bs-target="#cancelModal">
                    <i class="bi bi-slash-circle me-1"></i>Batalkan
                </button>
            </div>
            @elseif ($isExpired)
            <div class="card-footer bg-white border-top py-3 px-4">
                <span class="text-muted small">
                    <i class="bi bi-lock me-1"></i>Reservasi terlewat | aksi tidak tersedia.
                </span>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal Tolak --}}
@if ($reservation->status === 'pending' && ! $isExpired)
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold font-heading text-dark">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Tolak Reservasi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form method="POST" action="{{ route('officer.reservations.reject', $reservation) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body pt-2">
                    <p class="text-muted mb-3">
                        Tolak reservasi <strong>{{ $reservation->facility->name }}</strong>
                        milik {{ $reservation->user->name }}?
                    </p>
                    <div class="mb-0">
                        <label for="rejectReason" class="form-label fw-semibold small text-dark">Alasan <span class="text-danger">*</span></label>
                        <textarea name="status_reason" id="rejectReason"
                                  class="form-control @error('status_reason') is-invalid @enderror"
                                  rows="3" minlength="10" maxlength="500"
                                  placeholder="Tuliskan alasan penolakan secara jelas..." required></textarea>
                        <div class="form-text small">Minimal 10 karakter.</div>
                        @error('status_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm px-3">
                        <i class="bi bi-x-circle me-1"></i>Ya, Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Modal Batalkan --}}
@if ($reservation->status === 'approved')
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold font-heading text-dark">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Batalkan Reservasi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form method="POST" action="{{ route('officer.reservations.cancel', $reservation) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body pt-2">
                    <p class="text-muted mb-3">
                        Batalkan reservasi <strong>{{ $reservation->facility->name }}</strong>
                        milik {{ $reservation->user->name }}?
                    </p>
                    <div class="mb-0">
                        <label for="cancelReason" class="form-label fw-semibold small text-dark">Alasan <span class="text-danger">*</span></label>
                        <textarea name="status_reason" id="cancelReason"
                                  class="form-control @error('status_reason') is-invalid @enderror"
                                  rows="3" minlength="10" maxlength="500"
                                  placeholder="Tuliskan alasan pembatalan..." required></textarea>
                        <div class="form-text small">Minimal 10 karakter.</div>
                        @error('status_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm px-3">
                        <i class="bi bi-slash-circle me-1"></i>Ya, Batalkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
