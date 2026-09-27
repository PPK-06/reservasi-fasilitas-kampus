<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * P4 — tampilkan form registrasi mandiri.
     */
    public function create(): View
    {
        return view('register');
    }

    /**
     * P4 — proses registrasi mandiri, akun baru berstatus `pending`.
     *
     * `status` tidak fillable (F10), jadi diisi sebagai properti setelah
     * konstruktor. Lewat mass assignment ia akan ditolak penjaga F11.
     *
     * Password tidak di-hash di sini: cast `hashed` di model User sudah
     * menangani hashing, jadi controller tidak perlu memanggil Hash::make.
     *
     * Tidak ada Auth::login setelah ini — akun `pending` belum boleh masuk (C1).
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = new User($request->validated());
        $user->role = 'pengguna';
        $user->status = 'pending';
        $user->save();

        return redirect()
            ->route('login')
            ->with('success', 'Pendaftaran berhasil. Akun Anda menunggu verifikasi admin sebelum dapat dipakai untuk masuk.');
    }
}
