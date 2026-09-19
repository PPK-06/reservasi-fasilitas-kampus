@extends('layouts.app')

@section('title', 'Lapor Kerusakan')
@section('page-title', 'Lapor Kerusakan Fasilitas')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Data belum dapat dikirim.</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body p-4">

                <p class="text-muted">
                    Isi informasi kerusakan fasilitas dan lampirkan 1 sampai 3 foto.
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
                        <label for="facility_id" class="form-label">
                            Fasilitas <span class="text-danger">*</span>
                        </label>

                        @if ($facilityFromQuery)
                            <div class="form-control bg-light">
                                {{ $facilityFromQuery->name }}
                                @if ($facilityFromQuery->location)
                                    — {{ $facilityFromQuery->location }}
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

                                <option value="">Pilih fasilitas</option>

                                @foreach ($facilities as $facility)
                                    <option
                                        value="{{ $facility->id }}"
                                        @selected(old('facility_id') == $facility->id)>
                                        {{ $facility->name }}
                                        @if ($facility->location)
                                            — {{ $facility->location }}
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
                        <label for="category" class="form-label">
                            Kategori Kerusakan <span class="text-danger">*</span>
                        </label>

                        <select
                            name="category"
                            id="category"
                            class="form-select @error('category') is-invalid @enderror"
                            required>

                            <option value="">Pilih kategori</option>

                            <option value="kerusakan_alat"
                                @selected(old('category') === 'kerusakan_alat')>
                                Kerusakan Alat
                            </option>

                            <option value="kelistrikan"
                                @selected(old('category') === 'kelistrikan')>
                                Kelistrikan
                            </option>

                            <option value="pendingin_ruangan"
                                @selected(old('category') === 'pendingin_ruangan')>
                                Pendingin Ruangan
                            </option>

                            <option value="furnitur"
                                @selected(old('category') === 'furnitur')>
                                Furnitur
                            </option>

                            <option value="kebersihan"
                                @selected(old('category') === 'kebersihan')>
                                Kebersihan
                            </option>

                            <option value="lainnya"
                                @selected(old('category') === 'lainnya')>
                                Lainnya
                            </option>
                        </select>

                        @error('category')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">
                            Deskripsi Kerusakan <span class="text-danger">*</span>
                        </label>

                        <textarea
                            name="description"
                            id="description"
                            rows="5"
                            minlength="10"
                            maxlength="1000"
                            class="form-control @error('description') is-invalid @enderror"
                            placeholder="Jelaskan kondisi atau kerusakan yang ditemukan..."
                            required>{{ old('description') }}</textarea>

                        <div class="form-text">
                            Minimal 10 karakter, maksimal 1000 karakter.
                        </div>

                        @error('description')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="photos" class="form-label">
                            Foto Kerusakan <span class="text-danger">*</span>
                        </label>

                        <input
                            type="file"
                            name="photos[]"
                            id="photos"
                            class="form-control @error('photos') is-invalid @enderror @error('photos.*') is-invalid @enderror"
                            accept=".jpg,.jpeg,.png"
                            multiple
                            required>

                        <div class="form-text">
                            Unggah 1–3 foto JPG, JPEG, atau PNG. Maksimal 3 MB per foto.
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

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            Kirim Laporan
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