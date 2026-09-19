@extends('layouts.app')

@section('title', 'Daftar Laporan')
@section('page-title', 'Daftar Laporan')

@section('content')

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="card shadow-sm mb-4">
    <div class="card-body">

        <form method="GET"
              action="{{ route('officer.reports.index') }}"
              class="row g-3 align-items-end">

            <div class="col-md-3">
                <label for="status" class="form-label">
                    Status
                </label>

                <select name="status"
                        id="status"
                        class="form-select">

                    <option value="">Semua Status</option>

                    <option value="baru"
                        @selected(request('status') === 'baru')>
                        Baru
                    </option>

                    <option value="diproses"
                        @selected(request('status') === 'diproses')>
                        Diproses
                    </option>

                    <option value="selesai"
                        @selected(request('status') === 'selesai')>
                        Selesai
                    </option>

                    <option value="ditolak"
                        @selected(request('status') === 'ditolak')>
                        Ditolak
                    </option>
                </select>
            </div>


            <div class="col-md-3">
                <label for="category" class="form-label">
                    Kategori
                </label>

                <select name="category"
                        id="category"
                        class="form-select">

                    <option value="">Semua Kategori</option>

                    <option value="kerusakan_alat"
                        @selected(request('category') === 'kerusakan_alat')>
                        Kerusakan Alat
                    </option>

                    <option value="kelistrikan"
                        @selected(request('category') === 'kelistrikan')>
                        Kelistrikan
                    </option>

                    <option value="pendingin_ruangan"
                        @selected(request('category') === 'pendingin_ruangan')>
                        Pendingin Ruangan
                    </option>

                    <option value="furnitur"
                        @selected(request('category') === 'furnitur')>
                        Furnitur
                    </option>

                    <option value="kebersihan"
                        @selected(request('category') === 'kebersihan')>
                        Kebersihan
                    </option>

                    <option value="lainnya"
                        @selected(request('category') === 'lainnya')>
                        Lainnya
                    </option>
                </select>
            </div>


            <div class="col-md-4">
                <label for="facility_id" class="form-label">
                    Fasilitas
                </label>

                <select name="facility_id"
                        id="facility_id"
                        class="form-select">

                    <option value="">Semua Fasilitas</option>

                    @foreach ($facilities as $facility)
                        <option value="{{ $facility->id }}"
                            @selected((string) request('facility_id') === (string) $facility->id)>
                            {{ $facility->name }}
                        </option>
                    @endforeach
                </select>
            </div>


            <div class="col-md-2 d-grid">
                <button type="submit"
                        class="btn btn-primary">
                    Filter
                </button>
            </div>

            @if (request()->hasAny(['status', 'category', 'facility_id']))
                <div class="col-12">
                    <a href="{{ route('officer.reports.index') }}"
                       class="btn btn-sm btn-outline-secondary">
                        Reset Filter
                    </a>
                </div>
            @endif

        </form>

    </div>
</div>


<div class="card shadow-sm">
    <div class="card-body">

        @if ($reports->isEmpty())

            <div class="text-center py-5">
                <h5>Tidak ada laporan</h5>

                <p class="text-muted mb-0">
                    Tidak ada laporan yang sesuai dengan filter.
                </p>
            </div>

        @else

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Fasilitas</th>
                        <th>Pelapor</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                    </thead>

                    <tbody>

                    @foreach ($reports as $report)

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

                        <tr>

                            <td>
                                {{ $report->created_at->format('d/m/Y H:i') }}
                            </td>

                            <td>
                                <strong>
                                    {{ $report->facility->name }}
                                </strong>

                                @if ($report->facility->location)
                                    <div class="small text-muted">
                                        {{ $report->facility->location }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                {{ $report->user->name }}
                            </td>

                            <td>
                                {{ $categoryLabels[$report->category] ?? $report->category }}
                            </td>

                            <td>
                                <span class="badge text-bg-{{ $statusClass }}">
                                    {{ ucfirst($report->status) }}
                                </span>
                            </td>

                            <td class="text-end">
                                <a href="{{ route('officer.reports.show', $report) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    Detail
                                </a>
                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                </table>

            </div>

            <div class="mt-3">
                {{ $reports->links() }}
            </div>

        @endif

    </div>
</div>

@endsection
