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
     *
     * Hanya menampilkan. Seluruh aksi berada di method PATCH di bawah, dan
     * tombolnya dirender view mengikuti matriks C2.
     */
    public function show(User $user): View
    {
        return view('admin.users.show', [
            'user' => $user,
        ]);
    }

    /**
     * A5 — transisi `pending` → `verified` (C2), tujuan A3 (bagian 11).
     *
     * Cakupannya hanya dari `pending`. Bagian 11 memetakan keempat aksi status
     * satu-satu ke matriks C2, dan pemulihan akses akun `rejected` maupun
     * `suspended` adalah pekerjaan `activate`, bukan aksi ini.
     *
     * Diperiksa di server, bukan hanya disembunyikan di tampilan: PATCH yang
     * dikirim langsung untuk status lain tetap harus ditolak.
     *
     * Tidak membawa query string filter A3 — itu D15, disengaja. `back()` juga
     * tidak dipakai karena ia akan mendarat di A5, bukan A3.
     */
    public function verify(User $user): RedirectResponse
    {
        if ($user->status !== 'pending') {
            $alasan = match ($user->status) {
                'verified' => 'Akun ini sudah berstatus terverifikasi, tidak ada yang perlu diubah.',
                'rejected' => 'Akun yang ditolak tidak diverifikasi ulang. Pakai aksi Aktifkan untuk memulihkan aksesnya.',
                default => 'Akun yang dinonaktifkan tidak diverifikasi ulang. Pakai aksi Aktifkan untuk memulihkan aksesnya.',
            };

            return redirect()
                ->route('admin.users.show', $user)
                ->with('error', $alasan);
        }

        $user->status = 'verified';
        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Akun '.$user->name.' berhasil diverifikasi.');
    }

    /**
     * A5 — transisi `pending` → `rejected` (C2), tujuan A3 (bagian 11).
     *
     * Cakupannya jauh lebih sempit daripada verify: matriks C2 hanya
     * menyediakan `pending` → `rejected`. Menutup akses akun yang sudah
     * `verified` memakai suspend, bukan reject.
     */
    public function reject(User $user): RedirectResponse
    {
        if ($user->status !== 'pending') {
            $alasan = match ($user->status) {
                'verified' => 'Akun yang sudah terverifikasi tidak dapat ditolak. Pakai aksi Nonaktifkan untuk menutup aksesnya.',
                'rejected' => 'Akun ini sudah berstatus ditolak.',
                default => 'Akun yang dinonaktifkan tidak dapat ditolak.',
            };

            return redirect()
                ->route('admin.users.show', $user)
                ->with('error', $alasan);
        }

        $user->status = 'rejected';
        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Registrasi akun '.$user->name.' ditolak.');
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
