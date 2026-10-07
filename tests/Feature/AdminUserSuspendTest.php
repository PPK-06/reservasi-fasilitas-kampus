<?php

use App\Models\Facility;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * A5 Detail Akun — aksi suspend (C2 verified → suspended) dan modal
 * konfirmasinya yang memuat peringatan D4.
 *
 * Seluruh data dibuat lewat helper tests/Pest.php dan factory. Fasilitas diberi
 * nama unik supaya assertSee/assertDontSee tidak bertabrakan dengan data seeder.
 */
uses(DatabaseTransactions::class);

/*
 * Tampilan (GET admin.users.show)
 */

it('menampilkan tombol Nonaktifkan dan form suspend untuk akun verified', function (): void {
    $user = buatAkunDenganRole('pengguna');

    $this->actingAs(buatAkunDenganRole('admin'))
        ->get(route('admin.users.show', $user))
        ->assertOk()
        ->assertSee('Nonaktifkan')
        ->assertSee('data-bs-target="#suspendModal"', false)
        ->assertSee(route('admin.users.suspend', $user), false);
});

it('tidak menampilkan form suspend untuk akun yang bukan verified', function (string $status): void {
    $user = buatAkunDenganStatus($status);

    $this->actingAs(buatAkunDenganRole('admin'))
        ->get(route('admin.users.show', $user))
        ->assertOk()
        ->assertDontSee(route('admin.users.suspend', $user), false)
        ->assertDontSee('suspendModal', false);
})->with(['pending', 'rejected', 'suspended']);

it('tidak menampilkan form suspend di A5 milik admin yang sedang login', function (): void {
    $admin = buatAkunDenganRole('admin');

    $this->actingAs($admin)
        ->get(route('admin.users.show', $admin))
        ->assertOk()
        ->assertDontSee(route('admin.users.suspend', $admin), false)
        ->assertDontSee('suspendModal', false);
});

it('menampilkan reservasi approved mendatang dari beberapa fasilitas di modal (D4)', function (): void {
    $user = buatAkunDenganRole('pengguna');
    $alfa = Facility::factory()->create(['name' => 'Ruang Uji Suspend Alfa']);
    $beta = Facility::factory()->create(['name' => 'Ruang Uji Suspend Beta']);

    Reservation::factory()->for($user)->for($alfa)->approved()->create();
    Reservation::factory()->for($user)->for($beta)->approved()->create([
        'start_time' => now()->addDays(3)->setTime(13, 0),
        'end_time' => now()->addDays(3)->setTime(15, 0),
    ]);

    $this->actingAs(buatAkunDenganRole('admin'))
        ->get(route('admin.users.show', $user))
        ->assertOk()
        ->assertSee('Ruang Uji Suspend Alfa')
        ->assertSee('Ruang Uji Suspend Beta')
        ->assertSee('Terdapat 2 reservasi')
        ->assertSee('modal-lg', false);
});

it('tidak menampilkan reservasi pending, yang sudah lewat, atau milik akun lain', function (): void {
    $user = buatAkunDenganRole('pengguna');
    $lain = buatAkunDenganRole('pengguna');
    $pending = Facility::factory()->create(['name' => 'Ruang Uji Suspend Pending']);
    $lewat = Facility::factory()->create(['name' => 'Ruang Uji Suspend Lewat']);
    $milikLain = Facility::factory()->create(['name' => 'Ruang Uji Suspend Milik Lain']);

    Reservation::factory()->for($user)->for($pending)->pending()->create();
    Reservation::factory()->for($user)->for($lewat)->approved()->create([
        'start_time' => now()->subDays(2)->setTime(9, 0),
        'end_time' => now()->subDays(2)->setTime(11, 0),
    ]);
    Reservation::factory()->for($lain)->for($milikLain)->approved()->create();

    $this->actingAs(buatAkunDenganRole('admin'))
        ->get(route('admin.users.show', $user))
        ->assertOk()
        ->assertDontSee('Ruang Uji Suspend Pending')
        ->assertDontSee('Ruang Uji Suspend Lewat')
        ->assertDontSee('Ruang Uji Suspend Milik Lain')
        ->assertDontSee('Terdapat');
});

it('tetap merender modal tanpa peringatan D4 bila akun tidak punya reservasi', function (): void {
    $user = buatAkunDenganRole('pengguna');

    $this->actingAs(buatAkunDenganRole('admin'))
        ->get(route('admin.users.show', $user))
        ->assertOk()
        ->assertSee('id="suspendModal"', false)
        ->assertDontSee('tidak dibatalkan otomatis')
        ->assertDontSee('modal-lg', false);
});

