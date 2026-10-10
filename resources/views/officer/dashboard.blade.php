@extends('layouts.app')

@section('title', 'Dashboard Antrian Petugas | Sistem Fasilitas Kampus Undip')
@section('page-title', 'Dashboard Petugas Sarana')
@section('page-subtitle', 'Ikhtisar antrian permohonan reservasi dan laporan pemeliharaan fasilitas kampus')

@section('content')
<div class="row g-4">

    {{-- Panel Reservasi --}}
    <div class="col-md-6">
        <div class="card card-undip border shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-calendar-check text-primary fs-5"></i>
                    <h6 class="mb-0 fw-bold font-heading text-dark">Antrian Reservasi Menunggu</h6>
                </div>
                <span class="badge-status badge-status-pending">{{ $pendingCount }} Menunggu</span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse ($pendingReservations as $r)
                    <a href="{{ route('officer.reservations.show', $r) }}"
                       class="list-group-item list-group-item-action px-4 py-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-bold text-dark font-heading">{{ $r->facility->name }}</div>
                                <div class="text-muted small mt-1 font-monospace">
                                    <i class="bi bi-clock me-1"></i>{{ $r->start_time->format('d M Y, H:i') }} – {{ $r->end_time->format('H:i') }}
                                </div>
                            </div>
                            <span class="badge bg-light text-dark border small">
                                <i class="bi bi-person me-1"></i>{{ $r->user->name }}
                            </span>
                        </div>
                    </a>
                    @empty
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-check-circle fs-2 text-success d-block mb-2"></i>
                        <div class="fw-semibold text-dark">Tidak ada reservasi menunggu</div>
                        <small class="text-muted">Semua permohonan peminjaman ruangan telah diproses.</small>
                    </div>
                    @endforelse
                </div>
            </div>
            @if ($pendingCount > 10)
            <div class="card-footer bg-white border-top py-3 px-4 text-end">
                <a href="{{ route('officer.reservations.index') }}" class="btn btn-outline-undip btn-sm">
                    Lihat semua antrian ({{ $pendingCount }}) <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            @endif
        </div>
    </div>

    {{-- Panel Laporan --}}
    <div class="col-md-6">
        <div class="card card-undip border shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-tools text-warning fs-5"></i>
                    <h6 class="mb-0 fw-bold font-heading text-dark">Laporan Belum Selesai</h6>
                </div>
                <span class="badge-status badge-status-maintenance">{{ $pendingReportCount }} Kendala</span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse ($pendingReports as $report)
                    <a href="{{ route('officer.reports.show', $report) }}"
                       class="list-group-item list-group-item-action px-4 py-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-bold text-dark font-heading">{{ $report->facility->name }}</div>
                                <small class="text-muted">
                                    <i class="bi bi-calendar-event me-1"></i>{{ $report->created_at->format('d M Y, H:i') }}
                                </small>
                            </div>
                            @if ($report->status === 'baru')
                                <span class="badge-status badge-status-pending">Baru</span>
                            @else
                                <span class="badge-status badge-status-maintenance">Diproses</span>
                            @endif
                        </div>
                    </a>
                    @empty
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-check-circle fs-2 text-success d-block mb-2"></i>
                        <div class="fw-semibold text-dark">Tidak ada laporan menunggu</div>
                        <small class="text-muted">Seluruh sarana dan prasarana dalam kondisi baik.</small>
                    </div>
                    @endforelse
                </div>
            </div>
            @if ($pendingReportCount > 10)
            <div class="card-footer bg-white border-top py-3 px-4 text-end">
                <a href="{{ route('officer.reports.index') }}" class="btn btn-outline-undip btn-sm">
                    Lihat semua laporan ({{ $pendingReportCount }}) <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
