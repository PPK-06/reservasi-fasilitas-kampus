@extends('layouts.app')

@section('title', 'Riwayat Reservasi · Wiyata')
@section('page-title', 'Reservasi Saya')
@section('page-subtitle', 'Pantau status permohonan peminjaman ruangan dan sarana kampus Tembalang')
@section('page-actions')
    <a href="{{ route('reservations.create') }}" class="btn btn-undip-primary btn-sm px-3 shadow-sm">
        <i class="bi bi-calendar-plus me-1"></i>Ajukan Permohonan Baru
    </a>
@endsection

@section('content')
<div class="card card-undip border shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom">
        <h6 class="mb-0 fw-bold font-heading text-dark">
            <i class="bi bi-clock-history me-2 text-primary"></i>Daftar Riwayat Reservasi
        </h6>
        <span class="text-muted small">Total: {{ $reservations->total() }} permohonan</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background-color: var(--undip-navy); color: #ffffff;">
                    <tr>
                        <th class="ps-3 py-3" style="width:50px">#</th>
                        <th class="py-3">Fasilitas</th>
                        <th class="py-3">Tanggal Kegiatan</th>
                        <th class="py-3">Waktu Pemakaian</th>
                        <th class="py-3">Status Permohonan</th>
                        <th class="text-end pe-3 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reservations as $reservation)
                    <tr>
                        <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                        <td class="fw-semibold text-dark">
                            {{ $reservation->facility->name }}
                            <div class="text-muted" style="font-size: 0.76rem;">{{ $reservation->facility->location }}</div>
                        </td>
                        <td class="small">{{ $reservation->start_time->format('d M Y') }}</td>
                        <td class="small font-monospace">{{ $reservation->start_time->format('H:i') }} – {{ $reservation->end_time->format('H:i') }}</td>
                        <td>
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
                        </td>
                        <td class="text-end pe-3">
                            <a href="{{ route('reservations.show', $reservation) }}"
                               class="btn btn-outline-undip btn-sm px-3"
                               title="Lihat Detail Permohonan">
                                <i class="bi bi-eye me-1"></i>Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                            <div class="fw-semibold text-dark">Belum ada riwayat reservasi</div>
                            <p class="text-muted small mb-3">Anda belum pernah mengajukan permohonan peminjaman ruangan kampus.</p>
                            <a href="{{ route('reservations.create') }}" class="btn btn-undip-primary btn-sm px-3">
                                <i class="bi bi-calendar-plus me-1"></i>Mulai Ajukan Sekarang
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($reservations->hasPages())
    <div class="card-footer bg-white border-top d-flex justify-content-end py-3 px-3">
        {{ $reservations->links() }}
    </div>
    @endif
</div>
@endsection
