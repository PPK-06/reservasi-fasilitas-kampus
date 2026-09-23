@extends('layouts.app')

@section('title', 'Detail Laporan')
@section('page-title', 'Detail Laporan')

@section('page-actions')
    <a href="{{ route('officer.reports.index') }}"
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

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Perubahan belum dapat disimpan.</strong>

        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
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

                    <dt class="col-sm-4">
                        Pelapor
                    </dt>

                    <dd class="col-sm-8">
                        {{ $report->user->name }}
                        <div class="small text-muted">
                            {{ $report->user->email }}
                        </div>
                    </dd>


                    <dt class="col-sm-4">
                        Lokasi
                    </dt>

                    <dd class="col-sm-8">
                        {{ $report->facility->location ?: '-' }}
                    </dd>


                    <dt class="col-sm-4">
                        Kategori
                    </dt>

                    <dd class="col-sm-8">
                        {{ $categoryLabels[$report->category] ?? $report->category }}
                    </dd>


                    <dt class="col-sm-4">
                        Dilaporkan
                    </dt>

                    <dd class="col-sm-8">
                        {{ $report->created_at->format('d/m/Y H:i') }}
                    </dd>


                    <dt class="col-sm-4">
                        Deskripsi
                    </dt>

                    <dd class="col-sm-8">
                        {!! nl2br(e($report->description)) !!}
                    </dd>

                </dl>

            </div>
        </div>


        <div class="card shadow-sm mt-4">
            <div class="card-body p-4">

                <h5 class="mb-3">
                    Foto Kerusakan
                </h5>

                <div class="row g-3">

                    @forelse ($report->photos as $photo)

                        <div class="col-md-6">

                            <img
                                src="{{ asset('uploads/reports/'.$photo->file_name) }}"
                                alt="Foto kerusakan"
                                class="img-fluid rounded border"
                                style="width:100%; height:250px; object-fit:cover;">

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


    <div class="col-lg-5">

        <div class="card shadow-sm">
            <div class="card-body p-4">

                <h5 class="mb-3">
                    Tindak Lanjut Laporan
                </h5>


                <form method="POST"
                      action="{{ route('officer.reports.update', $report) }}">

                    @csrf
                    @method('PATCH')


                    <div class="mb-3">

                        <label for="status"
                               class="form-label">

                            Status
                            <span class="text-danger">*</span>

                        </label>


                        <select name="status"
                                id="status"
                                class="form-select @error('status') is-invalid @enderror"
                                required>

                            <option value="baru"
                                @selected(old('status', $report->status) === 'baru')>
                                Baru
                            </option>

                            <option value="diproses"
                                @selected(old('status', $report->status) === 'diproses')>
                                Diproses
                            </option>

                            <option value="selesai"
                                @selected(old('status', $report->status) === 'selesai')>
                                Selesai
                            </option>

                            <option value="ditolak"
                                @selected(old('status', $report->status) === 'ditolak')>
                                Ditolak
                            </option>

                        </select>

                        @error('status')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    <div class="mb-3">

                        <label for="resolution_note"
                               class="form-label">

                            Catatan Resolusi

                        </label>


                        <textarea
                            name="resolution_note"
                            id="resolution_note"
                            rows="5"
                            minlength="10"
                            maxlength="1000"
                            class="form-control @error('resolution_note') is-invalid @enderror"
                            placeholder="Isi catatan penyelesaian atau alasan penolakan...">{{ old('resolution_note', $report->resolution_note) }}</textarea>


                        <div id="resolution-help"
                             class="form-text">

                            Wajib minimal 10 karakter jika status Selesai atau Ditolak.

                        </div>


                        @error('resolution_note')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    <div class="d-grid">

                        <button type="submit"
                                class="btn btn-primary">

                            Simpan Perubahan

                        </button>

                    </div>

                </form>

            </div>
        </div>


        <div class="card shadow-sm mt-4">
            <div class="card-body p-4">

                <h5 class="mb-3">
                    Status Fasilitas
                </h5>


                <p class="mb-3">
                    Status saat ini:
                    <strong>
                        {{ $report->facility->status }}
                    </strong>
                </p>


                @if ($report->facility->status === 'active')

                    <div class="alert alert-warning small">
                        Perubahan status fasilitas bersifat informatif terhadap proses
                        penanganan laporan. Pastikan fasilitas memang perlu ditandai
                        dalam perbaikan sebelum melanjutkan.
                    </div>


                    @if (\Illuminate\Support\Facades\Route::has('officer.facilities.status'))

                        <form method="POST"
                              action="{{ route('officer.facilities.status', $report->facility) }}">

                            @csrf
                            @method('PATCH')

                            <input type="hidden"
                                   name="status"
                                   value="under_maintenance">

                            <button type="submit"
                                    class="btn btn-warning w-100">

                                Tandai Dalam Perbaikan

                            </button>

                        </form>

                    @else

                        <div class="alert alert-secondary mb-0">
                            Aksi tandai fasilitas akan aktif setelah route
                            <code>officer.facilities.status</code>
                            dari modul fasilitas tersedia.
                        </div>

                    @endif


                @elseif ($report->facility->status === 'under_maintenance')

                    <div class="alert alert-warning mb-0">
                        Fasilitas ini sedang berstatus dalam perbaikan.
                    </div>

                @else

                    <div class="alert alert-secondary mb-0">
                        Status fasilitas:
                        {{ $report->facility->status }}
                    </div>

                @endif

            </div>
        </div>

    </div>

</div>

@endsection


@push('scripts')
<script>
const statusSelect = document.getElementById('status');
const resolutionNote = document.getElementById('resolution_note');

function syncResolutionRequired() {
    if (!statusSelect || !resolutionNote) {
        return;
    }

    const mustHaveNote =
        statusSelect.value === 'selesai' ||
        statusSelect.value === 'ditolak';

    resolutionNote.required = mustHaveNote;
}

statusSelect?.addEventListener('change', syncResolutionRequired);

syncResolutionRequired();
</script>
@endpush
