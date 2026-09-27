<?php

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/**
 * A5 Detail Akun — show, verify, reject, activate, dan resetPassword.
 *
 * Inti berkas ini adalah matriks transisi C2. Tombol yang tidak dirender bukan
 * penjaga, jadi setiap transisi yang tidak sah diuji dengan PATCH yang dikirim
 * langsung, bukan lewat tombol.
 *
 * Belum mencakup suspend: masih abort(501), menunggu komponen peringatan D4.
 */
uses(DatabaseTransactions::class);

beforeEach(function (): void {
    if (! Schema::hasTable('users') || User::where('role', 'admin')->doesntExist()) {
        $this->markTestSkipped('Butuh database yang sudah diisi DatabaseSeeder.');
    }
});

/**
 * Navbar `layouts/app.blade.php` memanggil `admin.facilities.index` (M2) dan
 * `admin.recap.index` (M5) yang belum didaftarkan pemiliknya. Tanpa keduanya
 * layout melempar RouteNotFoundException. `routes/web.php` tidak disentuh.
 */
function daftarkanRouteNavbarAdmin(): void
{
    Route::get('/__test/admin/facilities', fn () => '')->name('admin.facilities.index');
    Route::get('/__test/admin/recap', fn () => '')->name('admin.recap.index');
    Route::getRoutes()->refreshNameLookups();
}

function adminPenguji(): User
{
    return User::where('role', 'admin')->firstOrFail();
}

function akunBerstatus(string $status): User
{
    return User::factory()->create(['status' => $status, 'role' => 'pengguna']);
}

it('menampilkan tombol Verifikasi dan Tolak untuk akun pending', function (): void {
    daftarkanRouteNavbarAdmin();
    $user = akunBerstatus('pending');

    $response = $this->actingAs(adminPenguji())->get(route('admin.users.show', $user));

    $response->assertOk()
        ->assertSee(route('admin.users.verify', $user), false)
        ->assertSee(route('admin.users.reject', $user), false)
        ->assertSee('Verifikasi')
        ->assertSee('Tolak');
});

it('tidak menampilkan tombol transisi status apa pun untuk akun verified', function (): void {
    daftarkanRouteNavbarAdmin();
    $user = akunBerstatus('verified');

    $response = $this->actingAs(adminPenguji())->get(route('admin.users.show', $user));

    $response->assertOk()
        ->assertDontSee(route('admin.users.verify', $user), false)
        ->assertDontSee(route('admin.users.reject', $user), false)
        ->assertDontSee(route('admin.users.activate', $user), false)
        // Nonaktifkan menunggu komponen peringatan D4; tombolnya tidak boleh
        // dirender lebih dulu sementara route-nya masih abort(501).
        ->assertDontSee(route('admin.users.suspend', $user), false)
        ->assertDontSee('Nonaktifkan');
});

it('menampilkan tombol Aktifkan hanya untuk akun rejected dan suspended', function (): void {
    daftarkanRouteNavbarAdmin();

    foreach (['rejected', 'suspended'] as $status) {
        $user = akunBerstatus($status);

        $this->actingAs(adminPenguji())->get(route('admin.users.show', $user))
            ->assertOk()
            ->assertSee(route('admin.users.activate', $user), false)
            ->assertSee('Aktifkan')
            ->assertDontSee(route('admin.users.verify', $user), false)
            ->assertDontSee(route('admin.users.reject', $user), false)
            ->assertDontSee(route('admin.users.suspend', $user), false);
    }

    $pending = akunBerstatus('pending');

    $this->actingAs(adminPenguji())->get(route('admin.users.show', $pending))
        ->assertOk()
        ->assertDontSee(route('admin.users.activate', $pending), false);
});

