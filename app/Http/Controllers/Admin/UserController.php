<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
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
        return view('admin.users.create');
    }

    /**
     * A4 — simpan akun baru, langsung berstatus `verified` (C2), tujuan A3
     * (bagian 11).
     *
     * `status` tidak fillable (F10), jadi diisi sebagai properti setelah
     * konstruktor. Lewat mass assignment ia akan ditolak penjaga F11.
     *
     * NIM/NIP dan tipe pengguna untuk petugas sudah dikosongkan
     * StoreUserRequest sebelum validasi (C4), jadi tidak diurus lagi di sini.
     *
     * Password tidak di-hash di sini: cast `hashed` di model User sudah
     * menangani hashing, jadi controller tidak perlu memanggil Hash::make.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = new User($request->validated());
        $user->status = 'verified';
        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Akun '.$user->name.' berhasil dibuat.');
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
     * A5 — transisi `rejected` → `verified` dan `suspended` → `verified` (C2),
     * tujuan A5 (bagian 11).
     *
     * Pasangan verify, bukan duplikatnya. Verify adalah penilaian pertama atas
     * akun `pending`; activate adalah pemulihan akses akun yang sudah pernah
     * dinilai lalu ditolak atau dinonaktifkan (catatan A5, keputusan M1 tahap 2).
     * Karena itu `pending` ditolak di sini dan diarahkan ke Verifikasi.
     *
     * Diperiksa di server, bukan hanya disembunyikan di tampilan: PATCH yang
     * dikirim langsung untuk status lain tetap harus ditolak.
     *
     * Berbeda dengan verify dan reject, tujuannya A5, bukan A3 — admin tetap
     * di halaman akun yang baru dipulihkan.
     */
    public function activate(User $user): RedirectResponse
    {
        if (! in_array($user->status, ['rejected', 'suspended'], true)) {
            $alasan = match ($user->status) {
                'verified' => 'Akun ini sudah berstatus terverifikasi, tidak ada yang perlu diaktifkan.',
                default => 'Akun yang masih menunggu tidak diaktifkan. Pakai aksi Verifikasi untuk menilai pendaftarannya.',
            };

            return redirect()
                ->route('admin.users.show', $user)
                ->with('error', $alasan);
        }

        $user->status = 'verified';
        $user->save();

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Akun '.$user->name.' berhasil diaktifkan kembali.');
    }

    /**
     * A5 — reset password oleh admin (C5), tujuan A5 (bagian 11).
     *
     * Cakupannya hanya akun `verified` dan `suspended`. Fitur ini ada sebagai
     * konsekuensi C5 — tidak ada lupa password mandiri — jadi maknanya hanya
     * bagi akun yang punya akses atau akan dipulihkan aksesnya. Akun `pending`
     * dan `rejected` belum pernah diberi akses; kalau nanti diverifikasi atau
     * diaktifkan, pemiliknya tetap memakai password buatannya sendiri saat
     * registrasi.
     *
     * Status diperiksa di server lebih dulu, sebelum validasi, supaya PATCH
     * yang dikirim langsung untuk status lain ditolak dengan alasan yang
     * benar, bukan dengan pesan validasi password.
     *
     * Validasi ditulis di sini, bukan di Form Request: kontrak bagian 25 hanya
     * punya satu field, dan bagian 6 tidak mendaftarkan Form Request untuknya.
     * Gagal validasi kembali ke A5, tempat modalnya berada.
     *
     * Password tidak di-hash di sini: cast `hashed` di model User sudah
     * menangani hashing, jadi controller tidak perlu memanggil Hash::make. Status akun tidak
     * disentuh — reset password bukan transisi C2.
     */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        if (! in_array($user->status, ['verified', 'suspended'], true)) {
            $alasan = match ($user->status) {
                'pending' => 'Akun yang masih menunggu belum pernah diberi akses, jadi passwordnya tidak direset. Pemilik akun tetap memakai password yang dibuatnya saat registrasi.',
                default => 'Akun yang ditolak tidak punya akses, jadi passwordnya tidak direset. Pakai aksi Aktifkan lebih dulu bila aksesnya perlu dipulihkan.',
            };

            return redirect()
                ->route('admin.users.show', $user)
                ->with('error', $alasan);
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->password = $validated['password'];
        $user->save();

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Password akun '.$user->name.' berhasil direset.');
    }
}
