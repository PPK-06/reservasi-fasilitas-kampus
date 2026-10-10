<?php

use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;

/**
 * P3 Login dan logout — `AuthController`.
 *
 * DatabaseTransactions, bukan RefreshDatabase: `Pest.php` sengaja tidak
 * memakai RefreshDatabase. Seluruh akun dibuat lewat factory dan di-rollback
 * setelah tiap test, jadi tidak ada yang bergantung pada akun demo seeder.
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
});

it('memasukkan akun verified dan mengarahkannya ke landing sesuai role (H1)', function (string $role, string $landingRoute): void {
    $user = buatAkunDenganRole($role);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => UserFactory::DEMO_PASSWORD,
    ]);

    $response->assertRedirect(route($landingRoute));
    $this->assertAuthenticatedAs($user);
})->with([
    'pengguna' => ['pengguna', 'facilities.index'],
    'petugas' => ['petugas', 'officer.dashboard'],
    'admin' => ['admin', 'admin.facilities.index'],
]);

it('menolak password yang salah dan tidak membuat session', function (): void {
    $user = User::factory()->create();

    $response = $this->from(route('login'))->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'bukan-password-yang-benar',
    ]);

    $response->assertRedirect(route('login'))
        ->assertSessionHasErrors(['email' => 'Email atau password salah.']);
    $this->assertGuest();
});

it('menolak email yang tidak terdaftar dengan pesan yang sama seperti password salah', function (): void {
    $response = $this->from(route('login'))->post(route('login.store'), [
        'email' => 'tidak.terdaftar@kampus.test',
        'password' => UserFactory::DEMO_PASSWORD,
    ]);

    $response->assertRedirect(route('login'))
        ->assertSessionHasErrors(['email' => 'Email atau password salah.']);
    $this->assertGuest();
});

it('menolak login akun yang statusnya bukan verified walau password benar (C2)', function (string $status, string $pesan): void {
    $user = buatAkunDenganStatus($status);

    $response = $this->from(route('login'))->post(route('login.store'), [
        'email' => $user->email,
        'password' => UserFactory::DEMO_PASSWORD,
    ]);

    $response->assertRedirect(route('login'))
        ->assertSessionHasErrors(['email' => $pesan]);
    $this->assertGuest();
})->with([
    'pending' => ['pending', 'Akun Anda masih menunggu verifikasi admin.'],
    'rejected' => ['rejected', 'Registrasi Anda ditolak. Hubungi admin untuk klarifikasi.'],
    'suspended' => ['suspended', 'Akun Anda dinonaktifkan. Hubungi admin.'],
]);

it('memberi pesan generik untuk password salah pada akun suspended, bukan pesan status', function (): void {
    $user = User::factory()->suspended()->create();

    $response = $this->from(route('login'))->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'bukan-password-yang-benar',
    ]);

    $response->assertRedirect(route('login'))
        ->assertSessionHasErrors(['email' => 'Email atau password salah.']);
    $this->assertGuest();
});

it('mengembalikan tamu ke URL terproteksi yang ditujunya setelah login', function (): void {
    $user = User::factory()->create();

    $this->get(route('reports.index'))->assertRedirect(route('login'));

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => UserFactory::DEMO_PASSWORD,
    ]);

    $response->assertRedirect(route('reports.index'));
    $this->assertAuthenticatedAs($user);
});

it('mengarahkan akun yang sudah login dari halaman login ke landing role-nya', function (): void {
    $petugas = User::factory()->petugas()->create();

    $this->actingAs($petugas)
        ->get(route('login'))
        ->assertRedirect(route('officer.dashboard'));
});

it('mengeluarkan akun saat logout dan mengosongkan session-nya', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['penanda_session' => 'masih-ada'])
        ->post(route('logout'));

    $response->assertRedirect(route('facilities.index'))
        ->assertSessionMissing('penanda_session');
    $this->assertGuest();

    $this->get(route('reservations.index'))->assertRedirect(route('login'));
});

it('menolak logout lewat GET dengan 405 dan akun tetap login', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('logout'))
        ->assertMethodNotAllowed();
    $this->assertAuthenticatedAs($user);
});

it('mengarahkan tamu yang mencoba logout ke halaman login', function (): void {
    $this->post(route('logout'))->assertRedirect(route('login'));
});