it('tidak membiarkan nama akun keluar dari string JS di konfirmasi Tolak', function (): void {
    /*
     * Varian kedua yang benar-benar berbahaya sebelum perbaikan: rangkaian
     * `+alert(1)+` dievaluasi sebagai argumen confirm(), jadi berjalan sebelum
     * return dan tanpa admin menekan apa pun di dialog.
     */
    foreach (["x');alert(1);('", "x'+alert(1)+'"] as $payload) {
        $user = User::factory()->create([
            'status' => 'pending',
            'role' => 'pengguna',
            'name' => $payload,
        ]);

        $html = $this->actingAs(adminPenguji())->get(route('admin.users.show', $user))
            ->assertOk()
            ->getContent();

        expect(preg_match('/onsubmit="([^"]*)"/', $html, $cocok))->toBe(1);

        // Browser mendekode entitas HTML di atribut sebelum JavaScript berjalan,
        // jadi yang diperiksa adalah hasil dekodenya, bukan HTML mentahnya.
        $js = html_entity_decode($cocok[1], ENT_QUOTES | ENT_HTML5);

        expect($js)->toStartWith("return confirm('")
            ->and($js)->toEndWith("');");

        $literal = substr($js, strlen('return confirm('), -strlen(');'));

        // Satu literal string utuh: tanda kutip hanya di kedua ujungnya.
        expect(substr_count($literal, "'"))->toBe(2)
            ->and($js)->not->toContain($payload);
    }
});

it('menampilkan modal Reset Password hanya untuk akun verified dan suspended', function (): void {
    daftarkanRouteNavbarAdmin();

    foreach (['verified', 'suspended'] as $status) {
        $user = akunBerstatus($status);

        $this->actingAs(adminPenguji())->get(route('admin.users.show', $user))
            ->assertOk()
            ->assertSee('Reset Password')
            ->assertSee(route('admin.users.reset-password', $user), false)
            ->assertSee('name="password_confirmation"', false);
    }

    foreach (['pending', 'rejected'] as $status) {
        $user = akunBerstatus($status);

        $this->actingAs(adminPenguji())->get(route('admin.users.show', $user))
            ->assertOk()
            ->assertDontSee('Reset Password')
            ->assertDontSee(route('admin.users.reset-password', $user), false)
            ->assertDontSee('resetPasswordModal', false);
    }
});

it('menampilkan tanda hubung untuk NIM/NIP dan tipe pengguna milik petugas (C4)', function (): void {
    daftarkanRouteNavbarAdmin();
    $petugas = User::where('role', 'petugas')->firstOrFail();

    expect($petugas->identity_number)->toBeNull()
        ->and($petugas->user_type)->toBeNull();

    $this->actingAs(adminPenguji())->get(route('admin.users.show', $petugas))
        ->assertOk()
        ->assertSee('NIM/NIP')
        ->assertSee('Tipe Pengguna')
        ->assertSee('—', false);
});

it('memverifikasi akun pending lalu kembali ke A3 dengan pesan sukses', function (): void {
    $user = akunBerstatus('pending');

    $this->actingAs(adminPenguji())
        ->patch(route('admin.users.verify', $user))
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('success');

    expect($user->fresh()->status)->toBe('verified');
});

it('menolak akun pending lalu kembali ke A3 dengan pesan sukses', function (): void {
    $user = akunBerstatus('pending');

    $this->actingAs(adminPenguji())
        ->patch(route('admin.users.reject', $user))
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('success');

    expect($user->fresh()->status)->toBe('rejected');
});

it('tidak membawa query string filter saat kembali ke A3 (D15)', function (): void {
    $user = akunBerstatus('pending');

    $response = $this->actingAs(adminPenguji())->patch(route('admin.users.verify', $user));

    expect($response->headers->get('Location'))->toBe(route('admin.users.index'))
        ->and($response->headers->get('Location'))->not->toContain('?');
});

it('menolak PATCH reject untuk akun verified tanpa mengubah apa pun (C2)', function (): void {
    $user = akunBerstatus('verified');

    $response = $this->actingAs(adminPenguji())->patch(route('admin.users.reject', $user));

    $response->assertRedirect(route('admin.users.show', $user))
        ->assertSessionHas('error');

    expect($response->getStatusCode())->toBe(302)
        ->and($user->fresh()->status)->toBe('verified');
});

it('menolak PATCH reject untuk akun rejected dan suspended (C2)', function (): void {
    foreach (['rejected', 'suspended'] as $status) {
        $user = akunBerstatus($status);

        $this->actingAs(adminPenguji())
            ->patch(route('admin.users.reject', $user))
            ->assertRedirect(route('admin.users.show', $user))
            ->assertSessionHas('error');

        expect($user->fresh()->status)->toBe($status);
    }
});

