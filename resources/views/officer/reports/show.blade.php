@extends('layouts.app')

@section('title', 'Detail Laporan · Wiyata')
@section('page-title', 'Detail Laporan')

@section('page-actions')
    <a href="{{ route('officer.reports.index') }}"
       class="btn btn-outline-secondary">
        Kembali
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

        <div class="card card-undip border shadow-sm">
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


        <div class="card card-undip border shadow-sm mt-4">
            <div class="card-body p-4">

                <h5 class="mb-3 font-heading fw-bold">
                    Foto Kerusakan
                </h5>

                <div class="row g-3">
                    @forelse ($report->photos as $photo)
                        <div class="col-md-6">
                            <div class="card border rounded-3 overflow-hidden shadow-xs h-100">
                                <div role="button"
                                     data-bs-toggle="modal"
                                     data-bs-target="#photoModal{{ $photo->id }}"
                                     class="d-flex align-items-center justify-content-center p-2 position-relative"
                                     style="background: #0f172a; height: 260px; cursor: pointer;"
                                     title="Klik untuk memperbesar foto">
                                    <img src="{{ asset('uploads/reports/'.$photo->file_name) }}"
                                         alt="Foto bukti kerusakan"
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
                                            data-bs-target="#photoModal{{ $photo->id }}">
                                        <i class="bi bi-zoom-in me-1"></i>Lihat Detil
                                    </button>
                                </div>
                            </div>

                            {{-- Modal Tampilan Foto Penuh / Detil --}}
                            <div class="modal fade" id="photoModal{{ $photo->id }}" tabindex="-1" aria-labelledby="photoModalLabel{{ $photo->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-xl">
                                    <div class="modal-content border-0 shadow-lg">
                                        <div class="modal-header bg-light py-2 px-3 border-bottom">
                                            <h6 class="modal-title font-heading fw-bold text-dark mb-0" id="photoModalLabel{{ $photo->id }}">
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

        <div class="card card-undip border shadow-sm">
            <div class="card-body p-4">

                <h5 class="mb-3 font-heading fw-bold">
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
                                class="btn btn-undip-primary">

                            Simpan Perubahan

                        </button>

                    </div>

                </form>

            </div>
        </div>


        <div class="card card-undip border shadow-sm mt-4">
            <div class="card-body p-4">

                <h5 class="mb-3 font-heading fw-bold">
                    Status Fasilitas
                </h5>


                <p class="mb-3">
                    Status saat ini:
                    <strong>
                        {{ $report->facility->statusLabel() }}
                    </strong>
                </p>


                @if ($report->facility->status === 'active')

                    <button type="button"
                            class="btn btn-warning w-100"
                            data-bs-toggle="modal"
                            data-bs-target="#statusModal-{{ $report->facility->id }}">
                        Tandai Dalam Perbaikan
                    </button>

                @elseif ($report->facility->status === 'under_maintenance')

                    <div class="alert alert-warning mb-0">
                        Fasilitas ini sedang berstatus dalam perbaikan.
                    </div>

                @else

                    <div class="alert alert-secondary mb-0">
                        Status fasilitas:
                        {{ $report->facility->statusLabel() }}
                    </div>

                @endif

            </div>
        </div>

    </div>

</div>

@if ($report->facility->status === 'active')
    <div class="modal fade" id="statusModal-{{ $report->facility->id }}" tabindex="-1"
         aria-labelledby="statusModalLabel-{{ $report->facility->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered {{ $upcomingReservations->isNotEmpty() ? 'modal-lg' : '' }}">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="statusModalLabel-{{ $report->facility->id }}">
                        <i class="bi bi-tools text-warning me-2"></i>Tandai Perbaikan Fasilitas
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body pt-2">
                    <p class="text-muted">
                        Kamu yakin ingin menandai fasilitas
                        <strong>{{ $report->facility->name }}</strong> dalam perbaikan?
                        Pengguna tidak akan bisa mengajukan reservasi baru.
                    </p>

                    <x-d4-warning :reservations="$upcomingReservations" />
                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-arrow-left me-1"></i>Batal
                    </button>
                    <form method="POST" action="{{ route('officer.facilities.status', $report->facility) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="under_maintenance">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-tools me-1"></i>Tandai Dalam Perbaikan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

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
