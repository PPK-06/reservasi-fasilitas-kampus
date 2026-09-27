<?php

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * A4 Tambah Akun — kontrak form bagian 24 `route-dan-kontrak-form.md`,
 * ditambah C2, C4, C5, dan F10 `dasar-proyek.md`.
 *
 * Field bersyarat (`identity_number`, `user_type`) disembunyikan JavaScript
 * saat role = petugas, tapi penjaganya hanya `required_if` di server. Karena
 * itu seluruh aturan di sini diuji dengan POST langsung, bukan lewat form.
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

function adminPembuatAkun(): User
{
    return User::where('role', 'admin')->firstOrFail();
}

/**
 * @return array<string, string>
 */
function isianTambahAkunSah(array $ganti = []): array
{
    return array_merge([
        'name' => 'Uji Tambah Akun',
        'email' => 'uji.tambah.akun@kampus.test',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
        'role' => 'pengguna',
        'identity_number' => '2399666001',
        'user_type' => 'dosen',
    ], $ganti);
}

it('menampilkan tombol Tambah Akun di header A3 yang mengarah ke A4', function (): void {
    $this->actingAs(adminPembuatAkun())->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Tambah Akun')
        ->assertSee('href="'.route('admin.users.create').'"', false);
});

it('menampilkan form A4 dengan role pengguna dan petugas saja', function (): void {
    $response = $this->actingAs(adminPembuatAkun())->get(route('admin.users.create'));

    $response->assertOk()
        ->assertSee(route('admin.users.store'), false)
        ->assertSee('name="name"', false)
        ->assertSee('name="email"', false)
        ->assertSee('name="password"', false)
        ->assertSee('name="password_confirmation"', false)
        ->assertSee('name="role"', false)
        ->assertSee('name="identity_number"', false)
        ->assertSee('name="user_type"', false)
        ->assertSee('value="pengguna"', false)
        ->assertSee('value="petugas"', false)
        ->assertDontSee('value="admin"', false)
        // C2 — status diisi controller, bukan field form.
        ->assertDontSee('name="status"', false);
});

it('membuat akun pengguna berstatus verified lalu kembali ke A3 (C2, bagian 11)', function (): void {
    $this->actingAs(adminPembuatAkun())
        ->post(route('admin.users.store'), isianTambahAkunSah())
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('success');

    $user = User::where('email', 'uji.tambah.akun@kampus.test')->firstOrFail();

    expect($user->status)->toBe('verified')
        ->and($user->role)->toBe('pengguna')
        ->and($user->identity_number)->toBe('2399666001')
        ->and($user->user_type)->toBe('dosen');
});

it('menyimpan password sebagai hash, bukan teks polos (C5)', function (): void {
    $this->actingAs(adminPembuatAkun())
        ->post(route('admin.users.store'), isianTambahAkunSah());

    $user = User::where('email', 'uji.tambah.akun@kampus.test')->firstOrFail();

    // Membuktikan password tidak tersimpan sebagai teks polos, dan hash-nya cocok.
    expect($user->password)->not->toBe('rahasia123')
        ->and(Hash::check('rahasia123', $user->password))->toBeTrue();
});

it('membuat akun petugas tanpa NIM/NIP dan tipe pengguna (C4)', function (): void {
    $this->actingAs(adminPembuatAkun())
        ->post(route('admin.users.store'), isianTambahAkunSah([
            'role' => 'petugas',
            'identity_number' => '',
            'user_type' => '',
        ]))
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHasNoErrors();

    $user = User::where('email', 'uji.tambah.akun@kampus.test')->firstOrFail();

    expect($user->status)->toBe('verified')
        ->and($user->role)->toBe('petugas')
        ->and($user->identity_number)->toBeNull()
        ->and($user->user_type)->toBeNull();
});

it('membuang NIM/NIP dan tipe pengguna yang ikut terkirim untuk petugas (C4)', function (): void {
    $this->actingAs(adminPembuatAkun())
        ->post(route('admin.users.store'), isianTambahAkunSah(['role' => 'petugas']))
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHasNoErrors();

    $user = User::where('email', 'uji.tambah.akun@kampus.test')->firstOrFail();

    expect($user->role)->toBe('petugas')
        ->and($user->identity_number)->toBeNull()
        ->and($user->user_type)->toBeNull();
});

