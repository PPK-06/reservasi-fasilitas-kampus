@extends('layouts.app')

@section('title', 'Registrasi Akun Civitas | Sistem Fasilitas Kampus Undip')

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-md-8 col-lg-6">
        <div class="card card-undip border shadow-sm">
            <div class="card-body p-4 p-sm-5">
                <div class="text-center mb-4">
                    <img src="{{ asset('images/landing/logo-undip.webp') }}"
                         alt="Logo Undip"
                         class="mb-3"
                         style="width: 52px; height: 52px; object-fit: contain;"
                         onerror="this.style.display='none'">
                    <h4 class="fw-bold mb-1 font-heading" style="color: var(--undip-navy);">Registrasi Civitas</h4>
                    <p class="text-muted small mb-0">Daftarkan akun resmi untuk peminjaman sarana dan pelaporan kampus</p>
                </div>

                <form method="POST" action="{{ route('register.store') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="name" class="form-label fw-semibold small text-dark">Nama Lengkap</label>
                            <input type="text" name="name" id="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}"
                                   placeholder="Nama sesuai KTM / SK pengangkatan"
                                   required maxlength="100" autofocus>
                        </div>

                        <div class="col-12">
                            <label for="email" class="form-label fw-semibold small text-dark">Alamat Email Kampus</label>
                            <input type="email" name="email" id="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}"
                                   placeholder="nama@students.undip.ac.id"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label for="identity_number" class="form-label fw-semibold small text-dark">Nomor Induk (NIM / NIP)</label>
                            <input type="text" name="identity_number" id="identity_number"
                                   class="form-control @error('identity_number') is-invalid @enderror"
                                   value="{{ old('identity_number') }}"
                                   placeholder="Contoh: 240601211..."
                                   required maxlength="30">
                        </div>

                        <div class="col-md-6">
                            <label for="user_type" class="form-label fw-semibold small text-dark">Status Civitas Akademika</label>
                            <select name="user_type" id="user_type"
                                    class="form-select @error('user_type') is-invalid @enderror" required>
                                <option value="" disabled @selected(old('user_type') === null)>Pilih status civitas</option>
                                <option value="mahasiswa" @selected(old('user_type') === 'mahasiswa')>Mahasiswa</option>
                                <option value="dosen" @selected(old('user_type') === 'dosen')>Dosen</option>
                                <option value="staf" @selected(old('user_type') === 'staf')>Staf</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label fw-semibold small text-dark">Password</label>
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Minimal 8 karakter"
                                   required minlength="8">
                            <div class="form-text small">Minimal 8 karakter.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label fw-semibold small text-dark">Ulangi Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Ulangi password"
                                   required minlength="8">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-undip-primary w-100 py-2 fw-semibold shadow-sm">
                            <i class="bi bi-person-plus me-1"></i>Daftar
                        </button>
                    </div>
                </form>

                <div class="text-center mt-4 pt-3 border-top">
                    <p class="text-muted small mb-0">
                        Sudah punya akun?
                        <a href="{{ route('login') }}" class="fw-semibold text-decoration-none" style="color: var(--undip-navy);">
                            Masuk di sini
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
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
