@extends('layouts.app')

@section('title', 'Lapor Kerusakan — Sistem Fasilitas Kampus Undip')
@section('page-title', 'Formulir Laporan Kerusakan')
@section('page-subtitle', 'Sampaikan kendala fasilitas kampus untuk penanganan cepat oleh petugas sarana')

@section('page-actions')
    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm px-3">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-4">
                <div class="fw-semibold mb-1">
                    <i class="bi bi-exclamation-octagon-fill me-2"></i>Data belum dapat dikirim.
                </div>
                <ul class="mb-0 ps-3 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card card-undip border shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold font-heading text-dark">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>Informasi Kendala Fasilitas
                </h6>
            </div>
            <div class="card-body p-4">

                <p class="text-muted small mb-4">
                    Isi informasi kerusakan fasilitas secara terperinci dan lampirkan 1 sampai 3 foto bukti di lokasi.
                </p>

                @php
                    $facilityFromQuery = $selectedFacilityId
                        ? $facilities->firstWhere('id', $selectedFacilityId)
                        : null;
                @endphp

                <form action="{{ route('reports.store') }}"
                      method="POST"
                      enctype="multipart/form-data">

                    @csrf

                    <div class="mb-3">
                        <label for="facility_id" class="form-label fw-semibold small text-dark">
                            Fasilitas Kampus <span class="text-danger">*</span>
                        </label>

                        @if ($facilityFromQuery)
                            <div class="form-control bg-light py-2">
                                <strong>{{ $facilityFromQuery->name }}</strong>
                                @if ($facilityFromQuery->location)
                                    <span class="text-muted">({{ $facilityFromQuery->location }})</span>
                                @endif
                            </div>

                            <input type="hidden"
                                   name="facility_id"
                                   value="{{ old('facility_id', $facilityFromQuery->id) }}">
                        @else
                            <select
                                name="facility_id"
                                id="facility_id"
                                class="form-select @error('facility_id') is-invalid @enderror"
                                required>

                                <option value="">Pilih fasilitas yang bermasalah</option>

                                @foreach ($facilities as $facility)
                                    <option
                                        value="{{ $facility->id }}"
                                        @selected(old('facility_id') == $facility->id)>
                                        {{ $facility->name }}
                                        @if ($facility->location)
                                            ({{ $facility->location }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        @endif

                        @error('facility_id')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label fw-semibold small text-dark">
                            Kategori Kerusakan <span class="text-danger">*</span>
                        </label>

                        <select
                            name="category"
                            id="category"
                            class="form-select @error('category') is-invalid @enderror"
                            required>

                            <option value="">Pilih kategori kendala</option>
                            <option value="kerusakan_alat" @selected(old('category') === 'kerusakan_alat')>Kerusakan Alat</option>
                            <option value="kelistrikan" @selected(old('category') === 'kelistrikan')>Kelistrikan</option>
                            <option value="pendingin_ruangan" @selected(old('category') === 'pendingin_ruangan')>Pendingin Ruangan (AC)</option>
                            <option value="furnitur" @selected(old('category') === 'furnitur')>Furnitur (Meja/Kursi/Pintu)</option>
                            <option value="kebersihan" @selected(old('category') === 'kebersihan')>Kebersihan</option>
                            <option value="lainnya" @selected(old('category') === 'lainnya')>Lainnya</option>
                        </select>

                        @error('category')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold small text-dark">
                            Deskripsi Kerusakan <span class="text-danger">*</span>
                        </label>

                        <textarea
                            name="description"
                            id="description"
                            rows="4"
                            minlength="10"
                            maxlength="1000"
                            class="form-control @error('description') is-invalid @enderror"
                            placeholder="Jelaskan kondisi atau kerusakan yang ditemukan di ruangan/fasilitas..."
                            required>{{ old('description') }}</textarea>

                        <div class="form-text small">
                            Minimal 10 karakter, maksimal 1000 karakter.
                        </div>

                        @error('description')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="photos" class="form-label fw-semibold small text-dark">
                            Foto Bukti Kerusakan <span class="text-danger">*</span>
                        </label>

                        <input
                            type="file"
                            name="photos[]"
                            id="photos"
                            class="form-control @error('photos') is-invalid @enderror @error('photos.*') is-invalid @enderror"
                            accept=".jpg,.jpeg,.png"
                            multiple
                            required>

                        <div class="form-text small">
                            Unggah 1 sampai 3 foto berformat JPG, JPEG, atau PNG. Maksimal 3 MB per foto.
                        </div>

                        @error('photos')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror

                        @error('photos.*')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-undip-primary btn-sm px-4 shadow-sm">
                            <i class="bi bi-send me-1"></i>Kirim Laporan
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('photos')?.addEventListener('change', function () {
    if (this.files.length > 3) {
        alert('Maksimal 3 foto.');
        this.value = '';
    }
});
</script>
@endpush
