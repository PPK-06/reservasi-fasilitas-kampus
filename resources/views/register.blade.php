@extends('layouts.app')

@section('title', 'Registrasi — Sistem Fasilitas Kampus')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Registrasi</h5>

                <form method="POST" action="{{ route('register.store') }}">
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

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               required minlength="8">
                        <div class="form-text">Minimal 8 karakter.</div>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label fw-semibold">Ulangi Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="form-control @error('password') is-invalid @enderror"
                               required minlength="8">
                    </div>

                    <div class="mb-3">
                        <label for="identity_number" class="form-label fw-semibold">NIM/NIP</label>
                        <input type="text" name="identity_number" id="identity_number"
                               class="form-control @error('identity_number') is-invalid @enderror"
                               value="{{ old('identity_number') }}" required maxlength="30">
                    </div>

                    <div class="mb-4">
                        <label for="user_type" class="form-label fw-semibold">Status Civitas</label>
                        <select name="user_type" id="user_type"
                                class="form-select @error('user_type') is-invalid @enderror" required>
                            <option value="" disabled @selected(old('user_type') === null)>Pilih salah satu</option>
                            <option value="mahasiswa" @selected(old('user_type') === 'mahasiswa')>Mahasiswa</option>
                            <option value="dosen" @selected(old('user_type') === 'dosen')>Dosen</option>
                            <option value="staf" @selected(old('user_type') === 'staf')>Staf</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-person-plus me-1"></i>Daftar
                    </button>
                </form>

                <p class="text-center text-muted mt-4 mb-0" style="font-size:0.9rem;">
                    Sudah punya akun?
                    <a href="{{ route('login') }}">Masuk di sini</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Umpan balik lebih cepat untuk kecocokan password. Aturan `confirmed` di
     server tetap sumber kebenaran: tanpa JavaScript, form ini tetap terkirim
     dan errornya muncul dari server. --}}
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
    })();
</script>
@endpush