it('menolak PATCH verify untuk akun yang sudah verified (C2)', function (): void {
    $user = akunBerstatus('verified');

    $response = $this->actingAs(adminPenguji())->patch(route('admin.users.verify', $user));

    $response->assertRedirect(route('admin.users.show', $user))
        ->assertSessionHas('error');

    expect($response->getStatusCode())->toBe(302)
        ->and($user->fresh()->status)->toBe('verified');
});

it('menolak PATCH verify dari rejected dan dari suspended (bagian 11)', function (): void {
    foreach (['rejected', 'suspended'] as $status) {
        $user = akunBerstatus($status);

        $response = $this->actingAs(adminPenguji())->patch(route('admin.users.verify', $user));

        $response->assertRedirect(route('admin.users.show', $user))
            ->assertSessionHas('error');

        // Pesannya mengarahkan ke aksi yang benar, supaya admin tidak
        // menyangka sistemnya rusak.
        expect(session('error'))->toContain('Aktifkan')
            ->and($response->getStatusCode())->toBe(302)
            ->and($user->fresh()->status)->toBe($status);
    }
});

it('tidak pernah mengembalikan akun ke pending', function (): void {
    foreach (['pending', 'verified', 'rejected', 'suspended'] as $status) {
        $user = akunBerstatus($status);

        $this->actingAs(adminPenguji())->patch(route('admin.users.verify', $user));
        $this->actingAs(adminPenguji())->patch(route('admin.users.reject', $user));

        expect($user->fresh()->status)->not->toBe('pending');
    }
});

it('tidak mengubah kolom lain dan tidak menghapus baris (C2, C6)', function (): void {
    $sebelumnya = User::count();
    $user = akunBerstatus('pending');
    $asli = $user->only(['role', 'email', 'identity_number', 'user_type', 'name', 'password']);

    $this->actingAs(adminPenguji())->patch(route('admin.users.verify', $user));

    $segar = $user->fresh();

    expect($segar->only(['role', 'email', 'identity_number', 'user_type', 'name', 'password']))->toBe($asli)
        ->and(User::count())->toBe($sebelumnya + 1);
});

it('mengurangi penanda pending di A3 setelah verifikasi', function (): void {
    daftarkanRouteNavbarAdmin();
    $user = akunBerstatus('pending');
    $admin = adminPenguji();

    $sebelum = $this->actingAs($admin)->get(route('admin.users.index'))->viewData('pendingCount');

    $this->actingAs($admin)->patch(route('admin.users.verify', $user));

    $sesudah = $this->actingAs($admin)->get(route('admin.users.index'))->viewData('pendingCount');

    expect($sesudah)->toBe($sebelum - 1);
});

it('menolak akses petugas ke seluruh aksi A5 dengan 403', function (): void {
    $petugas = User::where('role', 'petugas')->firstOrFail();
    $user = akunBerstatus('pending');

    $this->actingAs($petugas)->get(route('admin.users.show', $user))->assertForbidden();
    $this->actingAs($petugas)->patch(route('admin.users.verify', $user))->assertForbidden();
    $this->actingAs($petugas)->patch(route('admin.users.reject', $user))->assertForbidden();

    expect($user->fresh()->status)->toBe('pending');
});

it('membiarkan suspend tetap belum dikerjakan', function (): void {
    $user = akunBerstatus('verified');

    $this->actingAs(adminPenguji())->patch(route('admin.users.suspend', $user))->assertStatus(501);

    expect($user->fresh()->status)->toBe('verified');
});

it('mengaktifkan akun rejected dan suspended lalu kembali ke A5 (bagian 11)', function (): void {
    foreach (['rejected', 'suspended'] as $status) {
        $user = akunBerstatus($status);

        $this->actingAs(adminPenguji())
            ->patch(route('admin.users.activate', $user))
            ->assertRedirect(route('admin.users.show', $user))
            ->assertSessionHas('success');

        expect($user->fresh()->status)->toBe('verified');
    }
});

it('menolak PATCH activate untuk akun pending dan verified tanpa mengubah apa pun (C2)', function (): void {
    foreach (['pending', 'verified'] as $status) {
        $user = akunBerstatus($status);
        $asli = $user->fresh()->getAttributes();

        $this->actingAs(adminPenguji())
            ->patch(route('admin.users.activate', $user))
            ->assertRedirect(route('admin.users.show', $user))
            ->assertSessionHas('error');

        expect($user->fresh()->getAttributes())->toBe($asli);
    }
});

