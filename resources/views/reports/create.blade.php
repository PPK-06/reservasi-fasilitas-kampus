@extends('layouts.app')

@section('title', 'Lapor Kerusakan · Wiyata')
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
                      enctype="multipart/form-data"
                      novalidate
                      id="reportForm">

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

                    {{-- Komponen Upload Foto Bukti Kerusakan (Adapted FileUploadCard) --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-dark d-flex justify-content-between align-items-center">
                            <span>Foto Bukti Kerusakan <span class="text-danger">*</span></span>
                            <span class="text-muted small" id="fileCountBadge">0 dari 3 foto</span>
                        </label>

                        <div class="file-upload-card card border rounded-3 bg-white shadow-xs overflow-hidden" id="fileUploadCard">
                            <div class="p-3 p-md-4">
                                <div class="d-flex align-items-start justify-content-between mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                                             style="width: 44px; height: 44px; background-color: var(--undip-blue-surface); color: var(--undip-navy); flex-shrink: 0;">
                                            <i class="bi bi-cloud-arrow-up fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1 font-heading text-dark">Unggah Foto Bukti</h6>
                                            <p class="text-muted small mb-0">Pilih atau seret foto bukti kerusakan (maksimal 3 foto, dapat ditambahkan satu per satu).</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Dropzone Area --}}
                                <div id="dropzoneBox"
                                     class="border border-2 border-dashed rounded-3 p-4 text-center cursor-pointer transition-all"
                                     style="background-color: #fafbfc; border-color: #cbd5e1; cursor: pointer;">
                                    
                                    <input type="file"
                                           id="photos"
                                           name="photos[]"
                                           multiple
                                           accept="image/jpeg,image/png,image/jpg"
                                           class="d-none">

                                    <div class="py-2">
                                        <i class="bi bi-cloud-arrow-up text-muted" style="font-size: 2.25rem;"></i>
                                        <p class="fw-semibold text-dark mb-1 mt-2">Pilih foto atau seret & lepas ke sini</p>
                                        <p class="text-muted small mb-3">Format JPG, JPEG, atau PNG, maks 3 MB per foto.</p>
                                        <button type="button" class="btn btn-outline-undip btn-sm px-3 pointer-events-none">
                                            <i class="bi bi-folder2-open me-1"></i>Pilih Foto dari Perangkat
                                        </button>
                                    </div>
                                </div>

                                <div id="photosClientError" class="invalid-feedback d-block small mt-2" style="display: none !important;">
                                    <i class="bi bi-exclamation-circle me-1"></i><span id="photosClientErrorText"></span>
                                </div>

                                @error('photos')
                                    <div class="invalid-feedback d-block small mt-2">
                                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror

                                @error('photos.*')
                                    <div class="invalid-feedback d-block small mt-1">
                                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- Daftar Foto Terpilih --}}
                            <div id="filesListContainer" class="p-3 bg-light border-top" style="display: none;">
                                <div class="fw-semibold small text-muted mb-2 px-1">
                                    Foto Terpilih (<span id="selectedCountText">0</span>/3):
                                </div>
                                <ul class="list-unstyled mb-0 d-flex flex-column gap-2" id="filesList">
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end pt-3 border-top">
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
(function () {
    const dropzone = document.getElementById('dropzoneBox');
    const fileInput = document.getElementById('photos');
    const filesList = document.getElementById('filesList');
    const filesListContainer = document.getElementById('filesListContainer');
    const fileCountBadge = document.getElementById('fileCountBadge');
    const selectedCountText = document.getElementById('selectedCountText');
    const errorBox = document.getElementById('photosClientError');
    const errorText = document.getElementById('photosClientErrorText');
    const form = document.getElementById('reportForm');

    let accumulatedFiles = [];
    const MAX_FILES = 3;
    const MAX_SIZE = 3 * 1024 * 1024; // 3MB

    function showError(msg) {
        if (errorBox && errorText) {
            errorText.textContent = msg;
            errorBox.style.setProperty('display', 'block', 'important');
        }
    }

    function clearError() {
        if (errorBox) {
            errorBox.style.setProperty('display', 'none', 'important');
        }
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 KB';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function updateFileInput() {
        const dt = new DataTransfer();
        accumulatedFiles.forEach(file => dt.items.add(file));
        fileInput.files = dt.files;

        const count = accumulatedFiles.length;
        if (fileCountBadge) fileCountBadge.textContent = `${count} dari ${MAX_FILES} foto`;
        if (selectedCountText) selectedCountText.textContent = count;

        if (count > 0) {
            filesListContainer.style.display = 'block';
            clearError();
        } else {
            filesListContainer.style.display = 'none';
        }
        renderFileList();
    }

    function renderFileList() {
        filesList.innerHTML = '';
        accumulatedFiles.forEach((file, index) => {
            const li = document.createElement('li');
            li.className = 'p-3 bg-white rounded-2 border d-flex align-items-center justify-content-between shadow-xs';

            const imgId = `preview-thumb-${index}`;
            const reader = new FileReader();
            reader.onload = (e) => {
                const el = document.getElementById(imgId);
                if (el) el.src = e.target.result;
            };
            reader.readAsDataURL(file);

            li.innerHTML = `
                <div class="d-flex align-items-center gap-3 overflow-hidden me-2">
                    <img id="${imgId}" src="" alt="Thumbnail" class="rounded object-fit-cover border" style="width: 44px; height: 44px; background: #f1f5f9; flex-shrink: 0;">
                    <div class="overflow-hidden">
                        <p class="mb-0 fw-semibold text-dark text-truncate small" style="max-width: 250px;" title="${file.name}">${file.name}</p>
                        <div class="text-muted d-flex align-items-center gap-2" style="font-size: 0.75rem;">
                            <span>${formatFileSize(file.size)}</span>
                            <span>•</span>
                            <span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Siap diunggah</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm rounded-circle d-flex align-items-center justify-content-center p-0" style="width: 32px; height: 32px;" title="Hapus foto" data-index="${index}">
                    <i class="bi bi-trash3"></i>
                </button>
            `;

            li.querySelector('button').addEventListener('click', (e) => {
                e.stopPropagation();
                accumulatedFiles.splice(index, 1);
                updateFileInput();
            });

            filesList.appendChild(li);
        });
    }

    function handleAddFiles(files) {
        clearError();
        const allowed = ['image/jpeg', 'image/png', 'image/jpg'];
        let rejectedType = false;
        let rejectedSize = false;
        let rejectedCount = false;

        Array.from(files).forEach(file => {
            if (!allowed.includes(file.type.toLowerCase())) {
                rejectedType = true;
                return;
            }
            if (file.size > MAX_SIZE) {
                rejectedSize = true;
                return;
            }
            if (accumulatedFiles.length >= MAX_FILES) {
                rejectedCount = true;
                return;
            }
            // Cegah duplikasi nama & ukuran
            const isDuplicate = accumulatedFiles.some(f => f.name === file.name && f.size === file.size);
            if (!isDuplicate) {
                accumulatedFiles.push(file);
            }
        });

        if (rejectedType) showError('Hanya berkas gambar format JPG, JPEG, atau PNG yang diperbolehkan.');
        else if (rejectedSize) showError('Ukuran foto tidak boleh melebihi 3 MB.');
        else if (rejectedCount) showError(`Maksimal ${MAX_FILES} foto diperbolehkan.`);

        updateFileInput();
    }

    dropzone?.addEventListener('click', () => fileInput.click());

    fileInput?.addEventListener('change', function () {
        if (this.files.length > 0) {
            handleAddFiles(this.files);
            // Reset input value supaya user bisa memilih file yang sama atau memilih lagi
            this.value = '';
            // Sinkronkan kembali file yang terakumulasi
            const dt = new DataTransfer();
            accumulatedFiles.forEach(f => dt.items.add(f));
            fileInput.files = dt.files;
        }
    });

    dropzone?.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.style.borderColor = 'var(--undip-navy)';
        dropzone.style.backgroundColor = 'var(--undip-blue-surface)';
    });

    ['dragleave', 'dragend'].forEach(ev => {
        dropzone?.addEventListener(ev, () => {
            dropzone.style.borderColor = '#cbd5e1';
            dropzone.style.backgroundColor = '#fafbfc';
        });
    });

    dropzone?.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.style.borderColor = '#cbd5e1';
        dropzone.style.backgroundColor = '#fafbfc';
        if (e.dataTransfer && e.dataTransfer.files.length > 0) {
            handleAddFiles(e.dataTransfer.files);
        }
    });

    form?.addEventListener('submit', function (e) {
        clearError();
        const facility = document.getElementById('facility_id');
        const category = document.getElementById('category');
        const description = document.getElementById('description');

        let valid = true;

        if (facility && !facility.value) {
            facility.classList.add('is-invalid');
            valid = false;
        } else if (facility) {
            facility.classList.remove('is-invalid');
        }

        if (category && !category.value) {
            category.classList.add('is-invalid');
            valid = false;
        } else if (category) {
            category.classList.remove('is-invalid');
        }

        if (description && (!description.value.trim() || description.value.trim().length < 10)) {
            description.classList.add('is-invalid');
            valid = false;
        } else if (description) {
            description.classList.remove('is-invalid');
        }

        if (accumulatedFiles.length === 0) {
            showError('Foto bukti kerusakan wajib dilampirkan (minimal 1 foto).');
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
        }
    });
})();
</script>
@endpush
