@extends('layouts.app')

@section('title', 'Detail Laporan')
@section('page-title', 'Detail Laporan')

@section('page-actions')
    <a href="{{ route('reports.index') }}"
       class="btn btn-outline-secondary">
        Kembali
    </a>
@endsection

@section('content')

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
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

    $statusClass = match ($report->status) {
        'baru' => 'secondary',
        'diproses' => 'warning',
        'selesai' => 'success',
        'ditolak' => 'danger',
        default => 'secondary',
    };
@endphp

<div class="row g-4">

    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <div class="text-muted small">
                            Laporan #{{ $report->id }}
                        </div>

                        <h5 class="mb-0">
                            {{ $report->facility->name }}
                        </h5>
                    </div>

                    <span class="badge text-bg-{{ $statusClass }}">
                        {{ ucfirst($report->status) }}
                    </span>
                </div>

                <dl class="row mb-0">

                    <dt class="col-sm-4">Lokasi</dt>
                    <dd class="col-sm-8">
                        {{ $report->facility->location ?: '-' }}
                    </dd>

                    <dt class="col-sm-4">Kategori</dt>
                    <dd class="col-sm-8">
                        {{ $categoryLabels[$report->category] ?? $report->category }}
                    </dd>

                    <dt class="col-sm-4">Tanggal Laporan</dt>
                    <dd class="col-sm-8">
                        {{ $report->created_at->format('d/m/Y H:i') }}
                    </dd>

                    <dt class="col-sm-4">Deskripsi</dt>
                    <dd class="col-sm-8">
                        {!! nl2br(e($report->description)) !!}
                    </dd>

                </dl>

            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-body p-4">
                <h5>Catatan Resolusi Petugas</h5>

                @if ($report->resolution_note)
                    <p class="mb-0">
                        {!! nl2br(e($report->resolution_note)) !!}
                    </p>
                @else
                    <p class="text-muted mb-0">
                        Belum ada catatan resolusi dari petugas.
                    </p>
                @endif
            </div>
        </div>
    </div>


    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h5 class="mb-3">Foto Kerusakan</h5>

                <div class="row g-3">

                    @forelse ($report->photos as $photo)
                        <div class="col-12">
                            <img
                                src="{{ asset('uploads/reports/'.$photo->file_name) }}"
                                alt="Foto kerusakan"
                                class="img-fluid rounded border"
                                style="width: 100%; max-height: 350px; object-fit: cover;">
                        </div>
                    @empty
                        <div class="col-12">
                            <p class="text-muted mb-0">
                                Tidak ada foto.
                            </p>
                        </div>
                    @endforelse

                </div>
            </div>
        </div>
    </div>

</div>

@endsection
