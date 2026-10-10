@extends('layouts.app')

@section('title', 'Katalog Fasilitas · Wiyata')

@section('page-title', 'Katalog Fasilitas Kampus')
@section('page-subtitle', 'Daftar sarana perkuliahan, laboratorium, aula, dan perlengkapan kegiatan di Tembalang')
@section('page-actions')
    <span class="badge bg-white text-dark border px-3 py-2 fw-semibold shadow-sm" style="font-size: 0.85rem;">
        <i class="bi bi-collection me-1 text-primary"></i> {{ $facilities->total() }} fasilitas ditemukan
    </span>
@endsection

@section('content')

{{-- ═══════════════════════════════════════════════════════
     FILTER PENCARIAN (Tipe, Lokasi, Kapasitas)
     Dropdown dari konstanta model Facility (F6 / D1 / D2)
     ═══════════════════════════════════════════════════════ --}}
<div class="card card-undip mb-4 border shadow-sm">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('facilities.index') }}" id="filterForm">
            <div class="row g-3 align-items-end">

                {{-- Pencarian Nama --}}
                <div class="col-lg-3 col-md-6">
                    <label for="filterSearch" class="form-label fw-semibold small text-dark mb-1">
                        <i class="bi bi-search me-1 text-muted"></i>Cari Nama
                    </label>
                    <input type="text" name="search" id="filterSearch"
                           class="form-control"
                           value="{{ request('search') }}"
                           placeholder="Contoh: Ruang A-101, Aula...">
                </div>

                {{-- Tipe --}}
                <div class="col-lg-3 col-md-6">
                    <label for="filterType" class="form-label fw-semibold small text-dark mb-1">
                        <i class="bi bi-tag me-1 text-muted"></i>Tipe Sarana
                    </label>
                    <select name="type" id="filterType" class="form-select">
                        <option value="">Semua Tipe Fasilitas</option>
                        @foreach (\App\Models\Facility::TYPES as $value => $label)
                            <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Lokasi Gedung --}}
                <div class="col-lg-3 col-md-6">
                    <label for="filterLocation" class="form-label fw-semibold small text-dark mb-1">
                        <i class="bi bi-geo-alt me-1 text-muted"></i>Zona / Lokasi Gedung
                    </label>
                    <select name="location" id="filterLocation" class="form-select">
                        <option value="">Semua Lokasi Kampus</option>
                        @foreach (\App\Models\Facility::LOCATIONS as $value => $label)
                            <option value="{{ $value }}" {{ request('location') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Kapasitas Minimal (A4: hanya untuk capacity IS NOT NULL) --}}
                <div class="col-lg-3 col-md-6">
                    <label for="filterCapacity" class="form-label fw-semibold small text-dark mb-1">
                        <i class="bi bi-people me-1 text-muted"></i>Kapasitas Minimal
                    </label>
                    <div class="d-flex gap-2">
                        <input type="number" name="capacity" id="filterCapacity"
                               class="form-control"
                               value="{{ request('capacity') }}"
                               min="1" placeholder="cth: 30">
                        <button type="submit" class="btn btn-undip-primary px-3" title="Terapkan Filter">
                            <i class="bi bi-funnel"></i>
                        </button>
                        <a href="{{ route('facilities.index') }}" class="btn btn-outline-secondary px-3" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </div>

            </div>

            {{-- Catatan A4 --}}
            @if (request('capacity'))
                <div class="mt-3 pt-2 border-top">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>Filter kapasitas tidak berlaku untuk tipe Alat
                        (kapasitas tidak relevan).
                    </small>
                </div>
            @endif
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     GRID FASILITAS KAMPUS (D5: non-aktif tetap muncul)
     ═══════════════════════════════════════════════════════ --}}
