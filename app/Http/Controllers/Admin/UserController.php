<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * A3 — daftar akun, dengan filter status dan role lewat query string (D9).
     *
     * Nilai filter yang tidak dikenal diabaikan dan halaman tetap dirender
     * tanpa filter itu (D14) — tidak ada Form Request, tidak ada penolakan.
     * Daftar nilai sah ditulis eksplisit di sini, bukan dibaca dari kolom
     * enum, supaya status baru di database tidak diam-diam menjadi filter
     * yang bisa dipanggil.
     */
    public function index(Request $request): View
    {
        $filterableStatuses = ['pending', 'verified', 'rejected', 'suspended'];
        $filterableRoles = ['pengguna', 'petugas', 'admin'];

        $status = in_array($request->query('status'), $filterableStatuses, true)
            ? $request->query('status')
            : null;

        $role = in_array($request->query('role'), $filterableRoles, true)
            ? $request->query('role')
            : null;

        $users = User::query()
            ->when($status, fn (Builder $query, string $status): Builder => $query->where('status', $status))
            ->when($role, fn (Builder $query, string $role): Builder => $query->where('role', $role))
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            /**
             * D13 — angka penanda diambil dari seluruh tabel, bukan dari
             * koleksi yang sedang ditampilkan. Fungsinya memberi tahu ada
             * pekerjaan menunggu terlepas dari apa yang sedang dilihat.
             */
            'pendingCount' => User::where('status', 'pending')->count(),
            'activeStatus' => $status,
            'activeRole' => $role,
        ]);
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