it('tidak menolak akun petugas karena NIM/NIP terkirim yang sudah dipakai akun lain (C4)', function (): void {
    $nimTerpakai = User::where('role', 'pengguna')->whereNotNull('identity_number')->firstOrFail()->identity_number;

    // Tanpa pembersihan di prepareForValidation(), unique:users,identity_number
    // berjalan atas nilai ini dan menolak akun petugas yang tidak butuh NIM.
    $this->actingAs(adminPembuatAkun())
        ->post(route('admin.users.store'), isianTambahAkunSah([
            'role' => 'petugas',
            'identity_number' => $nimTerpakai,
        ]))
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHasNoErrors();

    $user = User::where('email', 'uji.tambah.akun@kampus.test')->firstOrFail();

    expect($user->role)->toBe('petugas')
        ->and($user->identity_number)->toBeNull()
        ->and($user->user_type)->toBeNull();
});

it('mengabaikan status yang disisipkan ke request (C2, F10)', function (): void {
    $this->actingAs(adminPembuatAkun())
        ->post(route('admin.users.store'), isianTambahAkunSah(['status' => 'pending']))
        ->assertSessionHasNoErrors();

    expect(User::where('email', 'uji.tambah.akun@kampus.test')->firstOrFail()->status)->toBe('verified');
});

it('menolak email yang sudah dipakai', function (): void {
    $sebelum = User::count();
    $emailTerpakai = User::where('role', 'pengguna')->firstOrFail()->email;

    $this->actingAs(adminPembuatAkun())
        ->post(route('admin.users.store'), isianTambahAkunSah(['email' => $emailTerpakai]))
        ->assertSessionHasErrors('email');

    expect(User::count())->toBe($sebelum);
});

it('menolak NIM/NIP yang sudah dipakai (C4)', function (): void {
    $sebelum = User::count();
    $nimTerpakai = User::where('role', 'pengguna')->whereNotNull('identity_number')->firstOrFail()->identity_number;

    $this->actingAs(adminPembuatAkun())
        ->post(route('admin.users.store'), isianTambahAkunSah(['identity_number' => $nimTerpakai]))
        ->assertSessionHasErrors('identity_number');

    expect(User::count())->toBe($sebelum);
});

it('menolak role admin (bagian 24)', function (): void {
    $sebelum = User::count();

    $this->actingAs(adminPembuatAkun())
        ->post(route('admin.users.store'), isianTambahAkunSah(['role' => 'admin']))
        ->assertSessionHasErrors('role');

    expect(User::count())->toBe($sebelum)
        ->and(User::where('email', 'uji.tambah.akun@kampus.test')->exists())->toBeFalse();
});

it('mempertahankan NIM/NIP dan tipe pengguna yang sudah diketik saat role belum dipilih', function (): void {
    $sebelum = User::count();
    $isian = isianTambahAkunSah();
    unset($isian['role']);

    $this->actingAs(adminPembuatAkun())
        ->from(route('admin.users.create'))
        ->post(route('admin.users.store'), $isian)
        ->assertRedirect(route('admin.users.create'))
        ->assertSessionHasErrors('role')
        ->assertSessionDoesntHaveErrors(['identity_number', 'user_type'])
        // Kesalahannya di role, jadi isian identitas tidak boleh ikut hilang.
        ->assertSessionHasInput('identity_number', '2399666001')
        ->assertSessionHasInput('user_type', 'dosen');

    // Dan benar-benar kembali terisi di form lewat old().
    $this->actingAs(adminPembuatAkun())->get(route('admin.users.create'))
        ->assertOk()
        ->assertSee('value="2399666001"', false)
        ->assertSee('<option value="dosen" selected', false);

    expect(User::count())->toBe($sebelum);
});

