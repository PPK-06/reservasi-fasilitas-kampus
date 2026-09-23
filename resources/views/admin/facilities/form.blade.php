@extends('layouts.app')

@php
    $isEdit = $facility->exists;
    $title = $isEdit ? 'Edit Fasilitas' : 'Tambah Fasilitas';
@endphp

@section('title', $title . ' — Sistem Fasilitas Kampus')

@section('page-title', $title)
@section('page-actions')
    <a href="{{ route('admin.facilities.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ $isEdit ? route('admin.facilities.update', $facility) : route('admin.facilities.store') }}" method="POST">
                    @csrf
                    @if ($isEdit)
                        @method('PATCH')
                    @endif

                    {{-- Nama --}}
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Nama Fasilitas <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $facility->name) }}" required maxlength="100"
                               placeholder="Contoh: Ruang Kelas 101">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        {{-- Tipe --}}
                        <div class="col-md-6">
                            <label for="type" class="form-label fw-semibold">Tipe <span class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="" disabled {{ old('type', $facility->type) ? '' : 'selected' }}>Pilih Tipe</option>
                                @foreach (\App\Models\Facility::TYPES as $value => $label)
                                    <option value="{{ $value }}" {{ old('type', $facility->type) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Lokasi --}}
                        <div class="col-md-6 mt-3 mt-md-0">
                            <label for="location" class="form-label fw-semibold">Lokasi <span class="text-danger">*</span></label>
                            <select name="location" id="location" class="form-select @error('location') is-invalid @enderror" required>
                                <option value="" disabled {{ old('location', $facility->location) ? '' : 'selected' }}>Pilih Lokasi</option>
                                @foreach (\App\Models\Facility::LOCATIONS as $value => $label)
                                    <option value="{{ $value }}" {{ old('location', $facility->location) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        {{-- Kapasitas --}}
                        <div class="col-md-6">
                            <label for="capacity" class="form-label fw-semibold">Kapasitas</label>
                            <input type="number" name="capacity" id="capacity"
                                   class="form-control @error('capacity') is-invalid @enderror"
                                   value="{{ old('capacity', $facility->capacity) }}" min="1" max="65535"
                                   placeholder="Kosongkan untuk tipe Alat">
                            <div class="form-text">Maksimal 65535 orang.</div>
                            @error('capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div class="col-md-6 mt-3 mt-md-0">
                            <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="" disabled {{ old('status', $facility->status) ? '' : 'selected' }}>Pilih Status</option>
                                @foreach (\App\Models\Facility::STATUSES as $value => $label)
                                    <option value="{{ $value }}" {{ old('status', $facility->status) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Deskripsi --}}
                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold">Deskripsi</label>
                        <textarea name="description" id="description" rows="4"
                                  class="form-control @error('description') is-invalid @enderror"
                                  maxlength="1000">{{ old('description', $facility->description) }}</textarea>
                        <div class="form-text">Maksimal 1000 karakter.</div>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tombol Submit --}}
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.facilities.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
