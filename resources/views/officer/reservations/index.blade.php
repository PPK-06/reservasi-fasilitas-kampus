@extends('layouts.app')

@section('title', 'Antrian Reservasi')
@section('page-title', 'Antrian Reservasi')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'menunggu' ? 'active' : '' }}"
                   href="{{ route('officer.reservations.index', ['tab' => 'menunggu']) }}">
                    Menunggu
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'terlewat' ? 'active' : '' }}"
                   href="{{ route('officer.reservations.index', ['tab' => 'terlewat']) }}">
                    Terlewat
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'semua' ? 'active' : '' }}"
                   href="{{ route('officer.reservations.index', ['tab' => 'semua']) }}">
                    Semua
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background-color: #0d3880; color: #fff;">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Fasilitas</th>
                        <th>Pemohon</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reservations as $r)
                    <tr>
                        <td class="ps-3 text-muted">{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $r->facility->name }}</td>
                        <td>{{ $r->user->name }}</td>
                        <td>{{ $r->start_time->format('d M Y') }}</td>
                        <td>{{ $r->start_time->format('H:i') }} – {{ $r->end_time->format('H:i') }}</td>
                        <td>
                            @if ($r->status === 'pending' && $r->start_time->lte(now()))
                                <span class="badge text-bg-secondary">Terlewat</span>
                            @elseif ($r->status === 'pending')
                                <span class="badge text-bg-warning">Menunggu</span>
                            @elseif ($r->status === 'approved')
                                <span class="badge text-bg-success">Disetujui</span>
                            @elseif ($r->status === 'rejected')
                                <span class="badge text-bg-danger">Ditolak</span>
                            @else
                                <span class="badge text-bg-secondary">Dibatalkan</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <a href="{{ route('officer.reservations.show', $r) }}"
                               class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                            Tidak ada data.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($reservations->hasPages())
    <div class="card-footer bg-white border-top d-flex justify-content-end py-2">
        {{ $reservations->links() }}
    </div>
    @endif
</div>
@endsection