/**
 * Email sepanjang $panjang karakter yang tetap sah formatnya: bagian lokal 64
 * karakter (batas RFC), sisanya domain dengan label tidak lebih dari 63.
 */
function emailSepanjang(int $panjang): string
{
    $domain = str_repeat('b', $panjang - 64 - 1 - 46).'.'.str_repeat('c', 40).'.test';

    return str_repeat('a', 64).'@'.$domain;
}

it('menerima name, email, dan NIM/NIP tepat di batas panjang (bagian 24)', function (): void {
    $email = emailSepanjang(150);

    expect(strlen($email))->toBe(150);

    $this->actingAs(adminPembuatAkun())
        ->post(route('admin.users.store'), isianTambahAkunSah([
            'name' => str_repeat('n', 100),
            'email' => $email,
            'identity_number' => str_repeat('9', 30),
        ]))
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHasNoErrors();

    expect(User::where('email', $email)->exists())->toBeTrue();
});

it('menolak name, email, dan NIM/NIP yang melewati batas panjang (bagian 24)', function (): void {
    $sebelum = User::count();
    $email = emailSepanjang(151);

    expect(strlen($email))->toBe(151);

    $this->actingAs(adminPembuatAkun())
        ->post(route('admin.users.store'), isianTambahAkunSah(['name' => str_repeat('n', 101)]))
        ->assertSessionHasErrors('name')
        ->assertSessionDoesntHaveErrors(['email', 'identity_number']);

    $this->actingAs(adminPembuatAkun())
        ->post(route('admin.users.store'), isianTambahAkunSah(['email' => $email]))
        ->assertSessionHasErrors('email')
        ->assertSessionDoesntHaveErrors(['name', 'identity_number']);

    $this->actingAs(adminPembuatAkun())
        ->post(route('admin.users.store'), isianTambahAkunSah(['identity_number' => str_repeat('9', 31)]))
        ->assertSessionHasErrors('identity_number')
        ->assertSessionDoesntHaveErrors(['name', 'email']);

    expect(User::count())->toBe($sebelum);
});

it('menolak tipe pengguna di luar mahasiswa, dosen, dan staf untuk role pengguna', function (): void {
    $sebelum = User::count();

    foreach (['admin', 'petugas', 'mahasiswi', 'MAHASISWA'] as $tipe) {
        $this->actingAs(adminPembuatAkun())
            ->post(route('admin.users.store'), isianTambahAkunSah(['user_type' => $tipe]))
            ->assertSessionHasErrors('user_type');
    }

    expect(User::count())->toBe($sebelum);
});

it('mewajibkan NIM/NIP dan tipe pengguna saat role pengguna (C4)', function (): void {
    $sebelum = User::count();

    $this->actingAs(adminPembuatAkun())
        ->post(route('admin.users.store'), isianTambahAkunSah([
            'identity_number' => '',
            'user_type' => '',
        ]))
        ->assertSessionHasErrors(['identity_number', 'user_type']);

    expect(User::count())->toBe($sebelum);
});

it('menolak password kurang dari 8 karakter dan konfirmasi yang tidak cocok', function (): void {
    $sebelum = User::count();

    $this->actingAs(adminPembuatAkun())
        ->post(route('admin.users.store'), isianTambahAkunSah([
            'password' => 'pendek1',
            'password_confirmation' => 'pendek1',
        ]))
        ->assertSessionHasErrors('password');

    $this->actingAs(adminPembuatAkun())
        ->post(route('admin.users.store'), isianTambahAkunSah([
            'password_confirmation' => 'rahasia999',
        ]))
        ->assertSessionHasErrors('password');

    expect(User::count())->toBe($sebelum);
});

it('menolak akses petugas ke A4 dengan 403', function (): void {
    $sebelum = User::count();
    $petugas = User::where('role', 'petugas')->firstOrFail();

    $this->actingAs($petugas)->get(route('admin.users.create'))->assertForbidden();
    $this->actingAs($petugas)->post(route('admin.users.store'), isianTambahAkunSah())->assertForbidden();

    expect(User::count())->toBe($sebelum);
});
