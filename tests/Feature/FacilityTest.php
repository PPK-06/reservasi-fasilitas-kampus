<?php

use App\Models\Facility;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    if (! Schema::hasTable('facilities') || ! Schema::hasTable('users')) {
        $this->markTestSkipped('Butuh database yang sudah dimigrasi.');
    }
});

/*
|--------------------------------------------------------------------------
| P1: Katalog Fasilitas Publik
|--------------------------------------------------------------------------
| Tamu (guest) bisa membuka halaman katalog fasilitas. Ini halaman publik,
| sehingga response harus 200 OK dan bukan diarahkan ke halaman login (302).
*/

it('P1: tamu dapat mengakses katalog fasilitas publik dengan status 200', function (): void {
    $this->get(route('facilities.index'))
        ->assertOk()
        ->assertViewIs('facilities.index');
});

it('P1: menampilkan fasilitas yang ada di katalog publik', function (): void {
    $facility = Facility::factory()->active()->create([
        'name' => 'Aula Uji Publik P1',
    ]);

    $this->get(route('facilities.index'))
        ->assertOk()
        ->assertSee('Aula Uji Publik P1');
});

/*
|--------------------------------------------------------------------------
| A1: Kelola Fasilitas Admin
|--------------------------------------------------------------------------
| Admin dapat membuka halaman daftar fasilitas dan mengubah status fasilitas.
| Pengguna biasa dan petugas mendapat 403 Forbidden. Tamu dialihkan ke login.
*/

it('A1: tamu dialihkan ke login saat mengakses daftar fasilitas admin', function (): void {
    $this->get(route('admin.facilities.index'))
        ->assertRedirect(route('login'));
});

it('A1: pengguna biasa ditolak 403 saat mengakses daftar fasilitas admin', function (): void {
    $pengguna = buatAkunDenganRole('pengguna');

    $this->actingAs($pengguna)
        ->get(route('admin.facilities.index'))
        ->assertForbidden();
});

it('A1: petugas ditolak 403 saat mengakses daftar fasilitas admin', function (): void {
    $petugas = buatAkunDenganRole('petugas');

    $this->actingAs($petugas)
        ->get(route('admin.facilities.index'))
        ->assertForbidden();
});

it('A1: admin dapat membuka daftar fasilitas admin', function (): void {
    $admin = buatAkunDenganRole('admin');

    $this->actingAs($admin)
        ->get(route('admin.facilities.index'))
        ->assertOk()
        ->assertViewIs('admin.facilities.index');
});

it('A1: admin dapat mengubah status fasilitas antara active dan inactive', function (): void {
    $admin = buatAkunDenganRole('admin');
    $facility = Facility::factory()->active()->create([
        'name' => 'Fasilitas Toggle Admin',
    ]);

    // Ubah ke inactive
    $response = $this->actingAs($admin)
        ->patch(route('admin.facilities.status', $facility), [
            'status' => 'inactive',
        ]);

    $response->assertRedirect(route('admin.facilities.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('facilities', [
        'id' => $facility->id,
        'status' => 'inactive',
    ]);

    // Ubah kembali ke active
    $response = $this->actingAs($admin)
        ->patch(route('admin.facilities.status', $facility), [
            'status' => 'active',
        ]);

    $response->assertRedirect(route('admin.facilities.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('facilities', [
        'id' => $facility->id,
        'status' => 'active',
    ]);
});

