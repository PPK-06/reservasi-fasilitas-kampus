<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * P3 — tampilkan form login.
     */
    public function create(): View
    {
        return view('login');
    }

    /**
     * P3 — proses login.
     *
     * Tidak memakai Auth::attempt() karena status akun (C2) harus diperiksa
     * sebelum session dibuat. Semua pesan gagal memakai kunci `email` (D8).
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['email' => 'Email atau password salah.'])
                ->onlyInput('email');
        }

        $rejectionMessage = $user->statusRejectionMessage();

        if ($rejectionMessage !== null) {
            return back()
                ->withErrors(['email' => $rejectionMessage])
                ->onlyInput('email');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route($user->landingRouteName()));
    }

    /**
     * Logout — wajib POST (dokumen route bagian 8), kembali ke P1.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('facilities.index');
    }
}
