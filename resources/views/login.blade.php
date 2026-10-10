@extends('layouts.app')

@section('title', 'Masuk Akun · Wiyata')

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-md-7 col-lg-5 col-xl-4">
        <div class="card card-undip border shadow-sm">
            <div class="card-body p-4 p-sm-5">
                {{-- Header Identitas Form --}}
                <div class="text-center mb-4">
                    <img src="{{ asset('images/landing/logo-undip.webp') }}"
                         alt="Logo Undip"
                         class="mb-3"
                         style="width: 52px; height: 52px; object-fit: contain;"
                         onerror="this.style.display='none'">
                    <h4 class="fw-bold mb-1 font-heading" style="color: var(--undip-navy);">Masuk Akun</h4>
                    <p class="text-muted small mb-0">Wiyata · Sistem Reservasi & Pelaporan Fasilitas Kampus</p>
                </div>

                <form method="POST" action="{{ route('login.store') }}" novalidate id="loginForm">
                    @csrf

                    {{-- Error kredensial dan penolakan status akun di kunci email (D8) --}}
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold small text-dark">Alamat Email Kampus</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" name="email" id="email"
                                   class="form-control border-start-0 @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}"
                                   placeholder="nama@undip.ac.id"
                                   required autofocus>
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block small mt-1">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                        <div id="emailError" class="invalid-feedback small mt-1" style="display: none;">
                            <i class="bi bi-exclamation-circle me-1"></i><span id="emailErrorText"></span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold small text-dark">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="bi bi-key"></i>
                            </span>
                            <input type="password" name="password" id="password"
                                   class="form-control border-start-0 @error('password') is-invalid @enderror"
                                   placeholder="••••••••"
                                   required>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block small mt-1">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                        <div id="passwordError" class="invalid-feedback small mt-1" style="display: none;">
                            <i class="bi bi-exclamation-circle me-1"></i>Password wajib diisi.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-undip-primary w-100 py-2 fw-semibold shadow-sm">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Login
                    </button>
                </form>

                <div class="text-center mt-4 pt-3 border-top">
                    <p class="text-muted small mb-0">
                        Belum memiliki akun civitas?
                        <a href="{{ route('register') }}" class="fw-semibold text-decoration-none" style="color: var(--undip-navy);">
                            Daftar di sini
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <div class="text-center mt-3 text-muted small">
            <i class="bi bi-shield-check me-1"></i>Akses aman terenkripsi untuk mahasiswa, dosen, dan staf Undip.
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('loginForm')?.addEventListener('submit', function (e) {
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const emailErr = document.getElementById('emailError');
    const emailErrText = document.getElementById('emailErrorText');
    const pwdErr = document.getElementById('passwordError');

    let hasError = false;
    emailErr.style.display = 'none';
    pwdErr.style.display = 'none';

    const val = (emailInput.value || '').trim();
    if (!val) {
        emailErrText.textContent = 'Alamat email wajib diisi.';
        emailErr.style.display = 'block';
        emailInput.classList.add('is-invalid');
        hasError = true;
    } else if (!val.includes('@') || !val.includes('.')) {
        emailErrText.textContent = 'Format email tidak sah (harus menyertakan tanda @ dan domain).';
        emailErr.style.display = 'block';
        emailInput.classList.add('is-invalid');
        hasError = true;
    } else {
        emailInput.classList.remove('is-invalid');
    }

    if (!(passwordInput.value || '')) {
        pwdErr.style.display = 'block';
        passwordInput.classList.add('is-invalid');
        hasError = true;
    } else {
        passwordInput.classList.remove('is-invalid');
    }

    if (hasError) {
        e.preventDefault();
    }
});
</script>
@endpush
@endsection
