@extends('layouts.app')

@section('title', 'Daftar Fasilitas — Sistem Fasilitas Kampus')

@section('page-title', 'Daftar Fasilitas')
@section('page-actions')
    <span class="text-muted" style="font-size: 0.85rem;">
        {{ $facilities->total() }} fasilitas ditemukan
    </span>
@endsection

@section('content')

{{-- ═══════════════════════════════════════════════════════
     FILTER PENCARIAN — US 2 (tipe, lokasi, kapasitas)
     Dropdown diisi dari konstanta PHP (F6 / D1 / D2)
     ═══════════════════════════════════════════════════════ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('facilities.index') }}" id="filterForm">
            <div class="row g-2 align-items-end">

                {{-- Pencarian nama --}}
                <div class="col-lg-3 col-md-6">
                    <label for="filterSearch" class="form-label fw-semibold mb-1" style="font-size:0.82rem;">
                        <i class="bi bi-search me-1"></i>Cari nama
                    </label>
                    <input type="text" name="search" id="filterSearch"
                           class="form-control form-control-sm"
                           value="{{ request('search') }}"
                           placeholder="Nama fasilitas…">
                </div>

                {{-- Tipe —  F6: loop Facility::TYPES --}}
                <div class="col-lg-2 col-md-6">
                    <label for="filterType" class="form-label fw-semibold mb-1" style="font-size:0.82rem;">
                        <i class="bi bi-tag me-1"></i>Tipe
                    </label>
                    <select name="type" id="filterType" class="form-select form-select-sm">
                        <option value="">Semua Tipe</option>
                        @foreach (\App\Models\Facility::TYPES as $value => $label)
                            <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Lokasi — F6: loop Facility::LOCATIONS --}}
                <div class="col-lg-2 col-md-6">
                    <label for="filterLocation" class="form-label fw-semibold mb-1" style="font-size:0.82rem;">
                        <i class="bi bi-geo-alt me-1"></i>Lokasi
                    </label>
                    <select name="location" id="filterLocation" class="form-select form-select-sm">
                        <option value="">Semua Lokasi</option>
                        @foreach (\App\Models\Facility::LOCATIONS as $value => $label)
                            <option value="{{ $value }}" {{ request('location') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Kapasitas minimal — A4: hanya berlaku pada capacity IS NOT NULL --}}
                <div class="col-lg-2 col-md-6">
                    <label for="filterCapacity" class="form-label fw-semibold mb-1" style="font-size:0.82rem;">
                        <i class="bi bi-people me-1"></i>Kapasitas min.
                    </label>
                    <input type="number" name="capacity" id="filterCapacity"
                           class="form-control form-control-sm"
                           value="{{ request('capacity') }}"
                           min="1" placeholder="cth: 20">
                </div>

                {{-- Tombol --}}
                <div class="col-lg-3 col-md-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    <a href="{{ route('facilities.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                    </a>
                </div>

            </div>

            {{-- Catatan A4 --}}
            @if (request('capacity'))
                <div class="mt-2">
                    <small class="text-muted fst-italic">
                        <i class="bi bi-info-circle me-1"></i>Filter kapasitas tidak berlaku untuk tipe Alat
                        (kapasitas tidak relevan).
                    </small>
                </div>
            @endif
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     GRID FASILITAS — D5: non-aktif tetap muncul
     ═══════════════════════════════════════════════════════ --}}
@if ($facilities->count())
    <div class="row g-3">
        @foreach ($facilities as $facility)
            @php
                $isActive  = $facility->status === 'active';
                $isMaint   = $facility->status === 'under_maintenance';
                $isInactive = $facility->status === 'inactive';
            @endphp

            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm {{ !$isActive ? 'opacity-75' : '' }}"
                     style="{{ !$isActive ? 'filter: grayscale(30%);' : '' }}">

                    {{-- Header kartu dengan badge status --}}
                    <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center py-2 px-3">
                        <span class="badge {{ $isActive ? 'text-bg-success' : ($isMaint ? 'text-bg-warning' : 'text-bg-secondary') }}"
                              style="font-size: 0.72rem;">
                            @if ($isMaint)
                                <i class="bi bi-tools me-1"></i>
                            @elseif ($isInactive)
                                <i class="bi bi-x-circle me-1"></i>
                            @else
                                <i class="bi bi-check-circle me-1"></i>
                            @endif
                            {{ $facility->statusLabel() }}
                        </span>
                        <span class="text-muted" style="font-size: 0.72rem;">
                            {{ $facility->type }}
                        </span>
                    </div>

                    {{-- Body --}}
                    <div class="card-body pt-2">
                        <h6 class="card-title fw-bold mb-1 {{ !$isActive ? 'text-muted' : 'text-dark' }}">
                            {{ $facility->name }}
                        </h6>

                        <div class="d-flex flex-column gap-1 mb-2" style="font-size: 0.82rem;">
                            <span class="text-muted">
                                <i class="bi bi-geo-alt-fill me-1"></i>{{ $facility->location }}
                            </span>
                            @if ($facility->type !== 'Alat' && !is_null($facility->capacity))
                                <span class="text-muted">
                                    <i class="bi bi-people-fill me-1"></i>{{ $facility->capacity }} orang
                                </span>
                            @endif
                        </div>

                        @if ($facility->description)
                            <p class="card-text text-muted mb-0" style="font-size: 0.8rem; line-height: 1.45;">
                                {{ Str::limit($facility->description, 80) }}
                            </p>
                        @endif
                    </div>

                    {{-- Footer: tombol detail --}}
                    <div class="card-footer bg-white border-top-0 pb-3 px-3">
                        <a href="{{ route('facilities.show', $facility) }}"
                           class="btn btn-sm w-100 {{ $isActive ? 'btn-outline-primary' : 'btn-outline-secondary' }}">
                            <i class="bi bi-eye me-1"></i>Lihat Detail
                        </a>

                        {{-- D5: penanda visual untuk fasilitas non-aktif --}}
                        @if ($isMaint)
                            <div class="text-center mt-2">
                                <small class="text-warning fw-semibold" style="font-size: 0.73rem;">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Sedang dalam perbaikan
                                </small>
                            </div>
                        @elseif ($isInactive)
                            <div class="text-center mt-2">
                                <small class="text-secondary fw-semibold" style="font-size: 0.73rem;">
                                    <i class="bi bi-slash-circle me-1"></i>Tidak tersedia
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Paginasi — Bootstrap 5 (F11, AppServiceProvider) --}}
    @if ($facilities->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $facilities->links() }}
        </div>
    @endif

@else
    {{-- Kosong --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-building fs-1 text-muted d-block mb-2"></i>
            <h6 class="fw-bold text-muted">Tidak ada fasilitas ditemukan</h6>
            <p class="text-muted mb-3" style="font-size: 0.85rem;">
                Coba ubah filter pencarian atau
                <a href="{{ route('facilities.index') }}" class="text-decoration-none">reset semua filter</a>.
            </p>
        </div>
    </div>
@endif

@endsection
