@props([
    'reservations' => collect(),
    'column' => 'user',
])

@if ($reservations && $reservations->isNotEmpty())
    <div class="alert alert-warning mb-0">
        <div class="fw-semibold mb-2">
            <i class="bi bi-exclamation-triangle me-1"></i>
            Terdapat {{ $reservations->count() }} reservasi yang sudah disetujui
            dan belum dilaksanakan:
        </div>
        <div class="table-responsive" style="max-height: 220px; overflow-y: auto;">
            <table class="table table-sm table-borderless mb-0" style="font-size: 0.82rem;">
                <thead>
                    <tr class="text-muted">
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        @if ($column === 'facility')
                            <th>Fasilitas</th>
                        @else
                            <th>Pemohon</th>
                        @endif
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reservations as $res)
                        <tr>
                            <td>{{ $res->start_time->format('d M Y') }}</td>
                            <td>{{ $res->start_time->format('H:i') }} – {{ $res->end_time->format('H:i') }}</td>
                            @if ($column === 'facility')
                                <td>{{ $res->facility->name ?? '-' }}</td>
                            @else
                                <td>{{ $res->user->name ?? '-' }}</td>
                            @endif
                            <td>
                                {{-- Link ke antrian reservasi petugas (O3) hanya untuk role petugas --}}
                                @if ($res->id && auth()->user()?->role === 'petugas')
                                    <a href="{{ route('officer.reservations.show', $res) }}"
                                       class="text-primary text-decoration-none"
                                       title="Lihat di antrian petugas">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <small class="text-muted d-block mt-2">
            <i class="bi bi-info-circle me-1"></i>
            Reservasi ini <strong>tidak dibatalkan otomatis</strong>. Pembatalan
            dilakukan petugas lewat halaman antrian (US 10).
        </small>
    </div>
@endif
