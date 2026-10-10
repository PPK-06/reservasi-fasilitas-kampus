@extends('layouts.app')

@section('title', 'Antrian Reservasi Petugas | Sistem Fasilitas Kampus Undip')
@section('page-title', 'Antrian Validasi Reservasi')
@section('page-subtitle', 'Pemeriksaan kelayakan permohonan peminjaman ruangan kampus oleh petugas')

@section('content')
<div class="card card-undip border shadow-sm">
    <div class="card-header bg-white py-3 border-bottom">
        <ul class="nav nav-pills card-header-pills gap-2">
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'menunggu' ? 'active' : '' }}"
                   style="{{ $tab === 'menunggu' ? 'background-color: var(--undip-navy); color: #fff;' : 'color: var(--text-heading);' }}"
                   href="{{ route('officer.reservations.index', ['tab' => 'menunggu']) }}">
                    <i class="bi bi-hourglass-split me-1"></i>Menunggu
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'terlewat' ? 'active' : '' }}"
                   style="{{ $tab === 'terlewat' ? 'background-color: var(--undip-navy); color: #fff;' : 'color: var(--text-heading);' }}"
                   href="{{ route('officer.reservations.index', ['tab' => 'terlewat']) }}">
                    <i class="bi bi-clock-history me-1"></i>Terlewat
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'semua' ? 'active' : '' }}"
                   style="{{ $tab === 'semua' ? 'background-color: var(--undip-navy); color: #fff;' : 'color: var(--text-heading);' }}"
                   href="{{ route('officer.reservations.index', ['tab' => 'semua']) }}">
                    <i class="bi bi-collection me-1"></i>Semua Permohonan
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background-color: var(--undip-navy); color: #ffffff;">
                    <tr>
                        <th class="ps-3 py-3" style="width: 50px;">#</th>
                        <th class="py-3">Fasilitas</th>
                        <th class="py-3">Pemohon</th>
                        <th class="py-3">Tanggal Kegiatan</th>
                        <th class="py-3">Waktu Pemakaian</th>
                        <th class="py-3">Status</th>
                        <th class="text-end pe-3 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reservations as $r)
                    <tr>
                        <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                        <td>
                            <strong class="text-dark font-heading">{{ $r->facility->name }}</strong>
                            <div class="text-muted" style="font-size: 0.76rem;">{{ $r->facility->location }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $r->user->name }}</div>
                            <small class="text-muted">{{ $r->user->identity_number ?? '-' }}</small>
                        </td>
                        <td class="small">{{ $r->start_time->format('d M Y') }}</td>
                        <td class="small font-monospace">{{ $r->start_time->format('H:i') }} – {{ $r->end_time->format('H:i') }}</td>
                        <td>
                            @if ($r->status === 'pending' && $r->start_time->lte(now()))
                                <span class="badge-status badge-status-rejected">
                                    <i class="bi bi-clock"></i> Terlewat
                                </span>
                            @elseif ($r->status === 'pending')
                                <span class="badge-status badge-status-pending">
                                    <i class="bi bi-hourglass-split"></i> Menunggu
                                </span>
                            @elseif ($r->status === 'approved')
                                <span class="badge-status badge-status-approved">
                                    <i class="bi bi-check-circle-fill"></i> Disetujui
                                </span>
                            @elseif ($r->status === 'rejected')
                                <span class="badge-status badge-status-rejected">
                                    <i class="bi bi-x-circle-fill"></i> Ditolak
                                </span>
                            @else
                                <span class="badge-status badge-status-rejected" style="background:#F1F5F9; color:#475569; border-color:#CBD5E1;">
                                    <i class="bi bi-slash-circle"></i> Dibatalkan
                                </span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <a href="{{ route('officer.reservations.show', $r) }}"
                               class="btn btn-outline-undip btn-sm px-3"
                               title="Verifikasi Permohonan">
                                <i class="bi bi-eye me-1"></i>Periksa
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                            <div class="fw-semibold text-dark">Tidak ada data antrian</div>
                            <small class="text-muted">Tidak ditemukan permohonan pada kategori ini.</small>
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
