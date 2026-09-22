<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * A3 — daftar akun.
     */
    public function index(): View
    {
        return view('admin.users.index');
    }

    /**
     * A4 — tampilkan form tambah akun.
     */
    public function create(): View
    {
        abort(501);
    }

    /**
     * A4 — simpan akun baru.
     */
    public function store(): RedirectResponse
    {
        abort(501);
    }

    /**
     * A5 — detail satu akun.
     */
    public function show(User $user): View
    {
        abort(501);
    }

    /**
     * A3 — transisi `pending` → `verified` (C2).
     */
    public function verify(User $user): RedirectResponse
    {
        abort(501);
    }

    /**
     * A3 — transisi `pending` → `rejected` (C2).
     */
    public function reject(User $user): RedirectResponse
    {
        abort(501);
    }

    /**
     * A5 — transisi `verified` → `suspended` (C2).
     */
    public function suspend(User $user): RedirectResponse
    {
        abort(501);
    }

    /**
     * A5 — transisi `rejected` | `suspended` → `verified` (C2).
     */
    public function activate(User $user): RedirectResponse
    {
        abort(501);
    }

    /**
     * A5 — reset password akun.
     */
    public function resetPassword(User $user): RedirectResponse
    {
        abort(501);
    }
}
