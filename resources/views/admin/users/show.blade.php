@extends('layouts.app')

@section('title', 'Detail Akun — Sistem Fasilitas Kampus')

@section('page-title', 'Detail Akun')

@section('page-actions')
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar Akun
    </a>
@endsection

@section('content')
@php
    /**
     * Label dan warna badge sama persis dengan A3
     * (resources/views/admin/users/index.blade.php), supaya satu status tidak
     * pernah terlihat berbeda di dua halaman.
     */
    $statusLabels = [
        'pending' => 'Menunggu',
        'verified' => 'Terverifikasi',
        'rejected' => 'Ditolak',
        'suspended' => 'Dinonaktifkan',
    ];

    $statusBadges = [
        'pending' => 'text-bg-warning',
        'verified' => 'text-bg-success',
        'rejected' => 'text-bg-danger',
        'suspended' => 'text-bg-secondary',
    ];

    $roleLabels = [
        'pengguna' => 'Pengguna',
        'petugas' => 'Petugas',
        'admin' => 'Admin',
    ];

    // C4 — nullable karena role; petugas dan admin tidak punya keduanya.
    $userTypeLabels = [
        'mahasiswa' => 'Mahasiswa',
        'dosen' => 'Dosen',
        'staf' => 'Staf',
    ];

    /*
     * Matriks transisi C2, dengan pembagian aksi mengikuti bagian 11 dokumen
     * route: verify dan reject sama-sama hanya melayani `pending`, sedangkan
     * pemulihan akses akun `rejected` dan `suspended` adalah pekerjaan activate.
     * Suspend hanya melayani `verified`, dan tidak dirender di A5 milik admin
     * yang sedang login: A4 tidak bisa membuat admin, jadi menonaktifkan diri
     * sendiri berisiko lockout (komentar Admin\UserController@suspend).
     *
     * Reset Password bukan transisi C2, tapi cakupannya juga dibatasi: hanya
     * akun `verified` dan `suspended`, yang punya akses atau akan dipulihkan
     * aksesnya (alasannya di komentar Admin\UserController@resetPassword).
     *
     * Penjagaan yang sesungguhnya tetap ada di controller; tombol yang tidak
     * dirender bukan penjaga.
     */
    $bolehVerifikasi = $user->status === 'pending';
    $bolehTolak = $user->status === 'pending';
    $bolehAktifkan = in_array($user->status, ['rejected', 'suspended'], true);
    $bolehNonaktifkan = $user->status === 'verified' && ! $user->is(auth()->user());
    $bolehResetPassword = in_array($user->status, ['verified', 'suspended'], true);
@endphp

<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark">{{ $user->name }}</h6>
            </div>

            <div class="card-body p-4">
                <dl class="row mb-0">
                    <dt class="col-sm-4 fw-semibold text-muted py-2">Nama</dt>
                    <dd class="col-sm-8 py-2">{{ $user->name }}</dd>

                    <dt class="col-sm-4 fw-semibold text-muted py-2">Email</dt>
                    <dd class="col-sm-8 py-2">{{ $user->email }}</dd>

                    <dt class="col-sm-4 fw-semibold text-muted py-2">Role</dt>
                    <dd class="col-sm-8 py-2">{{ $roleLabels[$user->role] ?? $user->role }}</dd>

                    <dt class="col-sm-4 fw-semibold text-muted py-2">Status</dt>
                    <dd class="col-sm-8 py-2">
                        <span class="badge {{ $statusBadges[$user->status] ?? 'text-bg-light text-dark border' }}">
                            {{ $statusLabels[$user->status] ?? $user->status }}
                        </span>
                    </dd>

                    {{-- Skema 6.1: keduanya nullable karena role, bukan karena opsional (C4) --}}
                    <dt class="col-sm-4 fw-semibold text-muted py-2">NIM/NIP</dt>
                    <dd class="col-sm-8 py-2">{{ $user->identity_number ?? '—' }}</dd>

                    <dt class="col-sm-4 fw-semibold text-muted py-2">Tipe Pengguna</dt>
                    <dd class="col-sm-8 py-2">
                        {{ $user->user_type ? ($userTypeLabels[$user->user_type] ?? $user->user_type) : '—' }}
                    </dd>

                    <dt class="col-sm-4 fw-semibold text-muted py-2">Terdaftar</dt>
                    <dd class="col-sm-8 py-2">{{ $user->created_at?->format('d M Y') ?? '—' }}</dd>
                </dl>
            </div>

            {{-- Tombol aksi mengikuti matriks transisi C2 (peta 2.4). Nonaktifkan
                 membuka modal konfirmasi berisi peringatan D4.
                 Reset Password bukan transisi status (C5), jadi dipisah ke
                 sisi kanan. --}}
            <div class="card-footer bg-white border-top py-3">
                <div class="d-flex flex-wrap gap-2">
                    @if ($bolehVerifikasi)
                        <form method="POST" action="{{ route('admin.users.verify', $user) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="bi bi-check-lg me-1"></i>Verifikasi
                            </button>
                        </form>
                    @endif

                    @if ($bolehTolak)
                        {{-- Dari `rejected` tidak ada jalan kembali ke `pending` (C2),
                             jadi penolakan dikonfirmasi lebih dulu.
                             Seluruh pesan dibentuk lewat @js, bukan {{ }} di dalam
                             string JS: browser mendekode entitas HTML di atribut
                             sebelum JavaScript berjalan, sehingga &#039; hasil {{ }}
                             kembali jadi ' dan nama akun bisa keluar dari string. --}}
                        <form method="POST" action="{{ route('admin.users.reject', $user) }}"
                              onsubmit="return confirm(@js('Tolak registrasi '.$user->name.'? Akun yang ditolak tidak dapat dikembalikan ke status menunggu.'));">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-x-lg me-1"></i>Tolak
                            </button>
                        </form>
                    @endif

                    @if ($bolehAktifkan)
                        <form method="POST" action="{{ route('admin.users.activate', $user) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Aktifkan
                            </button>
                        </form>
                    @endif

                    @if ($bolehNonaktifkan)
                        <button type="button" class="btn btn-outline-danger btn-sm"
                                data-bs-toggle="modal" data-bs-target="#suspendModal">
                            <i class="bi bi-slash-circle me-1"></i>Nonaktifkan
                        </button>
                    @endif

                    @if ($bolehResetPassword)
                        <button type="button" class="btn btn-outline-secondary btn-sm ms-auto"
                                data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                            <i class="bi bi-key me-1"></i>Reset Password
                        </button>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ════════════════════════════════════════════
     MODAL NONAKTIFKAN — transisi verified → suspended (C2)
     D4: reservasi approved mendatang milik akun ini ditampilkan sebagai
     peringatan, tidak dibatalkan (C6).
     ════════════════════════════════════════════ --}}
