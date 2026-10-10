@extends('layouts.app')

@section('title', 'Registrasi Akun Civitas · Wiyata')

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
                    <p class="text-muted small mb-0">Daftarkan akun Wiyata untuk peminjaman sarana dan pelaporan kampus</p>
                </div>

                <form method="POST" action="{{ route('register.store') }}" novalidate id="registerForm">
                    @csrf

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="name" class="form-label fw-semibold small text-dark">Nama Lengkap</label>
                            <input type="text" name="name" id="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}"
                                   placeholder="Nama sesuai KTM / SK pengangkatan"
                                   required maxlength="100" autofocus>
                            @error('name')
                                <div class="invalid-feedback d-block small mt-1">{{ $message }}</div>
                            @enderror
                            <div id="nameError" class="invalid-feedback small mt-1" style="display: none;">Nama lengkap wajib diisi.</div>
                        </div>

                        <div class="col-12">
                            <label for="email" class="form-label fw-semibold small text-dark">Alamat Email Kampus</label>
                            <input type="email" name="email" id="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}"
                                   placeholder="nama@students.undip.ac.id"
                                   required>
                            @error('email')
                                <div class="invalid-feedback d-block small mt-1">{{ $message }}</div>
                            @enderror
                            <div id="emailError" class="invalid-feedback small mt-1" style="display: none;"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="identity_number" class="form-label fw-semibold small text-dark">Nomor Induk (NIM / NIP)</label>
                            <input type="text" name="identity_number" id="identity_number"
                                   class="form-control @error('identity_number') is-invalid @enderror"
                                   value="{{ old('identity_number') }}"
                                   placeholder="Contoh: 240601211..."
                                   inputmode="numeric"
                                   pattern="[0-9]*"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                   required maxlength="30">
                            @error('identity_number')
                                <div class="invalid-feedback d-block small mt-1">{{ $message }}</div>
                            @enderror
                            <div id="identityError" class="invalid-feedback small mt-1" style="display: none;">Nomor Induk (NIM/NIP) wajib diisi angka.</div>
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
                            @error('user_type')
                                <div class="invalid-feedback d-block small mt-1">{{ $message }}</div>
                            @enderror
                            <div id="userTypeError" class="invalid-feedback small mt-1" style="display: none;">Pilih status civitas.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label fw-semibold small text-dark">Password</label>
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Minimal 8 karakter"
                                   required minlength="8">
                            <div class="form-text small">Minimal 8 karakter.</div>
                            @error('password')
                                <div class="invalid-feedback d-block small mt-1">{{ $message }}</div>
                            @enderror
                            <div id="passwordError" class="invalid-feedback small mt-1" style="display: none;">Password minimal 8 karakter.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label fw-semibold small text-dark">Ulangi Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="form-control @error('password_confirmation') is-invalid @enderror"
                                   placeholder="Ulangi password"
                                   required minlength="8">
                            @error('password_confirmation')
                                <div class="invalid-feedback d-block small mt-1">{{ $message }}</div>
                            @enderror
                            <div id="passwordConfirmError" class="invalid-feedback small mt-1" style="display: none;">Konfirmasi password tidak cocok.</div>
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
document.getElementById('registerForm')?.addEventListener('submit', function (e) {
    let hasError = false;

    // Reset error displays
    ['name', 'email', 'identity', 'userType', 'password', 'passwordConfirm'].forEach(id => {
        const el = document.getElementById(id + 'Error');
        if (el) el.style.display = 'none';
    });

    const name = document.getElementById('name');
    const email = document.getElementById('email');
    const nim = document.getElementById('identity_number');
    const userType = document.getElementById('user_type');
    const pwd = document.getElementById('password');
    const pwdConfirm = document.getElementById('password_confirmation');

    if (!name.value.trim()) {
        document.getElementById('nameError').style.display = 'block';
        name.classList.add('is-invalid');
        hasError = true;
    } else {
        name.classList.remove('is-invalid');
    }

    const emailVal = email.value.trim();
    const emailErr = document.getElementById('emailError');
    if (!emailVal) {
        emailErr.textContent = 'Alamat email wajib diisi.';
        emailErr.style.display = 'block';
        email.classList.add('is-invalid');
        hasError = true;
    } else if (!emailVal.includes('@') || !emailVal.includes('.')) {
        emailErr.textContent = 'Format email tidak sah (harus menyertakan tanda @ dan domain).';
        emailErr.style.display = 'block';
        email.classList.add('is-invalid');
        hasError = true;
    } else {
        email.classList.remove('is-invalid');
    }

    const nimVal = nim.value.trim();
    const idErr = document.getElementById('identityError');
    if (!nimVal) {
        idErr.textContent = 'Nomor Induk (NIM / NIP) wajib diisi.';
        idErr.style.display = 'block';
        nim.classList.add('is-invalid');
        hasError = true;
    } else if (!/^[0-9]+$/.test(nimVal)) {
        idErr.textContent = 'Nomor Induk (NIM / NIP) hanya boleh berupa angka.';
        idErr.style.display = 'block';
        nim.classList.add('is-invalid');
        hasError = true;
    } else {
        nim.classList.remove('is-invalid');
    }

    if (!userType.value) {
        document.getElementById('userTypeError').style.display = 'block';
        userType.classList.add('is-invalid');
        hasError = true;
    } else {
        userType.classList.remove('is-invalid');
    }

    if (!pwd.value || pwd.value.length < 8) {
        document.getElementById('passwordError').style.display = 'block';
        pwd.classList.add('is-invalid');
        hasError = true;
    } else {
        pwd.classList.remove('is-invalid');
    }

    if (pwd.value !== pwdConfirm.value) {
        document.getElementById('passwordConfirmError').style.display = 'block';
        pwdConfirm.classList.add('is-invalid');
        hasError = true;
    } else {
        pwdConfirm.classList.remove('is-invalid');
    }

    if (hasError) {
        e.preventDefault();
    }
});
</script>
@endpush
