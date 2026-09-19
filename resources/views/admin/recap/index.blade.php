@extends('layouts.app')

@section('title', 'Rekap & Export')
@section('page-title', 'Rekap & Export')

@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Filter belum valid.</strong>

        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


<div class="card shadow-sm mb-4">
    <div class="card-body">

        <form
            method="GET"
            action="{{ route('admin.recap.index') }}"
            class="row g-3 align-items-end">

            <div class="col-md-5">
                <label
                    for="start_date"
                    class="form-label">
                    Tanggal Mulai
                </label>

                <input
                    type="date"
                    name="start_date"
                    id="start_date"
                    value="{{ request('start_date') }}"
                    class="form-control @error('start_date') is-invalid @enderror"
                    required>

                @error('start_date')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>


            <div class="col-md-5">
                <label
                    for="end_date"
                    class="form-label">
                    Tanggal Selesai
                </label>

                <input
                    type="date"
                    name="end_date"
                    id="end_date"
                    value="{{ request('end_date') }}"
                    class="form-control @error('end_date') is-invalid @enderror"
                    required>

                @error('end_date')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>


            <div class="col-md-2 d-grid">
                <button
                    type="submit"
                    class="btn btn-primary">
                    Tampilkan
                </button>
            </div>

        </form>

    </div>
</div>


@if (! $hasFilter)

    <div class="alert alert-info">
        Pilih rentang tanggal untuk menampilkan rekap okupansi
        dan frekuensi kerusakan.
    </div>

@else

    <div class="d-flex flex-wrap
                justify-content-between
                align-items-center
                gap-3 mb-4">

        <div>
            <div class="text-muted small">
                Periode rekap
            </div>

            <strong>
                {{ $startDate }}
                s.d.
                {{ $endDate }}
            </strong>

            <span class="text-muted">
                ({{ $dayCount }} hari)
            </span>
        </div>


        <a
            href="{{ route('admin.recap.export', [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]) }}"
            class="btn btn-success">
            Export CSV
        </a>

    </div>


    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <div class="text-muted small mb-1">
                Reservasi tidak sempat diproses pada periode ini
            </div>

            <div class="display-6 fw-semibold">
                {{ $missedReservations }}
            </div>

            <div class="small text-muted mt-2">
                Reservasi berstatus pending yang waktu mulainya
                sudah lewat.
            </div>

        </div>
    </div>


    <ul
        class="nav nav-tabs"
        id="recapTabs"
        role="tablist">

        <li
            class="nav-item"
            role="presentation">

            <button
                class="nav-link active"
                id="occupancy-tab"
                data-bs-toggle="tab"
                data-bs-target="#occupancy-pane"
                type="button"
                role="tab">

                Okupansi

            </button>

        </li>


        <li
            class="nav-item"
            role="presentation">

            <button
                class="nav-link"
                id="damage-tab"
                data-bs-toggle="tab"
                data-bs-target="#damage-pane"
                type="button"
                role="tab">

                Frekuensi Kerusakan

            </button>

        </li>

    </ul>


    <div
        class="tab-content border
               border-top-0 rounded-bottom
               bg-white p-3"
        id="recapTabsContent">

        {{-- =========================================
             TAB OKUPANSI
        ========================================== --}}

        <div
            class="tab-pane fade show active"
            id="occupancy-pane"
            role="tabpanel"
            aria-labelledby="occupancy-tab">

            <div class="mb-3">
                <div class="small text-muted">

                    Okupansi =
                    slot terpakai ÷
                    (26 × jumlah hari)
                    × 100%.

                    Hanya reservasi berstatus
                    <code>approved</code>
                    yang dihitung.

                </div>
            </div>


            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                    <tr>
                        <th>Fasilitas</th>
                        <th>Lokasi</th>
                        <th class="text-end">
                            Reservasi Approved
                        </th>
                        <th class="text-end">
                            Slot Terpakai
                        </th>
                        <th class="text-end">
                            Total Slot
                        </th>
                        <th class="text-end">
                            Okupansi
                        </th>
                    </tr>
                    </thead>


                    <tbody>

                    @forelse ($occupancyRows as $row)

                        <tr>

                            <td>
                                <strong>
                                    {{ $row->name }}
                                </strong>
                            </td>

                            <td>
                                {{ $row->location ?: '-' }}
                            </td>

                            <td class="text-end">
                                {{ $row->reservation_count }}
                            </td>

                            <td class="text-end">
                                {{ number_format(
                                    $row->used_slots,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </td>

                            <td class="text-end">
                                {{ $row->total_slots }}
                            </td>

                            <td class="text-end">
                                <strong>
                                    {{ number_format(
                                        $row->occupancy_percentage,
                                        2,
                                        ',',
                                        '.'
                                    ) }}%
                                </strong>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="6"
                                class="text-center text-muted py-4">

                                Belum ada data fasilitas.

                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- =========================================
             TAB FREKUENSI KERUSAKAN
        ========================================== --}}

        <div
            class="tab-pane fade"
            id="damage-pane"
            role="tabpanel"
            aria-labelledby="damage-tab">

            <div class="mb-3">
                <div class="small text-muted">

                    Menghitung seluruh laporan pada periode
                    yang dipilih kecuali laporan berstatus
                    <code>ditolak</code>.

                </div>
            </div>


            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                    <tr>
                        <th>Fasilitas</th>
                        <th>Lokasi</th>
                        <th class="text-end">
                            Jumlah Laporan
                        </th>
                    </tr>
                    </thead>


                    <tbody>

                    @forelse ($damageRows as $row)

                        <tr>

                            <td>
                                <strong>
                                    {{ $row->name }}
                                </strong>
                            </td>

                            <td>
                                {{ $row->location ?: '-' }}
                            </td>

                            <td class="text-end">
                                <span class="badge text-bg-secondary">
                                    {{ $row->report_count }}
                                </span>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="3"
                                class="text-center text-muted py-4">

                                Belum ada data fasilitas.

                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

@endif

@endsection
