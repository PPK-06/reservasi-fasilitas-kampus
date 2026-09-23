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

<!-- Contoh: status reservasi -->
@php $status = $reservation->status; @endphp

@switch($status)
    @case('pending')
        <span class="badge text-bg-warning">Menunggu</span>
        @break
    @case('approved')
        <span class="badge text-bg-success">Disetujui</span>
        @break
    @case('rejected')
        <span class="badge text-bg-danger">Ditolak</span>
        @break
    @case('cancelled_by_user')
        <span class="badge text-bg-secondary">Dibatalkan pengguna</span>
        @break
    @case('cancelled_by_officer')
        <span class="badge text-bg-dark">Dibatalkan petugas</span>
        @break
    @default
        <span class="badge text-bg-light text-dark border">{{ $reservation->status }}</span>
@endswitch

<!-- Contoh: status laporan -->
@switch($report->status)
    @case('baru')
        <span class="badge text-bg-warning"><i class="bi bi-hourglass-split me-1"></i>Baru</span>
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
    @default
        <span class="badge text-bg-light text-dark border">{{ $report->status }}</span>
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
                            <!-- tempel Badge Status snippet di sini -->
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
3. FORM INPUT
   Contoh input teks dengan old() dan validasi @error per-field.
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

<div class="mb-3">
    <label for="name" class="form-label fw-semibold">Nama</label>
    <input type="text" name="name" id="name"
           class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name') }}" required maxlength="100">
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>


━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
4. MODAL KONFIRMASI
   Gunakan untuk aksi destruktif: tolak, hapus, batalkan, dll.
   Cara pakai:
     a. Taruh blok #confirmModal di bawah tabel (sebelum @endsection).
     b. Pada setiap tombol "Tolak"/"Hapus" di tabel, tambahkan:
           data-bs-toggle="modal"
           data-bs-target="#confirmModal"
           data-action="{{ route('officer.reservations.reject', $reservation) }}"
           data-name="{{ $item->name }}"
     c. Script di @push('scripts') menangkap data-* dan mengisi modal.
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

<!-- Tombol pemicu (di dalam baris tabel) -->
<button type="button"
        class="btn btn-outline-danger btn-sm"
        data-bs-toggle="modal"
        data-bs-target="#confirmModal"
        data-action="{{ route('officer.reservations.reject', $reservation) }}"
        data-name="{{ $reservation->facility->name ?? '-' }}">
    <i class="bi bi-x-circle"></i> Tolak
</button>

<!-- Modal (taruh sekali di luar loop, sebelum @endsection) -->
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

<!-- Script — masuk ke @push('scripts') di view bersangkutan -->
@push('scripts')
<script>
document.getElementById('confirmModal').addEventListener('show.bs.modal', function (event) {
    const trigger  = event.relatedTarget;
    const name     = trigger.dataset.name;

    document.getElementById('confirmItemName').textContent = name;
    document.getElementById('confirmForm').action = trigger.dataset.action;
});
</script>
@endpush


━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
5. MODAL KONFIRMASI PENOLAKAN (DENGAN ALASAN)
   Varian modal #4 khusus untuk aksi penolakan yang mewajibkan alasan.
   Cara pakai sama dengan modal #4, cukup ganti target ke #rejectModal.
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

<!-- Tombol pemicu (di dalam baris tabel) -->
<button type="button"
        class="btn btn-outline-danger btn-sm"
        data-bs-toggle="modal"
        data-bs-target="#rejectModal"
        data-action="{{ route('officer.reservations.reject', $reservation) }}"
        data-name="{{ $reservation->facility->name ?? '-' }}">
    <i class="bi bi-x-circle"></i> Tolak
</button>

<!-- Modal Penolakan dengan textarea alasan (taruh sekali di luar loop) -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="rejectModalLabel">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Konfirmasi Penolakan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body pt-2">
                    <p class="text-muted">
                        Kamu yakin ingin menolak reservasi untuk
                        <strong id="rejectItemName"></strong>?
                        Tindakan ini tidak dapat diurungkan.
                    </p>
                    <div class="mb-0">
                        <label for="statusReason" class="form-label fw-semibold">Alasan</label>
                        <textarea name="status_reason" id="statusReason"
                                  class="form-control @error('status_reason') is-invalid @enderror"
                                  rows="3" minlength="10" maxlength="500" required></textarea>
                        <div class="form-text">Minimal 10 karakter.</div>
                        @error('status_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-arrow-left me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i>Ya, Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script — masuk ke @push('scripts') di view bersangkutan -->
@push('scripts')
<script>
document.getElementById('rejectModal').addEventListener('show.bs.modal', function (event) {
    const trigger  = event.relatedTarget;
    const name     = trigger.dataset.name;

    document.getElementById('rejectItemName').textContent = name;
    document.getElementById('rejectForm').action = trigger.dataset.action;
});
</script>
@endpush

================================================================================
--}}