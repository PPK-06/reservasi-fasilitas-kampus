{{--
================================================================================
  KOMPONEN UI — COPY-PASTE SNIPPETS
  Tiga blok siap pakai berbasis Bootstrap murni.
  Tidak ada class custom yang dibutuhkan.
================================================================================

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
1. BADGE STATUS
   Pakai inline di tabel, card, atau deskripsi item.
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

{{-- Contoh: status reservasi --}}
@php $status = $reservation->status; @endphp

@switch($status)
    @case('menunggu')
        <span class="badge text-bg-warning">Menunggu</span>
        @break
    @case('disetujui')
        <span class="badge text-bg-success">Disetujui</span>
        @break
    @case('ditolak')
        <span class="badge text-bg-danger">Ditolak</span>
        @break
    @case('selesai')
        <span class="badge text-bg-secondary">Selesai</span>
        @break
    @default
        <span class="badge text-bg-light text-dark border">{{ $status }}</span>
@endswitch

{{-- Contoh: status laporan --}}
@switch($report->status)
    @case('menunggu')
        <span class="badge text-bg-warning"><i class="bi bi-hourglass-split me-1"></i>Menunggu</span>
        @break
    @case('diproses')
        <span class="badge text-bg-primary"><i class="bi bi-gear-wide-connected me-1"></i>Diproses</span>
        @break
    @case('selesai')
        <span class="badge text-bg-success"><i class="bi bi-check-lg me-1"></i>Selesai</span>
        @break
    @case('ditolak')
        <span class="badge text-bg-danger"><i class="bi bi-x-lg me-1"></i>Ditolak</span>
        @break
@endswitch


━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
2. TABEL DAFTAR
   table-responsive + table-hover + thead berwarna biru primer.
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-bold text-dark">Daftar Reservasi</h6>
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
                        <th>Mulai</th>
                        <th>Selesai</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reservations as $reservation)
                    <tr>
                        <td class="ps-3 text-muted">{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $reservation->facility->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($reservation->start_time)->format('d M Y, H:i') }}</td>
                        <td>{{ \Carbon\Carbon::parse($reservation->end_time)->format('d M Y, H:i') }}</td>
                        <td>
                            {{-- tempel Badge Status snippet di sini --}}
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
                            Belum ada data reservasi.
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


━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
3. MODAL KONFIRMASI
   Gunakan untuk aksi destruktif: tolak, hapus, batalkan, dll.
   Cara pakai:
     a. Taruh blok #confirmModal di bawah tabel (sebelum @endsection).
     b. Pada setiap tombol "Tolak"/"Hapus" di tabel, tambahkan:
           data-bs-toggle="modal"
           data-bs-target="#confirmModal"
           data-id="{{ $item->id }}"
           data-name="{{ $item->name }}"
     c. Script di @push('scripts') menangkap data-* dan mengisi modal.
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

{{-- Tombol pemicu (di dalam baris tabel) --}}
<button type="button"
        class="btn btn-outline-danger btn-sm"
        data-bs-toggle="modal"
        data-bs-target="#confirmModal"
        data-id="{{ $reservation->id }}"
        data-name="{{ $reservation->facility->name ?? '-' }}">
    <i class="bi bi-x-circle"></i> Tolak
</button>

{{-- Modal (taruh sekali di luar loop, sebelum @endsection) --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="confirmModalLabel">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Konfirmasi Tindakan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-0 text-muted">
                    Kamu yakin ingin menolak reservasi untuk
                    <strong id="confirmItemName"></strong>?
                    Tindakan ini tidak dapat diurungkan.
                </p>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-arrow-left me-1"></i>Batal
                </button>
                <form id="confirmForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i>Ya, Tolak
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Script — masuk ke @push('scripts') di view bersangkutan --}}
@push('scripts')
<script>
document.getElementById('confirmModal').addEventListener('show.bs.modal', function (event) {
    const trigger  = event.relatedTarget;
    const id       = trigger.dataset.id;
    const name     = trigger.dataset.name;

    document.getElementById('confirmItemName').textContent = name;
    // Ganti URL sesuai route yang dipakai, mis. route('officer.reservations.reject', id)
    document.getElementById('confirmForm').action = `/officer/reservations/${id}/reject`;
});
</script>
@endpush

================================================================================
--}}