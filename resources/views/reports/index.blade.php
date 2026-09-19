@extends('layouts.app')

@section('title', 'Laporan Saya')
@section('page-title', 'Laporan Saya')

@section('page-actions')
    <a href="{{ route('reports.create') }}" class="btn btn-primary">
        Buat Laporan
    </a>
@endsection

@section('content')

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-body">

        @if ($reports->isEmpty())
            <div class="text-center py-5">
                <h5>Belum ada laporan</h5>
                <p class="text-muted mb-3">
                    Anda belum pernah mengirim laporan kerusakan fasilitas.
                </p>

                <a href="{{ route('reports.create') }}" class="btn btn-primary">
                    Buat Laporan
                </a>
            </div>
        @else

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Fasilitas</th>
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
                                <strong>{{ $report->facility->name }}</strong>

                                @if ($report->facility->location)
                                    <div class="small text-muted">
                                        {{ $report->facility->location }}
                                    </div>
                                @endif
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
                                <a href="{{ route('reports.show', $report) }}"
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
