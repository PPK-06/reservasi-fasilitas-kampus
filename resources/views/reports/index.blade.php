@extends('layouts.app')

@section('title', 'Laporan Saya · Wiyata')
@section('page-title', 'Laporan Kerusakan Saya')
@section('page-subtitle', 'Pantau status penanganan kendala dan perbaikan sarana yang Anda laporkan')

@section('page-actions')
    <a href="{{ route('reports.create') }}" class="btn btn-undip-primary btn-sm px-3 shadow-sm">
        <i class="bi bi-plus-lg me-1"></i>Buat Laporan Baru
    </a>
@endsection

@section('content')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endif

<div class="card card-undip border shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold font-heading text-dark">
            <i class="bi bi-tools me-2 text-warning"></i>Riwayat Laporan Kerusakan
        </h6>
        <span class="text-muted small">Total: {{ $reports->total() }} laporan</span>
    </div>

    <div class="card-body p-0">
        @if ($reports->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-check2-circle fs-1 text-success d-block mb-2"></i>
                <h5 class="fw-bold font-heading text-dark">Belum ada laporan</h5>
                <p class="text-muted small mb-3">
                    Anda belum pernah mengirim laporan kerusakan fasilitas.
                </p>
                <a href="{{ route('reports.create') }}" class="btn btn-undip-primary btn-sm px-4">
                    <i class="bi bi-plus-lg me-1"></i>Buat Laporan
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: var(--undip-navy); color: #ffffff;">
                    <tr>
                        <th class="ps-3 py-3">Tanggal Laporan</th>
                        <th class="py-3">Fasilitas</th>
                        <th class="py-3">Kategori</th>
                        <th class="py-3">Status Penanganan</th>
                        <th class="text-end pe-3 py-3">Aksi</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach ($reports as $report)
                        @php
                            $categoryLabels = [
                                'kerusakan_alat' => 'Kerusakan Alat',
                                'kelistrikan' => 'Kelistrikan',
                                'pendingin_ruangan' => 'Pendingin Ruangan',
                                'furnitur' => 'Furnitur',
                                'kebersihan' => 'Kebersihan',
                                'lainnya' => 'Lainnya',
                            ];
                        @endphp

                        <tr>
                            <td class="ps-3 text-muted small">
                                {{ $report->created_at->format('d/m/Y H:i') }}
                            </td>

                            <td>
                                <strong class="text-dark">{{ $report->facility->name }}</strong>
                                @if ($report->facility->location)
                                    <div class="text-muted" style="font-size: 0.76rem;">
                                        <i class="bi bi-geo-alt me-1"></i>{{ $report->facility->location }}
                                    </div>
                                @endif
                            </td>

                            <td class="small">
                                <span class="badge bg-light text-dark border">
                                    {{ $categoryLabels[$report->category] ?? $report->category }}
                                </span>
                            </td>

                            <td>
                                @if ($report->status === 'baru')
                                    <span class="badge-status badge-status-pending">
                                        <i class="bi bi-hourglass-split"></i> Baru
                                    </span>
                                @elseif ($report->status === 'diproses')
                                    <span class="badge-status badge-status-maintenance">
                                        <i class="bi bi-gear-wide-connected"></i> Diproses
                                    </span>
                                @elseif ($report->status === 'selesai')
                                    <span class="badge-status badge-status-approved">
                                        <i class="bi bi-check-circle-fill"></i> Selesai
                                    </span>
                                @elseif ($report->status === 'ditolak')
                                    <span class="badge-status badge-status-rejected">
                                        <i class="bi bi-x-circle-fill"></i> Ditolak
                                    </span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($report->status) }}</span>
                                @endif
                            </td>

                            <td class="text-end pe-3">
                                <a href="{{ route('reports.show', $report) }}"
                                   class="btn btn-sm btn-outline-undip">
                                    <i class="bi bi-eye me-1"></i>Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            @if ($reports->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-end py-3 px-3">
                    {{ $reports->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