@if ($facilities->count())
    <div class="row g-4">
        @foreach ($facilities as $facility)
            @php
                $isActive   = $facility->status === 'active';
                $isMaint    = $facility->status === 'under_maintenance';
                $isInactive = $facility->status === 'inactive';

                $thumbSrc = $facility->image_url;
                $thumbAlt = $facility->imageAlt();
            @endphp

            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="card card-undip card-undip-interactive h-100 d-flex flex-column border {{ !$isActive ? 'opacity-75' : '' }}"
                     style="{{ !$isActive ? 'filter: grayscale(25%);' : '' }}">

                    {{-- Visual Header Bersih Tanpa Teks Overlay --}}
                    <div class="overflow-hidden" style="height: 160px; border-top-left-radius: 13px; border-top-right-radius: 13px;">
                        @if ($thumbSrc)
                            <img src="{{ $thumbSrc }}"
                                 sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 25vw"
                                 alt="{{ $thumbAlt }}"
                                 class="w-100 h-100 object-fit-cover"
                                 loading="lazy">
                        @else
                            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center"
                                 style="background: linear-gradient(135deg, #07172C 0%, #134074 100%); color: #ffffff;">
                                @if ($facility->type === 'Lapangan')
                                    <i class="bi bi-trophy fs-2 mb-1 text-warning"></i>
                                    <span style="font-size: 0.72rem;" class="text-white-50">Area Olahraga</span>
                                @else
                                    <i class="bi bi-box-seam fs-2 mb-1 text-warning"></i>
                                    <span style="font-size: 0.72rem;" class="text-white-50">Unit Inventaris</span>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Body Kartu --}}
                    <div class="card-body p-3 d-flex flex-column flex-grow-1">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-light text-dark border fw-semibold" style="font-size: 0.72rem;">
                                {{ $facility->type }}
                            </span>
                            @if ($isActive)
                                <span class="badge-status badge-status-approved">
                                    <i class="bi bi-check-circle-fill"></i> Aktif
                                </span>
                            @elseif ($isMaint)
                                <span class="badge-status badge-status-maintenance">
                                    <i class="bi bi-tools"></i> Dalam Perbaikan
                                </span>
                            @else
                                <span class="badge-status badge-status-rejected">
                                    <i class="bi bi-x-circle-fill"></i> Nonaktif
                                </span>
                            @endif
                        </div>

                        <h6 class="fw-bold mb-1 font-heading text-truncate {{ !$isActive ? 'text-muted' : 'text-dark' }}"
                            title="{{ $facility->name }}">
                            {{ $facility->name }}
                        </h6>

                        <div class="d-flex flex-column gap-1 my-2" style="font-size: 0.8rem;">
                            <span class="text-muted text-truncate">
                                <i class="bi bi-geo-alt-fill me-1 text-danger"></i>{{ $facility->location }}
                            </span>
                            @if ($facility->type !== 'Alat' && !is_null($facility->capacity))
                                <span class="text-muted">
                                    <i class="bi bi-people-fill me-1 text-primary"></i>Kapasitas: {{ $facility->capacity }} orang
                                </span>
                            @endif
                        </div>

                        @if ($facility->description)
                            <p class="text-muted small mb-3 flex-grow-1" style="font-size: 0.78rem; line-height: 1.45;">
                                {{ Str::limit($facility->description, 75) }}
                            </p>
                        @else
                            <div class="flex-grow-1"></div>
                        @endif

                        {{-- Tombol Detail --}}
                        <div class="pt-2 border-top mt-auto">
                            <a href="{{ route('facilities.show', $facility) }}"
                               class="btn btn-sm w-100 {{ $isActive ? 'btn-undip-primary' : 'btn-outline-secondary' }}">
                                <i class="bi bi-eye me-1"></i>Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Paginasi Bersih --}}
    @if ($facilities->hasPages())
        <div class="d-flex justify-content-center mt-4 pt-2">
            {{ $facilities->links() }}
        </div>
    @endif

@else
    {{-- Empty State Ramah --}}
    <div class="card card-undip border shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-building-slash fs-1 text-muted d-block mb-3"></i>
            <h5 class="fw-bold text-dark font-heading mb-1">Tidak Ada Fasilitas Ditemukan</h5>
            <p class="text-muted small mb-3">
                Kriteria pencarian tidak cocok dengan sarana yang tersedia di kampus Tembalang.
            </p>
            <a href="{{ route('facilities.index') }}" class="btn btn-undip-primary btn-sm px-4">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Semua Filter
            </a>
        </div>
    </div>
@endif

@endsection
