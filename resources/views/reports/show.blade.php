@extends('layouts.app')

@section('title', 'Detail Laporan Kerusakan | Sistem Fasilitas Kampus Undip')
@section('page-title', 'Detail Laporan Kerusakan')
@section('page-subtitle', 'Informasi kendala fasilitas dan catatan penanganan oleh petugas sarana')

@section('page-actions')
    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm px-3">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar
    </a>
@endsection

@section('content')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endif

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
                            <img
                                src="{{ asset('uploads/reports/'.$photo->file_name) }}"
                                alt="Foto kerusakan fasilitas {{ $report->facility->name }}"
                                class="img-fluid rounded border shadow-sm"
                                style="width: 100%; max-height: 350px; object-fit: cover;"
                                loading="lazy">
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
