@extends('layouts.app')

@section('title', 'Tambah Akun · Wiyata')

@section('page-title', 'Tambah Akun')

@section('page-actions')
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar Akun
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-undip border shadow-sm">
            <div class="card-body p-4">
                <p class="text-muted" style="font-size:0.9rem;">
                    Akun yang dibuat admin langsung berstatus terverifikasi dan bisa
                    dipakai masuk. Akun admin tidak dibuat dari sini.
                </p>

                <form method="POST" action="{{ route('admin.users.store') }}" novalidate>
                    @csrf

                    {{-- Pesan error tidak dicetak ulang di sini; layout induk sudah
                         menampilkan seluruhnya lewat banner $errors->any() (D8) --}}
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Nama</label>
                        <input type="text" name="name" id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required maxlength="100" autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   required minlength="8" autocomplete="new-password">
                            <div class="form-text">Minimal 8 karakter.</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label fw-semibold">Ulangi Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="form-control @error('password') is-invalid @enderror"
                                   required minlength="8" autocomplete="new-password">
                        </div>
                    </div>

                    {{-- Bagian 24: `admin` tidak ada di daftar. Akun admin dibuat lewat seeder --}}
                    <div class="mb-3">
                        <label for="role" class="form-label fw-semibold">Role</label>
                        <select name="role" id="role"
                                class="form-select @error('role') is-invalid @enderror" required>
                            <option value="" disabled @selected(old('role') === null)>Pilih salah satu</option>
                            <option value="pengguna" @selected(old('role') === 'pengguna')>Pengguna</option>
                            <option value="petugas" @selected(old('role') === 'petugas')>Petugas</option>
                        </select>
                    </div>

                    {{-- C4 | hanya relevan untuk role pengguna. Tanpa JavaScript
                         keduanya tetap tampil dan tidak ber-atribut required;
                         `required_if:role,pengguna` di server satu-satunya penjaga. --}}
                    <div id="identityFields">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="identity_number" class="form-label fw-semibold">NIM/NIP</label>
                                <input type="text" name="identity_number" id="identity_number"
                                       class="form-control @error('identity_number') is-invalid @enderror"
                                       inputmode="numeric"
                                       pattern="[0-9]*"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                       value="{{ old('identity_number') }}" maxlength="30">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="user_type" class="form-label fw-semibold">Tipe Pengguna</label>
                                <select name="user_type" id="user_type"
                                        class="form-select @error('user_type') is-invalid @enderror">
                                    <option value="" @selected(old('user_type') === null)>Pilih salah satu</option>
                                    <option value="mahasiswa" @selected(old('user_type') === 'mahasiswa')>Mahasiswa</option>
                                    <option value="dosen" @selected(old('user_type') === 'dosen')>Dosen</option>
                                    <option value="staf" @selected(old('user_type') === 'staf')>Staf</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3 pt-3 border-top">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-undip-primary shadow-sm">
                            <i class="bi bi-person-plus me-1"></i>Simpan Akun
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Umpan balik lebih cepat, bukan penjaga. Saat role petugas, NIM/NIP dan
     tipe pengguna disembunyikan sekaligus di-disable: field yang hanya
     disembunyikan tetap ikut terkirim. StoreUserRequest juga mengosongkan
     keduanya untuk petugas sebelum validasi, jadi tanpa JavaScript hasilnya
     tetap benar. --}}
<script>
    (function () {
        const role = document.getElementById('role');
        const identityFields = document.getElementById('identityFields');
        const identityNumber = document.getElementById('identity_number');
        const userType = document.getElementById('user_type');
        const password = document.getElementById('password');
        const confirmation = document.getElementById('password_confirmation');

        const sesuaikanFieldIdentitas = function () {
            const pengguna = role.value === 'pengguna';

            identityFields.classList.toggle('d-none', role.value === 'petugas');

            [identityNumber, userType].forEach(function (field) {
                field.disabled = role.value === 'petugas';
                field.required = pengguna;
            });
        };

        const periksaKecocokan = function () {
            const tidakCocok = confirmation.value !== '' && confirmation.value !== password.value;
            confirmation.setCustomValidity(tidakCocok ? 'Konfirmasi password tidak sama.' : '');
        };

        role.addEventListener('change', sesuaikanFieldIdentitas);
        password.addEventListener('input', periksaKecocokan);
        confirmation.addEventListener('input', periksaKecocokan);

        sesuaikanFieldIdentitas();
    })();
</script>
@endpush
