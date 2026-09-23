@extends('layouts.app')

@section('title', 'Dashboard Antrian')
@section('page-title', 'Dashboard Antrian')

@section('content')
<div class="row g-4">

    {{-- Panel Reservasi --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-calendar-check me-1"></i>Reservasi Menunggu
                </h6>
                <span class="badge text-bg-warning rounded-pill">{{ $pendingCount }}</span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse ($pendingReservations as $r)
                    <a href="{{ route('officer.reservations.show', $r) }}"
                       class="list-group-item list-group-item-action px-3 py-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold">{{ $r->facility->name }}</div>
                                <small class="text-muted">
                                    {{ $r->start_time->format('d M Y, H:i') }} – {{ $r->end_time->format('H:i') }}
                                </small>
                            </div>
                            <small class="text-muted">{{ $r->user->name }}</small>
                        </div>
                    </a>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-check-circle fs-4 d-block mb-1"></i>
                        Tidak ada reservasi menunggu.
                    </div>
                    @endforelse
                </div>
            </div>
            @if ($pendingCount > 10)
            <div class="card-footer bg-white border-top py-2 text-end">
                <a href="{{ route('officer.reservations.index') }}" class="btn btn-outline-primary btn-sm">
                    Lihat semua ({{ $pendingCount }})
                </a>
            </div>
            @endif
        </div>
    </div>

    {{-- Panel Laporan --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-tools me-1"></i>Laporan Belum Selesai
                </h6>
                <span class="badge text-bg-danger rounded-pill">{{ $pendingReportCount }}</span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse ($pendingReports as $report)
                    <a href="{{ route('officer.reports.show', $report) }}"
                       class="list-group-item list-group-item-action px-3 py-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold">{{ $report->facility->name }}</div>
                                <small class="text-muted">{{ $report->created_at->format('d M Y') }}</small>
                            </div>
                            @if ($report->status === 'baru')
                                <span class="badge text-bg-warning">Baru</span>
                            @else
                                <span class="badge text-bg-primary">Diproses</span>
                            @endif
                        </div>
                    </a>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-check-circle fs-4 d-block mb-1"></i>
                        Tidak ada laporan menunggu.
                    </div>
                    @endforelse
                </div>
            </div>
            @if ($pendingReportCount > 10)
            <div class="card-footer bg-white border-top py-2 text-end">
                <a href="{{ route('officer.reports.index') }}" class="btn btn-outline-primary btn-sm">
                    Lihat semua ({{ $pendingReportCount }})
                </a>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