it('A1: tamu dan non-admin dilarang mengubah status fasilitas di route admin', function (): void {
    $facility = Facility::factory()->active()->create();

    // Tamu -> login
    $this->patch(route('admin.facilities.status', $facility), ['status' => 'inactive'])
        ->assertRedirect(route('login'));

    // Pengguna -> 403
    $pengguna = buatAkunDenganRole('pengguna');
    $this->actingAs($pengguna)
        ->patch(route('admin.facilities.status', $facility), ['status' => 'inactive'])
        ->assertForbidden();

    // Petugas -> 403
    $petugas = buatAkunDenganRole('petugas');
    $this->actingAs($petugas)
        ->patch(route('admin.facilities.status', $facility), ['status' => 'inactive'])
        ->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| A2: Tambah dan Edit Fasilitas oleh Admin
|--------------------------------------------------------------------------
| Admin berhasil menambah dan mengedit fasilitas. Kasus validasi gagal
| mengembalikan error dan input lama tetap dipertahankan.
*/

it('A2: admin dapat membuka form tambah dan form edit fasilitas', function (): void {
    $admin = buatAkunDenganRole('admin');
    $facility = Facility::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.facilities.create'))
        ->assertOk()
        ->assertViewIs('admin.facilities.form');

    $this->actingAs($admin)
        ->get(route('admin.facilities.edit', $facility))
        ->assertOk()
        ->assertViewIs('admin.facilities.form');
});

it('A2: admin berhasil menambah fasilitas baru', function (): void {
    $admin = buatAkunDenganRole('admin');

    $payload = [
        'name' => 'Laboratorium Jaringan Baru',
        'type' => 'Laboratorium',
        'location' => 'Gedung B',
        'capacity' => 45,
        'description' => 'Fasilitas baru untuk riset jaringan.',
        'status' => 'active',
    ];

    $response = $this->actingAs($admin)
        ->post(route('admin.facilities.store'), $payload);

    $response->assertRedirect(route('admin.facilities.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('facilities', [
        'name' => 'Laboratorium Jaringan Baru',
        'type' => 'Laboratorium',
        'location' => 'Gedung B',
        'capacity' => 45,
        'status' => 'active',
    ]);
});

it('A2: admin berhasil mengedit fasilitas yang sudah ada', function (): void {
    $admin = buatAkunDenganRole('admin');
    $facility = Facility::factory()->create([
        'name' => 'Nama Sebelum Diedit',
        'type' => 'Ruang Kelas',
        'location' => 'Gedung A',
        'capacity' => 30,
        'status' => 'active',
    ]);

    $payload = [
        'name' => 'Nama Sesudah Diedit',
        'type' => 'Aula',
        'location' => 'Gedung Serba Guna',
        'capacity' => 150,
        'description' => 'Deskripsi yang sudah diperbarui.',
        'status' => 'inactive',
    ];

    $response = $this->actingAs($admin)
        ->patch(route('admin.facilities.update', $facility), $payload);

    $response->assertRedirect(route('admin.facilities.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('facilities', [
        'id' => $facility->id,
        'name' => 'Nama Sesudah Diedit',
        'type' => 'Aula',
        'location' => 'Gedung Serba Guna',
        'capacity' => 150,
        'status' => 'inactive',
    ]);
});

it('A2: validasi gagal saat input tidak valid saat tambah fasilitas', function (): void {
    $admin = buatAkunDenganRole('admin');

    $payload = [
        'name' => '', // required
        'type' => 'TipeNgawur', // invalid in
        'location' => 'LokasiNgawur', // invalid in
        'capacity' => -5, // min 1
        'status' => 'invalid_status', // invalid in
    ];

    $response = $this->actingAs($admin)
        ->from(route('admin.facilities.create'))
        ->post(route('admin.facilities.store'), $payload);

    $response->assertRedirect(route('admin.facilities.create'))
        ->assertSessionHasErrors(['name', 'type', 'location', 'capacity', 'status'])
        ->assertSessionHasInput('type', 'TipeNgawur')
        ->assertSessionHasInput('location', 'LokasiNgawur');

    $this->assertDatabaseMissing('facilities', ['type' => 'TipeNgawur']);
});

it('A2: validasi gagal saat input tidak valid saat edit fasilitas dan data tidak berubah', function (): void {
    $admin = buatAkunDenganRole('admin');
    $facility = Facility::factory()->create([
        'name' => 'Nama Asli Edit',
        'type' => 'Ruang Kelas',
        'location' => 'Gedung A',
        'capacity' => 30,
        'status' => 'active',
    ]);

    $payload = [
        'name' => '', // required
        'type' => 'TipeNgawur', // invalid in
        'location' => 'LokasiNgawur', // invalid in
        'capacity' => -5, // min 1
        'status' => 'invalid_status', // invalid in
    ];

    $this->actingAs($admin)
        ->from(route('admin.facilities.edit', $facility))
        ->patch(route('admin.facilities.update', $facility), $payload)
        ->assertRedirect(route('admin.facilities.edit', $facility))
        ->assertSessionHasErrors(['name', 'type', 'location', 'capacity', 'status'])
        ->assertSessionHasInput('type', 'TipeNgawur');

    $this->assertDatabaseHas('facilities', [
        'id' => $facility->id,
        'name' => 'Nama Asli Edit',
        'type' => 'Ruang Kelas',
        'location' => 'Gedung A',
        'capacity' => 30,
        'status' => 'active',
    ]);
});

it('A2: petugas ditolak 403 di route store dan update fasilitas admin', function (): void {
    $petugas = buatAkunDenganRole('petugas');
    $facility = Facility::factory()->create(['name' => 'Fasilitas Tetap']);

    $this->actingAs($petugas)
        ->post(route('admin.facilities.store'), [
            'name' => 'Fasilitas Selundupan',
            'type' => 'Aula',
            'location' => 'Gedung Serba Guna',
            'capacity' => 10,
            'status' => 'active',
        ])
        ->assertForbidden();

    $this->assertDatabaseMissing('facilities', ['name' => 'Fasilitas Selundupan']);

    $this->actingAs($petugas)
        ->patch(route('admin.facilities.update', $facility), [
            'name' => 'Nama Diretas',
            'type' => 'Aula',
            'location' => 'Gedung Serba Guna',
            'capacity' => 10,
            'status' => 'active',
        ])
        ->assertForbidden();

    $this->assertDatabaseHas('facilities', ['id' => $facility->id, 'name' => 'Fasilitas Tetap']);
});

it('O6: akun selain petugas ditolak di halaman kelola fasilitas petugas', function (): void {
    $this->get(route('officer.facilities.index'))
        ->assertRedirect(route('login'));

    foreach (['pengguna', 'admin'] as $role) {
        $this->actingAs(buatAkunDenganRole($role))
            ->get(route('officer.facilities.index'))
            ->assertForbidden();
    }
});

/*
|--------------------------------------------------------------------------
| O6: Petugas Ubah Status Fasilitas (Active <-> Under Maintenance)
|--------------------------------------------------------------------------
| Petugas dapat mengubah status fasilitas antara active dan under_maintenance.
| Admin ditolak 403 saat mengakses route status fasilitas petugas.
*/

it('O6: petugas dapat membuka halaman O6 kelola ketersediaan fasilitas', function (): void {
    $petugas = buatAkunDenganRole('petugas');

    $this->actingAs($petugas)
        ->get(route('officer.facilities.index'))
        ->assertOk()
        ->assertViewIs('officer.facilities.index');
});

it('O6: petugas dapat mengubah status fasilitas dari active ke under_maintenance dan sebaliknya', function (): void {
    $petugas = buatAkunDenganRole('petugas');
    $facility = Facility::factory()->active()->create([
        'name' => 'Ruang Uji Pemeliharaan',
    ]);

    // Active -> under_maintenance
    $response = $this->actingAs($petugas)
        ->patch(route('officer.facilities.status', $facility), [
            'status' => 'under_maintenance',
        ]);

    $response->assertSessionHas('success');

    $this->assertDatabaseHas('facilities', [
        'id' => $facility->id,
        'status' => 'under_maintenance',
    ]);

    // Under maintenance -> active
    $response = $this->actingAs($petugas)
        ->patch(route('officer.facilities.status', $facility), [
            'status' => 'active',
        ]);

    $response->assertSessionHas('success');

    $this->assertDatabaseHas('facilities', [
        'id' => $facility->id,
        'status' => 'active',
    ]);
});

it('O6: admin ditolak 403 saat mengakses route ubah status fasilitas petugas', function (): void {
    $admin = buatAkunDenganRole('admin');
    $facility = Facility::factory()->active()->create();

    $this->actingAs($admin)
        ->patch(route('officer.facilities.status', $facility), [
            'status' => 'under_maintenance',
        ])
        ->assertForbidden();
});

it('O6: petugas ditolak 403 jika mencoba mengubah fasilitas yang berstatus inactive', function (): void {
    $petugas = buatAkunDenganRole('petugas');
    $facility = Facility::factory()->inactive()->create();

    $this->actingAs($petugas)
        ->patch(route('officer.facilities.status', $facility), [
            'status' => 'active',
        ])
        ->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| Wewenang D3: Pembagian Wewenang Status Antara Admin dan Petugas
|--------------------------------------------------------------------------
| Petugas kirim status=inactive harus ditolak (validasi).
| Admin kirim under_maintenance di status admin juga harus ditolak (validasi).
*/

it('Wewenang D3: petugas mengirim status=inactive ditolak oleh validasi', function (): void {
    $petugas = buatAkunDenganRole('petugas');
    $facility = Facility::factory()->active()->create();

    $response = $this->actingAs($petugas)
        ->patch(route('officer.facilities.status', $facility), [
            'status' => 'inactive',
        ]);

    $response->assertSessionHasErrors('status');

    // Pastikan status di database tidak berubah
    $this->assertDatabaseHas('facilities', [
        'id' => $facility->id,
        'status' => 'active',
    ]);
});

it('Wewenang D3: admin mengirim status=under_maintenance ditolak oleh validasi di route admin', function (): void {
    $admin = buatAkunDenganRole('admin');
    $facility = Facility::factory()->active()->create();

    $response = $this->actingAs($admin)
        ->patch(route('admin.facilities.status', $facility), [
            'status' => 'under_maintenance',
        ]);

    $response->assertSessionHasErrors('status');

    // Pastikan status di database tidak berubah
    $this->assertDatabaseHas('facilities', [
        'id' => $facility->id,
        'status' => 'active',
    ]);
});