/*
 * Aksi (PATCH admin.users.suspend)
 */

it('menonaktifkan akun verified lalu kembali ke A5 dengan pesan sukses', function (string $role): void {
    $user = buatAkunDenganRole($role);

    $this->actingAs(buatAkunDenganRole('admin'))
        ->patch(route('admin.users.suspend', $user))
        ->assertRedirect(route('admin.users.show', $user))
        ->assertSessionHas('success');

    expect($user->fresh()->status)->toBe('suspended');
})->with(['pengguna', 'petugas']);

it('menolak suspend untuk akun yang bukan verified tanpa mengubah status (C2)', function (string $status): void {
    $user = buatAkunDenganStatus($status);

    $this->actingAs(buatAkunDenganRole('admin'))
        ->patch(route('admin.users.suspend', $user))
        ->assertRedirect(route('admin.users.show', $user))
        ->assertSessionHas('error')
        ->assertSessionMissing('success');

    expect($user->fresh()->status)->toBe($status);
})->with(['pending', 'rejected', 'suspended']);

it('menolak admin yang menonaktifkan akunnya sendiri', function (): void {
    $admin = buatAkunDenganRole('admin');

    $this->actingAs($admin)
        ->patch(route('admin.users.suspend', $admin))
        ->assertRedirect(route('admin.users.show', $admin))
        ->assertSessionHas('error')
        ->assertSessionMissing('success');

    expect($admin->fresh()->status)->toBe('verified');
});

it('membolehkan admin menonaktifkan admin lain', function (): void {
    $adminLain = buatAkunDenganRole('admin');

    $this->actingAs(buatAkunDenganRole('admin'))
        ->patch(route('admin.users.suspend', $adminLain))
        ->assertRedirect(route('admin.users.show', $adminLain))
        ->assertSessionHas('success');

    expect($adminLain->fresh()->status)->toBe('suspended');
});

it('tidak membatalkan reservasi approved milik akun yang dinonaktifkan (C6)', function (): void {
    $user = buatAkunDenganRole('pengguna');
    $facility = Facility::factory()->create(['name' => 'Ruang Uji Suspend C6']);
    $reservation = Reservation::factory()->for($user)->for($facility)->approved()->create();

    $this->actingAs(buatAkunDenganRole('admin'))
        ->patch(route('admin.users.suspend', $user))
        ->assertSessionHas('success');

    expect($user->fresh()->status)->toBe('suspended')
        ->and($reservation->fresh()->status)->toBe('approved');
});

it('hanya mengubah status saat suspend (C2, C6)', function (): void {
    $user = buatAkunDenganRole('pengguna');
    $asli = $user->only(['name', 'email', 'role', 'password']);

    $this->actingAs(buatAkunDenganRole('admin'))
        ->patch(route('admin.users.suspend', $user));

    $segar = $user->fresh();

    expect($segar)->not->toBeNull()
        ->and($segar->only(array_keys($asli)))->toBe($asli)
        ->and($segar->status)->toBe('suspended');
});

it('menolak suspend dari petugas dan pengguna dengan 403', function (string $role): void {
    $user = buatAkunDenganRole('pengguna');

    $this->actingAs(buatAkunDenganRole($role))
        ->patch(route('admin.users.suspend', $user))
        ->assertForbidden();

    expect($user->fresh()->status)->toBe('verified');
})->with(['petugas', 'pengguna']);

it('mengarahkan tamu yang mencoba suspend ke halaman login', function (): void {
    $user = buatAkunDenganRole('pengguna');

    $this->patch(route('admin.users.suspend', $user))
        ->assertRedirect(route('login'));

    expect($user->fresh()->status)->toBe('verified');
});

it('mengeluarkan akun yang dinonaktifkan saat membuka halaman landing-nya', function (): void {
    /*
     * Petugas, bukan pengguna: landing pengguna (facilities.index) adalah
     * route publik tanpa middleware role, jadi tidak memeriksa status.
     * fresh() meniru request berikutnya, yang memuat ulang akun dari database.
     */
    $petugas = buatAkunDenganRole('petugas');

    $this->actingAs(buatAkunDenganRole('admin'))
        ->patch(route('admin.users.suspend', $petugas))
        ->assertSessionHas('success');

    $this->actingAs($petugas->fresh())
        ->get(route($petugas->landingRouteName()))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors(['email' => 'Akun Anda dinonaktifkan. Hubungi admin.']);

    $this->assertGuest();
});
