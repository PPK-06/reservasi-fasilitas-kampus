<?php

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/**
 * Middleware `auth` + `role` (`EnsureUserHasRole`) pada ketiga kelompok route.
 *
 * Satu route perwakilan per kelompok: `reservations.index` (pengguna),
 * `officer.dashboard` (petugas), dan `admin.users.index` (admin).
 *
 * DatabaseTransactions, bukan RefreshDatabase: `Pest.php` sengaja tidak
 * memakai RefreshDatabase. Seluruh akun dibuat lewat factory dan di-rollback
 * setelah tiap test.
 */
uses(DatabaseTransactions::class);

/*
 * `phpunit.xml` mengarahkan suite ke SQLite in-memory yang tidak punya tabel.
 * Jalankan dengan:
 *
 *   DB_CONNECTION=mysql DB_DATABASE=reservasi_fasilitas php artisan test
 */
beforeEach(function (): void {
    if (! Schema::hasTable('users')) {
        $this->markTestSkipped('Butuh database yang sudah dimigrasi.');
    }

    daftarkanRouteDummyPerRole();
});

/**
 * Route dummy untuk sisi positif, satu per role, supaya test tidak perlu
 * merender view modul lain. Susunan middleware-nya sama dengan kelompok route
 * asli di `routes/web.php`: `web` (dari berkas route web), `auth`, lalu
 * `role:{role}`.
 */
function daftarkanRouteDummyPerRole(): void
{
    foreach (['pengguna', 'petugas', 'admin'] as $role) {
        Route::middleware(['web', 'auth', 'role:'.$role])
            ->get('/__test/role/'.$role, fn (): string => 'ok-'.$role);
    }
}

it('mengarahkan tamu ke halaman login pada route terproteksi', function (string $routeName): void {
    $this->get(route($routeName))->assertRedirect(route('login'));
    $this->assertGuest();
})->with([
    'kelompok pengguna' => ['reservations.index'],
    'kelompok petugas' => ['officer.dashboard'],
    'kelompok admin' => ['admin.users.index'],
]);

it('menolak role lain dengan 403', function (string $routeName, string $roleLain): void {
    $user = match ($roleLain) {
        'admin' => User::factory()->admin()->create(),
        'petugas' => User::factory()->petugas()->create(),
        'pengguna' => User::factory()->create(),
    };

    $this->actingAs($user)
        ->get(route($routeName))
        ->assertForbidden();
})->with([
    'kelompok pengguna diakses petugas' => ['reservations.index', 'petugas'],
    'kelompok pengguna diakses admin' => ['reservations.index', 'admin'],
    'kelompok petugas diakses pengguna' => ['officer.dashboard', 'pengguna'],
    'kelompok petugas diakses admin' => ['officer.dashboard', 'admin'],
    'kelompok admin diakses pengguna' => ['admin.users.index', 'pengguna'],
    'kelompok admin diakses petugas' => ['admin.users.index', 'petugas'],
]);

it('meloloskan akun verified ke route milik role-nya sendiri', function (string $role): void {
    $user = match ($role) {
        'admin' => User::factory()->admin()->create(),
        'petugas' => User::factory()->petugas()->create(),
        'pengguna' => User::factory()->create(),
    };

    $this->actingAs($user)
        ->get('/__test/role/'.$role)
        ->assertOk()
        ->assertSeeText('ok-'.$role);
})->with(['pengguna', 'petugas', 'admin']);

it('menolak role lain dengan 403 pada route dummy, sama seperti route asli', function (): void {
    $petugas = User::factory()->petugas()->create();

    $this->actingAs($petugas)
        ->get('/__test/role/admin')
        ->assertForbidden();
});

it('mengeluarkan akun yang di-suspend saat session-nya masih hidup', function (): void {
    $user = User::factory()->suspended()->create();

    $response = $this->actingAs($user)->get(route('reservations.index'));

    $response->assertRedirect(route('login'))
        ->assertSessionHasErrors(['email' => 'Akun Anda dinonaktifkan. Hubungi admin.']);
    $this->assertGuest();
});

it('memeriksa status akun lebih dulu daripada role', function (): void {
    $petugasSuspended = User::factory()->petugas()->suspended()->create();

    $this->actingAs($petugasSuspended)
        ->get(route('admin.users.index'))
        ->assertRedirect(route('login'));
    $this->assertGuest();
});
