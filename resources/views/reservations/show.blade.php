@extends('layouts.app')

@section('title', 'Detail Reservasi · Wiyata')
@section('page-title', 'Detail Permohonan Reservasi')
@section('page-subtitle', 'Informasi lengkap jadwal peminjaman ruangan dan status persetujuan')
@section('page-actions')
    <a href="{{ route('reservations.index') }}" class="btn btn-outline-secondary btn-sm px-3">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Riwayat
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-undip border shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-calendar2-check text-primary fs-5"></i>
                    <h6 class="mb-0 fw-bold font-heading text-dark">Data Pengajuan</h6>
                </div>
                <div>
                    @php $s = $reservation->status; @endphp
                    @if ($s === 'pending' && $reservation->start_time->lte(now()))
                        <span class="badge-status badge-status-rejected">
                            <i class="bi bi-clock"></i> Tidak sempat diproses
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
                    @elseif ($s === 'cancelled_by_user')
                        <span class="badge-status badge-status-rejected" style="background:#F1F5F9; color:#475569; border-color:#CBD5E1;">
                            <i class="bi bi-slash-circle"></i> Dibatalkan
                        </span>
                    @elseif ($s === 'cancelled_by_officer')
                        <span class="badge-status badge-status-rejected">
                            <i class="bi bi-x-octagon-fill"></i> Dibatalkan petugas
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

                    <dt class="col-sm-4 text-muted small fw-semibold">Tanggal</dt>
                    <dd class="col-sm-8 text-dark mb-2">{{ $reservation->start_time->format('d M Y') }}</dd>

                    <dt class="col-sm-4 text-muted small fw-semibold">Waktu</dt>
                    <dd class="col-sm-8 text-dark mb-2">
                        {{ $reservation->start_time->format('H:i') }} – {{ $reservation->end_time->format('H:i') }} WIB
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
                $canCancel = false;
                if ($reservation->status === 'pending') {
                    $canCancel = true;
                } elseif ($reservation->status === 'approved') {
                    $canCancel = now()->toDateString() < $reservation->start_time->toDateString();
                }
            @endphp

            @if ($canCancel)
            <div class="card-footer bg-white border-top d-flex justify-content-end gap-2 py-3 px-4">
                <button type="button" class="btn btn-outline-danger btn-sm px-3"
                        data-bs-toggle="modal" data-bs-target="#cancelModal">
                    <i class="bi bi-x-circle me-1"></i>Batalkan
                </button>
            </div>
            @elseif (in_array($reservation->status, ['pending', 'approved']))
            <div class="card-footer bg-white border-top py-3 px-4">
                <span class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i>Sudah lewat batas pembatalan mandiri, hubungi petugas sarana.
                </span>
            </div>
            @endif
        </div>
    </div>
</div>

@if ($canCancel)
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold font-heading text-dark" id="cancelModalLabel">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Batalkan Reservasi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form method="POST" action="{{ route('reservations.cancel', $reservation) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body pt-2">
                    <p class="text-muted mb-3">
                        Kamu yakin ingin membatalkan reservasi
                        <strong>{{ $reservation->facility->name }}</strong>
                        pada {{ $reservation->start_time->format('d M Y, H:i') }}?
                    </p>
                    <div class="mb-0">
                        <label for="status_reason" class="form-label fw-semibold small text-dark">
                            Alasan Pembatalan <span class="text-muted fw-normal">(opsional)</span>
                        </label>
                        <textarea name="status_reason" id="status_reason"
                                  class="form-control" rows="3" maxlength="500"
                                  placeholder="Contoh: Perubahan jadwal rapat panitia..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="bi bi-arrow-left me-1"></i>Kembali
                    </button>
                    <button type="submit" class="btn btn-danger btn-sm px-3">
                        <i class="bi bi-x-circle me-1"></i>Ya, Batalkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
