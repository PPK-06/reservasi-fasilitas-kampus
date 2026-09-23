@extends('layouts.app')

@section('title', 'Login — Sistem Fasilitas Kampus')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Login</h5>

                <form method="POST" action="{{ route('login.store') }}">
                    @csrf

                    {{-- Error kredensial dan penolakan status akun sama-sama di kunci `email` (D8) --}}
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required autofocus>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Login
                    </button>
                </form>

                {{-- Flash sukses registrasi ditampilkan layout induk, tidak diulang di sini --}}
                <p class="text-center text-muted mt-4 mb-0" style="font-size:0.9rem;">
                    Belum punya akun?
                    <a href="{{ route('register') }}">Daftar di sini</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
