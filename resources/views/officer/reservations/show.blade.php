@extends('layouts.app')

@section('title', 'Detail Reservasi — Petugas')
@section('page-title', 'Detail Reservasi')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Fasilitas</dt>
                    <dd class="col-sm-8">{{ $reservation->facility->name }}</dd>

                    <dt class="col-sm-4">Pemohon</dt>
                    <dd class="col-sm-8">{{ $reservation->user->name }}</dd>

                    <dt class="col-sm-4">Tanggal</dt>
                    <dd class="col-sm-8">{{ $reservation->start_time->format('d M Y') }}</dd>

                    <dt class="col-sm-4">Waktu</dt>
                    <dd class="col-sm-8">{{ $reservation->start_time->format('H:i') }} – {{ $reservation->end_time->format('H:i') }}</dd>

                    <dt class="col-sm-4">Tujuan</dt>
                    <dd class="col-sm-8">{{ $reservation->purpose }}</dd>

                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">
                        @php $s = $reservation->status; @endphp
                        @if ($s === 'pending' && $reservation->start_time->lte(now()))
                            <span class="badge text-bg-secondary">Terlewat</span>
                        @elseif ($s === 'pending')
                            <span class="badge text-bg-warning">Menunggu</span>
                        @elseif ($s === 'approved')
                            <span class="badge text-bg-success">Disetujui</span>
                        @elseif ($s === 'rejected')
                            <span class="badge text-bg-danger">Ditolak</span>
                        @else
                            <span class="badge text-bg-secondary">Dibatalkan</span>
                        @endif
                    </dd>

                    @if ($reservation->status_reason)
                    <dt class="col-sm-4">Alasan</dt>
                    <dd class="col-sm-8">{{ $reservation->status_reason }}</dd>
                    @endif

                    <dt class="col-sm-4">Diajukan</dt>
                    <dd class="col-sm-8">{{ $reservation->created_at->format('d M Y, H:i') }}</dd>
                </dl>
            </div>

            @php
                $isExpired = $reservation->status === 'pending' && $reservation->start_time->lte(now());
            @endphp

            @if ($reservation->status === 'pending' && ! $isExpired)
            <div class="card-footer bg-white border-top d-flex gap-2 justify-content-end py-3">
                {{-- Setujui --}}
                <form method="POST" action="{{ route('officer.reservations.approve', $reservation) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="bi bi-check-lg me-1"></i>Setujui
                    </button>
                </form>

                {{-- Tolak --}}
                <button type="button" class="btn btn-danger btn-sm"
                        data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="bi bi-x-circle me-1"></i>Tolak
                </button>
            </div>
            @elseif ($reservation->status === 'approved')
            <div class="card-footer bg-white border-top d-flex gap-2 justify-content-end py-3">
                {{-- Batalkan (petugas) --}}
                <button type="button" class="btn btn-outline-danger btn-sm"
                        data-bs-toggle="modal" data-bs-target="#cancelModal">
                    <i class="bi bi-slash-circle me-1"></i>Batalkan
                </button>
            </div>
            @elseif ($isExpired)
            <div class="card-footer bg-white border-top py-3">
                <span class="text-muted small">
                    <i class="bi bi-lock me-1"></i>Reservasi terlewat — aksi tidak tersedia.
                </span>
            </div>
            @endif
        </div>

        <div class="mt-3">
            <a href="{{ route('officer.reservations.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>
</div>

{{-- Modal Tolak --}}
@if ($reservation->status === 'pending' && ! $isExpired)
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Tolak Reservasi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form method="POST" action="{{ route('officer.reservations.reject', $reservation) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body pt-2">
                    <p class="text-muted">
                        Tolak reservasi <strong>{{ $reservation->facility->name }}</strong>
                        milik {{ $reservation->user->name }}?
                    </p>
                    <div class="mb-0">
                        <label for="rejectReason" class="form-label fw-semibold">Alasan <span class="text-danger">*</span></label>
                        <textarea name="status_reason" id="rejectReason"
                                  class="form-control @error('status_reason') is-invalid @enderror"
                                  rows="3" minlength="10" maxlength="500" required></textarea>
                        <div class="form-text">Minimal 10 karakter.</div>
                        @error('status_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
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
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Batalkan Reservasi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form method="POST" action="{{ route('officer.reservations.cancel', $reservation) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body pt-2">
                    <p class="text-muted">
                        Batalkan reservasi <strong>{{ $reservation->facility->name }}</strong>
                        milik {{ $reservation->user->name }}?
                    </p>
                    <div class="mb-0">
                        <label for="cancelReason" class="form-label fw-semibold">Alasan <span class="text-danger">*</span></label>
                        <textarea name="status_reason" id="cancelReason"
                                  class="form-control @error('status_reason') is-invalid @enderror"
                                  rows="3" minlength="10" maxlength="500" required></textarea>
                        <div class="form-text">Minimal 10 karakter.</div>
                        @error('status_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-slash-circle me-1"></i>Ya, Batalkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
