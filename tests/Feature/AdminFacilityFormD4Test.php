<?php

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    if (! Schema::hasTable('facilities') || ! Schema::hasTable('reservations') || ! Schema::hasTable('users')) {
        $this->markTestSkipped('Butuh database yang sudah dimigrasi.');
    }
});

/*
|--------------------------------------------------------------------------
| D4 pada Form Edit Fasilitas (A2)
|--------------------------------------------------------------------------
| Admin yang membuka form edit fasilitas active melihat peringatan berisi
| reservasi approved mendatang milik fasilitas itu. Peringatan tidak tampil
| pada form tambah, fasilitas non-active, atau fasilitas tanpa reservasi.
| Menyimpan perubahan status tidak membatalkan reservasi apa pun.
*/

it('D4 A2: edit fasilitas active menampilkan peringatan dan data reservasi approved mendatang', function (): void {
    $admin = buatAkunDenganRole('admin');
    $facility = Facility::factory()->active()->create();
    $pemohon = User::factory()->create(['name' => 'Pemohon Terdampak Satu']);

    Reservation::factory()->approved()->create([
        'facility_id' => $facility->id,
        'user_id' => $pemohon->id,
        'start_time' => now()->addDays(3)->setTime(10, 0),
        'end_time' => now()->addDays(3)->setTime(12, 0),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.facilities.edit', $facility))
        ->assertOk()
        ->assertViewIs('admin.facilities.form')
        ->assertSee('Terdapat 1 reservasi yang sudah disetujui')
        ->assertSee('Pemohon Terdampak Satu')
        ->assertSee(now()->addDays(3)->format('d M Y'))
        ->assertSee('10:00')
        ->assertSee('Peringatan ini berlaku jika status diubah ke Nonaktif atau Dalam Perbaikan.');
});

it('D4 A2: reservasi fasilitas lain, pending, dan yang sudah lewat tidak ditampilkan', function (): void {
    $admin = buatAkunDenganRole('admin');
    $facility = Facility::factory()->active()->create();
    $fasilitasLain = Facility::factory()->active()->create();

    $terdampak = User::factory()->create(['name' => 'Pemohon Yang Terdampak']);
    Reservation::factory()->approved()->create([
        'facility_id' => $facility->id,
        'user_id' => $terdampak->id,
    ]);

    Reservation::factory()->approved()->create([
        'facility_id' => $fasilitasLain->id,
        'user_id' => User::factory()->create(['name' => 'Pemohon Fasilitas Lain'])->id,
    ]);

    Reservation::factory()->pending()->create([
        'facility_id' => $facility->id,
        'user_id' => User::factory()->create(['name' => 'Pemohon Masih Pending'])->id,
    ]);

    Reservation::factory()->approved()->create([
        'facility_id' => $facility->id,
        'user_id' => User::factory()->create(['name' => 'Pemohon Sudah Lewat'])->id,
        'start_time' => now()->subDays(2)->setTime(9, 0),
        'end_time' => now()->subDays(2)->setTime(11, 0),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.facilities.edit', $facility))
        ->assertOk()
        ->assertSee('Terdapat 1 reservasi yang sudah disetujui')
        ->assertSee('Pemohon Yang Terdampak')
        ->assertDontSee('Pemohon Fasilitas Lain')
        ->assertDontSee('Pemohon Masih Pending')
        ->assertDontSee('Pemohon Sudah Lewat');
});

it('D4 A2: edit fasilitas under_maintenance menampilkan peringatan jika status diubah ke Nonaktif', function (): void {
    $admin = buatAkunDenganRole('admin');
    $facility = Facility::factory()->underMaintenance()->create();
    $pemohon = User::factory()->create(['name' => 'Pemohon Terdampak Perbaikan']);

    Reservation::factory()->approved()->create([
        'facility_id' => $facility->id,
        'user_id' => $pemohon->id,
        'start_time' => now()->addDays(3)->setTime(10, 0),
        'end_time' => now()->addDays(3)->setTime(12, 0),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.facilities.edit', $facility))
        ->assertOk()
        ->assertViewIs('admin.facilities.form')
        ->assertSee('d4-warning-section')
        ->assertSee('Terdapat 1 reservasi yang sudah disetujui')
        ->assertSee('Pemohon Terdampak Perbaikan')
        ->assertSee('Peringatan ini berlaku jika status diubah ke Nonaktif.')
        ->assertDontSee('Peringatan ini berlaku jika status diubah ke Nonaktif atau Dalam Perbaikan.');
});

it('D4 A2: fasilitas inactive tidak menampilkan peringatan walau punya reservasi', function (): void {
    $admin = buatAkunDenganRole('admin');
    $facility = Facility::factory()->inactive()->create();

    Reservation::factory()->approved()->create([
        'facility_id' => $facility->id,
        'user_id' => User::factory()->create(['name' => 'Pemohon Fasilitas Nonaktif'])->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.facilities.edit', $facility))
        ->assertOk()
        ->assertDontSee('d4-warning-section')
        ->assertDontSee('Terdapat 1 reservasi')
        ->assertDontSee('Pemohon Fasilitas Nonaktif')
        ->assertDontSee('Peringatan ini berlaku');
});

it('D4 A2: fasilitas active tanpa reservasi mendatang tidak menampilkan peringatan', function (): void {
    $admin = buatAkunDenganRole('admin');
    $facility = Facility::factory()->active()->create();

    $this->actingAs($admin)
        ->get(route('admin.facilities.edit', $facility))
        ->assertOk()
        ->assertDontSee('reservasi yang sudah disetujui')
        ->assertDontSee('Peringatan ini berlaku');
});

it('D4 A2: form tambah fasilitas tidak menampilkan peringatan', function (): void {
    $admin = buatAkunDenganRole('admin');

    // Reservasi approved mendatang di fasilitas lain tidak boleh bocor ke form tambah.
    Reservation::factory()->approved()->create([
        'user_id' => User::factory()->create(['name' => 'Pemohon Di Fasilitas Lain'])->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.facilities.create'))
        ->assertOk()
        ->assertViewIs('admin.facilities.form')
        ->assertDontSee('d4-warning-section')
        ->assertDontSee('reservasi yang sudah disetujui')
        ->assertDontSee('Pemohon Di Fasilitas Lain')
        ->assertDontSee('Peringatan ini berlaku');
});

it('D4 A2: update status ke inactive lewat form edit tidak membatalkan reservasi approved', function (): void {
    $admin = buatAkunDenganRole('admin');
    $facility = Facility::factory()->active()->create([
        'name' => 'Fasilitas Akan Dinonaktifkan',
        'type' => 'Aula',
        'location' => 'Gedung Serba Guna',
        'capacity' => 100,
    ]);

    $reservation = Reservation::factory()->approved()->create([
        'facility_id' => $facility->id,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.facilities.update', $facility), [
            'name' => 'Fasilitas Akan Dinonaktifkan',
            'type' => 'Aula',
            'location' => 'Gedung Serba Guna',
            'capacity' => 100,
            'status' => 'inactive',
        ])
        ->assertRedirect(route('admin.facilities.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('facilities', [
        'id' => $facility->id,
        'status' => 'inactive',
    ]);

    $this->assertDatabaseHas('reservations', [
        'id' => $reservation->id,
        'status' => 'approved',
    ]);
});