@if ($bolehNonaktifkan)
<div class="modal fade" id="suspendModal" tabindex="-1"
     aria-labelledby="suspendModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered {{ $upcomingReservations->isNotEmpty() ? 'modal-lg' : '' }}">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="suspendModalLabel">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Nonaktifkan Akun
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body pt-2">
                <p class="text-muted">
                    Nonaktifkan akun <strong>{{ $user->name }}</strong>? Akun ini tidak
                    dapat login sampai diaktifkan kembali oleh admin.
                </p>

                <x-d4-warning :reservations="$upcomingReservations" column="facility" />
            </div>

            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-arrow-left me-1"></i>Batal
                </button>
                <form method="POST" action="{{ route('admin.users.suspend', $user) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-slash-circle me-1"></i>Ya, Nonaktifkan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ════════════════════════════════════════════
     MODAL RESET PASSWORD — kontrak bagian 25
     Pesan error tidak dicetak ulang di sini; layout induk sudah menampilkan
     seluruhnya lewat banner $errors->any() (D8). Field password tidak diisi
     ulang dengan old() karena password tidak pernah dikembalikan ke browser.
     ════════════════════════════════════════════ --}}
@if ($bolehResetPassword)
<div class="modal fade" id="resetPasswordModal" tabindex="-1"
     aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                @csrf
                @method('PATCH')

                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="resetPasswordModalLabel">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body pt-2">
                    <p class="text-muted" style="font-size:0.9rem;">
                        Password baru untuk <strong>{{ $user->name }}</strong>. Sampaikan
                        langsung ke pemilik akun; status akun tidak berubah.
                    </p>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Password Baru</label>
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               required minlength="8" autocomplete="new-password">
                        <div class="form-text">Minimal 8 karakter.</div>
                    </div>

                    <div class="mb-0">
                        <label for="password_confirmation" class="form-label fw-semibold">Ulangi Password Baru</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="form-control @error('password') is-invalid @enderror"
                               required minlength="8" autocomplete="new-password">
                    </div>
                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-key me-1"></i>Simpan Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@if ($bolehResetPassword)
@push('scripts')
{{-- Umpan balik lebih cepat untuk kecocokan password, pola yang sama dengan P4.
     Aturan `confirmed` di server tetap sumber kebenaran. Kalau server menolak,
     modal dibuka lagi supaya admin langsung melihat field yang bermasalah. --}}
<script>
    (function () {
        const password = document.getElementById('password');
        const confirmation = document.getElementById('password_confirmation');

        const periksaKecocokan = function () {
            const tidakCocok = confirmation.value !== '' && confirmation.value !== password.value;
            confirmation.setCustomValidity(tidakCocok ? 'Konfirmasi password tidak sama.' : '');
        };

        password.addEventListener('input', periksaKecocokan);
        confirmation.addEventListener('input', periksaKecocokan);

        @if ($errors->has('password'))
            bootstrap.Modal.getOrCreateInstance(document.getElementById('resetPasswordModal')).show();
        @endif
    })();
</script>
@endpush
@endif