it('mengarahkan akun pending ke aksi Verifikasi saat activate ditolak', function (): void {
    $user = akunBerstatus('pending');

    $this->actingAs(adminPenguji())->patch(route('admin.users.activate', $user));

    expect(session('error'))->toContain('Verifikasi');
});

it('hanya mengubah status saat activate (C2)', function (): void {
    $user = akunBerstatus('suspended');
    $asli = $user->only(['role', 'email', 'identity_number', 'user_type', 'name', 'password']);

    $this->actingAs(adminPenguji())->patch(route('admin.users.activate', $user));

    expect($user->fresh()->only(array_keys($asli)))->toBe($asli);
});

it('mereset password sebagai hash, bukan teks polos, tanpa mengubah status (C5)', function (): void {
    foreach (['verified', 'suspended'] as $status) {
        $user = akunBerstatus($status);

        $this->actingAs(adminPenguji())
            ->patch(route('admin.users.reset-password', $user), [
                'password' => 'passwordbaru123',
                'password_confirmation' => 'passwordbaru123',
            ])
            ->assertRedirect(route('admin.users.show', $user))
            ->assertSessionHas('success');

        $segar = $user->fresh();

        // Membuktikan password tidak tersimpan sebagai teks polos, dan hash-nya cocok.
        expect(Hash::check('passwordbaru123', $segar->password))->toBeTrue()
            ->and($segar->status)->toBe($status);
    }
});

it('menolak reset password untuk akun pending dan rejected tanpa mengubah password', function (): void {
    foreach (['pending', 'rejected'] as $status) {
        $user = akunBerstatus($status);
        $hashLama = $user->password;

        // Isian password sah, supaya yang menolak adalah pengecekan status,
        // bukan validasi.
        $this->actingAs(adminPenguji())
            ->patch(route('admin.users.reset-password', $user), [
                'password' => 'passwordbaru123',
                'password_confirmation' => 'passwordbaru123',
            ])
            ->assertRedirect(route('admin.users.show', $user))
            ->assertSessionHas('error')
            ->assertSessionHasNoErrors();

        $segar = $user->fresh();

        expect($segar->password)->toBe($hashLama)
            ->and(Hash::check('passwordbaru123', $segar->password))->toBeFalse()
            ->and($segar->status)->toBe($status);
    }
});

it('menolak reset password kurang dari 8 karakter', function (): void {
    $user = akunBerstatus('verified');
    $hashLama = $user->password;

    $this->actingAs(adminPenguji())
        ->from(route('admin.users.show', $user))
        ->patch(route('admin.users.reset-password', $user), [
            'password' => 'pendek1',
            'password_confirmation' => 'pendek1',
        ])
        ->assertRedirect(route('admin.users.show', $user))
        ->assertSessionHasErrors('password');

    expect($user->fresh()->password)->toBe($hashLama);
});

it('menolak reset password dengan konfirmasi yang tidak cocok', function (): void {
    $user = akunBerstatus('verified');
    $hashLama = $user->password;

    $this->actingAs(adminPenguji())
        ->from(route('admin.users.show', $user))
        ->patch(route('admin.users.reset-password', $user), [
            'password' => 'passwordbaru123',
            'password_confirmation' => 'passwordlain123',
        ])
        ->assertRedirect(route('admin.users.show', $user))
        ->assertSessionHasErrors('password');

    expect($user->fresh()->password)->toBe($hashLama);
});

it('menolak akses petugas ke activate dan reset password dengan 403', function (): void {
    $petugas = User::where('role', 'petugas')->firstOrFail();
    $user = akunBerstatus('suspended');
    $hashLama = $user->password;

    $this->actingAs($petugas)->patch(route('admin.users.activate', $user))->assertForbidden();
    $this->actingAs($petugas)->patch(route('admin.users.reset-password', $user), [
        'password' => 'passwordbaru123',
        'password_confirmation' => 'passwordbaru123',
    ])->assertForbidden();

    $segar = $user->fresh();

    expect($segar->status)->toBe('suspended')
        ->and($segar->password)->toBe($hashLama);
});
