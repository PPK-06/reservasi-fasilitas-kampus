@extends('layouts.app')

@section('title', 'Riwayat Reservasi')
@section('page-title', 'Riwayat Reservasi Saya')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-bold text-dark">Riwayat Reservasi</h6>
        <a href="{{ route('reservations.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Ajukan Baru
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background-color: #0d3880; color: #fff;">
                    <tr>
                        <th class="ps-3" style="width:40px">#</th>
                        <th>Fasilitas</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reservations as $reservation)
                    <tr>
                        <td class="ps-3 text-muted">{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $reservation->facility->name }}</td>
                        <td>{{ $reservation->start_time->format('d M Y') }}</td>
                        <td>{{ $reservation->start_time->format('H:i') }} – {{ $reservation->end_time->format('H:i') }}</td>
                        <td>
                            @php $s = $reservation->status; @endphp
                            @if ($s === 'pending' && $reservation->start_time->lte(now()))
                                <span class="badge text-bg-secondary">Tidak sempat diproses</span>
                            @elseif ($s === 'pending')
                                <span class="badge text-bg-warning">Menunggu</span>
                            @elseif ($s === 'approved')
                                <span class="badge text-bg-success">Disetujui</span>
                            @elseif ($s === 'rejected')
                                <span class="badge text-bg-danger">Ditolak</span>
                            @elseif ($s === 'cancelled_by_user')
                                <span class="badge text-bg-secondary">Dibatalkan</span>
                            @elseif ($s === 'cancelled_by_officer')
                                <span class="badge text-bg-dark">Dibatalkan petugas</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <a href="{{ route('reservations.show', $reservation) }}"
                               class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                            Belum ada reservasi.
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
