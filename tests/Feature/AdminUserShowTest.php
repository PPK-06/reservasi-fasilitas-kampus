<?php

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/**
 * A5 Detail Akun — show, verify, dan reject.
 *
 * Inti berkas ini adalah matriks transisi C2. Tombol yang tidak dirender bukan
 * penjaga, jadi setiap transisi yang tidak sah diuji dengan PATCH yang dikirim
 * langsung, bukan lewat tombol.
 *
 * Belum mencakup suspend, activate, dan resetPassword: ketiganya masih
 * abort(501) di potongan ini.
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

it('tidak menampilkan tombol aksi apa pun untuk akun verified', function (): void {
    daftarkanRouteNavbarAdmin();
    $user = akunBerstatus('verified');

    $response = $this->actingAs(adminPenguji())->get(route('admin.users.show', $user));

    $response->assertOk()
        ->assertDontSee(route('admin.users.verify', $user), false)
        ->assertDontSee(route('admin.users.reject', $user), false)
        ->assertSee('Tidak ada aksi yang tersedia');
});

it('tidak menampilkan tombol aksi apa pun untuk akun rejected dan suspended', function (): void {
    daftarkanRouteNavbarAdmin();

    foreach (['rejected', 'suspended'] as $status) {
        $user = akunBerstatus($status);

        $this->actingAs(adminPenguji())->get(route('admin.users.show', $user))
            ->assertOk()
            ->assertDontSee(route('admin.users.verify', $user), false)
            ->assertDontSee(route('admin.users.reject', $user), false)
            // activate menyusul di potongan berikutnya; tombolnya tidak boleh
            // dirender lebih dulu sementara route-nya masih abort(501).
            ->assertDontSee(route('admin.users.activate', $user), false)
            ->assertSee('Tidak ada aksi yang tersedia');
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

it('membiarkan suspend, activate, dan reset password tetap belum dikerjakan', function (): void {
    $user = akunBerstatus('verified');
    $admin = adminPenguji();

    $this->actingAs($admin)->patch(route('admin.users.suspend', $user))->assertStatus(501);
    $this->actingAs($admin)->patch(route('admin.users.activate', $user))->assertStatus(501);
    $this->actingAs($admin)->patch(route('admin.users.reset-password', $user))->assertStatus(501);
});
