@extends('layouts.app')

@section('title', 'Detail Reservasi')
@section('page-title', 'Detail Reservasi')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">

                <dl class="row mb-0">
                    <dt class="col-sm-4">Fasilitas</dt>
                    <dd class="col-sm-8">{{ $reservation->facility->name }}</dd>

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
                            <span class="badge text-bg-secondary">Tidak sempat diproses</span>
                        @elseif ($s === 'pending')
                            <span class="badge text-bg-warning">Menunggu</span>
                        @elseif ($s === 'approved')
                            <span class="badge text-bg-success">Disetujui</span>
                        @elseif ($s === 'rejected')
                            <span class="badge text-bg-danger">Ditolak</span>
                        @elseif ($s === 'cancelled_by_user')
                            <span class="badge text-bg-secondary">Dibatalkan</span>
                        @elseif ($s === 'cancelled_by_officer')
                            <span class="badge text-bg-dark">Dibatalkan petugas</span>
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
                $canCancel = false;
                if ($reservation->status === 'pending') {
                    $canCancel = true;
                } elseif ($reservation->status === 'approved') {
                    $canCancel = now()->toDateString() < $reservation->start_time->toDateString();
                }
            @endphp

            @if ($canCancel)
            <div class="card-footer bg-white border-top d-flex justify-content-end gap-2 py-3">
                <button type="button" class="btn btn-outline-danger btn-sm"
                        data-bs-toggle="modal" data-bs-target="#cancelModal">
                    <i class="bi bi-x-circle me-1"></i>Batalkan
                </button>
            </div>
            @elseif (in_array($reservation->status, ['pending', 'approved']))
            <div class="card-footer bg-white border-top py-3">
                <span class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i>Sudah lewat batas pembatalan mandiri, hubungi petugas.
                </span>
            </div>
            @endif
        </div>

        <div class="mt-3">
            <a href="{{ route('reservations.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>
</div>

@if ($canCancel)
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="cancelModalLabel">
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
                        <label for="status_reason" class="form-label fw-semibold">
                            Alasan <span class="text-muted fw-normal">(opsional)</span>
                        </label>
                        <textarea name="status_reason" id="status_reason"
                                  class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-arrow-left me-1"></i>Kembali
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i>Ya, Batalkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
