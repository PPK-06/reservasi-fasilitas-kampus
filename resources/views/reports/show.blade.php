@extends('layouts.app')

@section('title', 'Detail Laporan Kerusakan · Wiyata')
@section('page-title', 'Detail Laporan Kerusakan')
@section('page-subtitle', 'Informasi kendala fasilitas dan catatan penanganan oleh petugas sarana')

@section('page-actions')
    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm px-3">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar
    </a>
@endsection

@section('content')

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

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card card-undip border shadow-sm">
            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-start mb-4 pb-3 border-bottom">
                    <div>
                        <div class="text-muted small">
                            Nomor Tiket: #{{ $report->id }}
                        </div>
                        <h4 class="mb-0 fw-bold font-heading text-dark">
                            {{ $report->facility->name }}
                        </h4>
                    </div>

                    <div>
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
                    </div>
                </div>

                <dl class="row mb-0 g-3">
                    <dt class="col-sm-4 text-muted small fw-semibold">Lokasi Gedung</dt>
                    <dd class="col-sm-8 text-dark mb-2">
                        <i class="bi bi-geo-alt me-1 text-danger"></i>{{ $report->facility->location ?: '-' }}
                    </dd>

                    <dt class="col-sm-4 text-muted small fw-semibold">Kategori Kerusakan</dt>
                    <dd class="col-sm-8 mb-2">
                        <span class="badge bg-light text-dark border">
                            {{ $categoryLabels[$report->category] ?? $report->category }}
                        </span>
                    </dd>

                    <dt class="col-sm-4 text-muted small fw-semibold">Waktu Pelaporan</dt>
                    <dd class="col-sm-8 text-dark small mb-2">
                        {{ $report->created_at->format('d/m/Y H:i') }} WIB
                    </dd>

                    <dt class="col-sm-4 text-muted small fw-semibold">Deskripsi Kendala</dt>
                    <dd class="col-sm-8 text-dark mb-0" style="line-height: 1.6;">
                        {!! nl2br(e($report->description)) !!}
                    </dd>
                </dl>

            </div>
        </div>

        {{-- Resolusi Petugas --}}
        <div class="card card-undip border shadow-sm mt-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold font-heading text-dark">
                    <i class="bi bi-clipboard-check me-2 text-primary"></i>Catatan Resolusi Petugas
                </h6>
            </div>
            <div class="card-body p-4">
                @if ($report->resolution_note)
                    <div class="p-3 rounded-2 bg-light border text-dark" style="line-height: 1.6;">
                        {!! nl2br(e($report->resolution_note)) !!}
                    </div>
                @else
                    <div class="text-muted small">
                        <i class="bi bi-info-circle me-1"></i>Belum ada catatan resolusi dari petugas sarana kampus.
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Kolom Kanan: Foto Kerusakan --}}
    <div class="col-lg-5">
        <div class="card card-undip border shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold font-heading text-dark">
                    <i class="bi bi-images me-2 text-primary"></i>Foto Bukti Kerusakan
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    @forelse ($report->photos as $photo)
                        <div class="col-12">
                            <div class="card border rounded-3 overflow-hidden shadow-xs">
                                <div role="button"
                                     data-bs-toggle="modal"
                                     data-bs-target="#userPhotoModal{{ $photo->id }}"
                                     class="d-flex align-items-center justify-content-center p-2 position-relative"
                                     style="background: #0f172a; height: 260px; cursor: pointer;"
                                     title="Klik untuk memperbesar foto">
                                    <img src="{{ asset('uploads/reports/'.$photo->file_name) }}"
                                         alt="Foto kerusakan fasilitas {{ $report->facility->name }}"
                                         class="w-100 h-100"
                                         style="object-fit: contain;">
                                    <span class="position-absolute bottom-0 end-0 m-2 badge bg-dark bg-opacity-75 text-white border border-secondary small">
                                        <i class="bi bi-arrows-fullscreen me-1"></i>Perbesar
                                    </span>
                                </div>
                                <div class="p-2 bg-white border-top d-flex align-items-center justify-content-between">
                                    <span class="small text-muted text-truncate" style="max-width: 160px;">Foto #{{ $loop->iteration }}</span>
                                    <button type="button"
                                            class="btn btn-outline-undip btn-sm py-1 px-2"
                                            style="font-size: 0.75rem;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#userPhotoModal{{ $photo->id }}">
                                        <i class="bi bi-zoom-in me-1"></i>Lihat Detil
                                    </button>
                                </div>
                            </div>

                            {{-- Modal Tampilan Foto Penuh / Detil --}}
                            <div class="modal fade" id="userPhotoModal{{ $photo->id }}" tabindex="-1" aria-labelledby="userPhotoModalLabel{{ $photo->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-xl">
                                    <div class="modal-content border-0 shadow-lg">
                                        <div class="modal-header bg-light py-2 px-3 border-bottom">
                                            <h6 class="modal-title font-heading fw-bold text-dark mb-0" id="userPhotoModalLabel{{ $photo->id }}">
                                                <i class="bi bi-image me-2 text-primary"></i>Bukti Kerusakan #{{ $loop->iteration }} - {{ $report->facility->name }}
                                            </h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                        </div>
                                        <div class="modal-body p-2 p-md-3 text-center" style="background: #090d16;">
                                            <img src="{{ asset('uploads/reports/'.$photo->file_name) }}"
                                                 alt="Foto bukti kerusakan resolusi penuh"
                                                 class="img-fluid rounded"
                                                 style="max-height: 80vh; width: auto; object-fit: contain;">
                                        </div>
                                        <div class="modal-footer py-2 px-3 bg-light border-top d-flex justify-content-between">
                                            <a href="{{ asset('uploads/reports/'.$photo->file_name) }}"
                                               target="_blank"
                                               class="btn btn-outline-undip btn-sm">
                                                <i class="bi bi-box-arrow-up-right me-1"></i>Buka Resolusi Asli di Tab Baru
                                            </a>
                                            <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">
                                                Tutup
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-4 text-muted small">
                            <i class="bi bi-image fs-2 d-block mb-2"></i>
                            Tidak ada foto lampiran.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
