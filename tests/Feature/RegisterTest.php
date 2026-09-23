<?php

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * P4 Registrasi mandiri — kontrak form bagian 16 `route-dan-kontrak-form.md`,
 * ditambah C1, C4, C5, F10, dan F11 `dasar-proyek.md`.
 *
 * Tiga hal yang diuji di sini gagal secara diam-diam kalau salah: hash ganda
 * (akun tidak bisa login, tanpa pesan error), `status` lewat mass assignment
 * (tertahan F11 hanya di luar production), dan auto-login setelah registrasi
 * (akun `pending` masuk padahal C1 melarangnya). Ketiganya tidak terlihat dari
 * pemeriksaan manual halaman.
 */
uses(DatabaseTransactions::class);

/*
 * Sama seperti AdminUserIndexTest: suite bawaan mengarah ke SQLite in-memory
 * tanpa RefreshDatabase, jadi tanpa penjaga ini seluruh berkas gagal karena
 * tabelnya tidak ada. Jalankan dengan:
 *
 *   DB_CONNECTION=mysql DB_DATABASE=reservasi_fasilitas php artisan test
 */
beforeEach(function (): void {
    if (! Schema::hasTable('users') || User::where('role', 'admin')->doesntExist()) {
        $this->markTestSkipped('Butuh database yang sudah diisi DatabaseSeeder.');
    }
});

/**
 * @return array<string, string>
 */
function isianRegistrasiSah(array $ganti = []): array
{
    return array_merge([
        'name' => 'Uji Registrasi',
        'email' => 'uji.registrasi@kampus.test',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
        'identity_number' => '2399555001',
        'user_type' => 'mahasiswa',
    ], $ganti);
}

it('menampilkan form registrasi kepada tamu dengan keenam field', function (): void {
    $response = $this->get(route('register'));

    $response->assertOk()
        ->assertSee('name="name"', false)
        ->assertSee('name="email"', false)
        ->assertSee('name="password"', false)
        ->assertSee('name="password_confirmation"', false)
        ->assertSee('name="identity_number"', false)
        ->assertSee('name="user_type"', false);
});

it('menolak submit kosong tanpa membuat akun', function (): void {
    $sebelum = User::count();

    $this->post(route('register.store'), [])
        ->assertSessionHasErrors(['name', 'email', 'password', 'identity_number', 'user_type']);

    expect(User::count())->toBe($sebelum);
});

it('menolak email yang sudah dipakai', function (): void {
    $sebelum = User::count();
    $emailTerpakai = User::where('role', 'pengguna')->firstOrFail()->email;

    $this->post(route('register.store'), isianRegistrasiSah(['email' => $emailTerpakai]))
        ->assertSessionHasErrors('email');

    expect(User::count())->toBe($sebelum);
});

it('menolak NIM/NIP yang sudah dipakai (C4)', function (): void {
    $sebelum = User::count();
    $nimTerpakai = User::whereNotNull('identity_number')->firstOrFail()->identity_number;

    $this->post(route('register.store'), isianRegistrasiSah(['identity_number' => $nimTerpakai]))
        ->assertSessionHasErrors('identity_number');

    expect(User::count())->toBe($sebelum);
});

it('menolak konfirmasi password yang tidak sama', function (): void {
    $this->post(route('register.store'), isianRegistrasiSah(['password_confirmation' => 'berbeda123']))
        ->assertSessionHasErrors('password');

    expect(User::where('email', 'uji.registrasi@kampus.test')->exists())->toBeFalse();
});

it('menolak password kurang dari 8 karakter (C5)', function (): void {
    $this->post(route('register.store'), isianRegistrasiSah([
        'password' => 'tujuh77',
        'password_confirmation' => 'tujuh77',
    ]))->assertSessionHasErrors('password');

    expect(User::where('email', 'uji.registrasi@kampus.test')->exists())->toBeFalse();
});

it('membuat akun pengguna berstatus pending dan mengembalikan ke login dengan pesan sukses', function (): void {
    $response = $this->post(route('register.store'), isianRegistrasiSah());

    $response->assertRedirect(route('login'))
        ->assertSessionHas('success')
        ->assertSessionHasNoErrors();

    $user = User::where('email', 'uji.registrasi@kampus.test')->firstOrFail();

    expect($user->role)->toBe('pengguna')
        ->and($user->status)->toBe('pending')
        ->and($user->user_type)->toBe('mahasiswa');

    // Tidak ada auto-login: akun pending belum boleh masuk (C1).
    $this->assertGuest();
});

it('menyimpan password sebagai hash tunggal, bukan plaintext dan bukan hash ganda', function (): void {
    $this->post(route('register.store'), isianRegistrasiSah());

    $user = User::where('email', 'uji.registrasi@kampus.test')->firstOrFail();

    expect($user->password)->not->toBe('rahasia123')
        ->and(Hash::check('rahasia123', $user->password))->toBeTrue();
});

it('tidak menerima role maupun status dari form', function (): void {
    $this->post(route('register.store'), isianRegistrasiSah([
        'role' => 'admin',
        'status' => 'verified',
    ]))->assertRedirect(route('login'));

    $user = User::where('email', 'uji.registrasi@kampus.test')->firstOrFail();

    expect($user->role)->toBe('pengguna')
        ->and($user->status)->toBe('pending');
});

it('menolak login akun hasil registrasi selama masih pending (C1)', function (): void {
    $this->post(route('register.store'), isianRegistrasiSah());

    $this->post(route('login.store'), [
        'email' => 'uji.registrasi@kampus.test',
        'password' => 'rahasia123',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('meloloskan login akun yang sama setelah diverifikasi admin', function (): void {
    $this->post(route('register.store'), isianRegistrasiSah());

    $user = User::where('email', 'uji.registrasi@kampus.test')->firstOrFail();
    $user->status = 'verified';
    $user->save();

    $this->post(route('login.store'), [
        'email' => 'uji.registrasi@kampus.test',
        'password' => 'rahasia123',
    ])->assertRedirect(route('facilities.index'));

    $this->assertAuthenticatedAs($user->fresh());
});

it('menolak akses halaman registrasi bagi yang sudah login', function (): void {
    $pengguna = User::where('role', 'pengguna')->where('status', 'verified')->firstOrFail();

    $this->actingAs($pengguna)
        ->get(route('register'))
        ->assertRedirect(route('facilities.index'));
});
