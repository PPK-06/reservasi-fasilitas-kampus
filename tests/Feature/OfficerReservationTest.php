<?php

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    if (! Schema::hasTable('reservations')) {
        $this->markTestSkipped('Butuh database yang sudah dimigrasi.');
    }
});

it('mengarahkan tamu ke login saat mengakses O1 dashboard petugas', function (): void {
    $this->get(route('officer.dashboard'))
        ->assertRedirect(route('login'));
});

it('memberi 403 untuk pengguna saat mengakses O1 dashboard petugas', function (): void {
    $pengguna = buatAkunDenganRole('pengguna');
    $this->actingAs($pengguna)
        ->get(route('officer.dashboard'))
        ->assertForbidden();
});

it('petugas dapat mengakses O1 dashboard antrian', function (): void {
    $officer = buatAkunDenganRole('petugas');
    $this->actingAs($officer)
        ->get(route('officer.dashboard'))
        ->assertOk()
        ->assertViewIs('officer.dashboard')
        ->assertSee('Dashboard Antrian');
});

it('mengarahkan tamu ke login saat mengakses O2 daftar reservasi petugas', function (): void {
    $this->get(route('officer.reservations.index'))
        ->assertRedirect(route('login'));
});

it('memberi 403 untuk pengguna saat mengakses O2 daftar reservasi petugas', function (): void {
    $pengguna = buatAkunDenganRole('pengguna');
    $this->actingAs($pengguna)
        ->get(route('officer.reservations.index'))
        ->assertForbidden();
});

it('petugas dapat mengakses O2 daftar antrian reservasi', function (): void {
    $officer = buatAkunDenganRole('petugas');
    $this->actingAs($officer)
        ->get(route('officer.reservations.index'))
        ->assertOk()
        ->assertViewIs('officer.reservations.index');
});

it('mengarahkan tamu ke login saat mengakses O3 detail reservasi petugas', function (): void {
    $res = Reservation::factory()->create();
    $this->get(route('officer.reservations.show', $res))
        ->assertRedirect(route('login'));
});

it('petugas dapat melihat O3 detail reservasi', function (): void {
    $officer = buatAkunDenganRole('petugas');
    $res = Reservation::factory()->create();

    $this->actingAs($officer)
        ->get(route('officer.reservations.show', $res))
        ->assertOk()
        ->assertViewIs('officer.reservations.show');
});

it('petugas dapat menyetujui reservasi pending di O3', function (): void {
    $officer = buatAkunDenganRole('petugas');
    $res = Reservation::factory()->pending()->create();

    $this->actingAs($officer)
        ->patch(route('officer.reservations.approve', $res))
        ->assertRedirect(route('officer.reservations.index'));

    expect($res->fresh()->status)->toBe('approved');
});

it('petugas dapat menolak reservasi pending dengan alasan di O3', function (): void {
    $officer = buatAkunDenganRole('petugas');
    $res = Reservation::factory()->pending()->create();

    $this->actingAs($officer)
        ->patch(route('officer.reservations.reject', $res), [
            'status_reason' => 'Ruangan dipakai kegiatan jurusan yang mendesak',
        ])
        ->assertRedirect(route('officer.reservations.index'));

    expect($res->fresh()->status)->toBe('rejected')
        ->and($res->fresh()->status_reason)->toBe('Ruangan dipakai kegiatan jurusan yang mendesak');
});

it('petugas dapat membatalkan reservasi approved dengan alasan di O3', function (): void {
    $officer = buatAkunDenganRole('petugas');
    $res = Reservation::factory()->approved()->create();

    $this->actingAs($officer)
        ->patch(route('officer.reservations.cancel', $res), [
            'status_reason' => 'Perbaikan darurat instalasi listrik ruangan',
        ])
        ->assertRedirect(route('officer.reservations.index'));

    expect($res->fresh()->status)->toBe('cancelled_by_officer')
        ->and($res->fresh()->status_reason)->toBe('Perbaikan darurat instalasi listrik ruangan');
});

it('petugas tidak dapat menyetujui reservasi pending yang waktu mulainya sudah lewat A8', function (): void {
    $officer = buatAkunDenganRole('petugas');
    $res = Reservation::factory()->pending()->create([
        'start_time' => now()->subHours(2),
        'end_time' => now()->subHour(),
    ]);

    $this->actingAs($officer)
        ->patch(route('officer.reservations.approve', $res))
        ->assertRedirect()
        ->assertSessionHas('error', 'Reservasi ini sudah terlewat dan tidak dapat disetujui.');

    expect($res->fresh()->status)->toBe('pending');
});

it('petugas tidak dapat menyetujui reservasi jika bertumpuk dengan reservasi approved lain F2', function (): void {
    $officer = buatAkunDenganRole('petugas');
    $facility = Facility::factory()->create();
    $facilityLain = Facility::factory()->create();

    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $userC = User::factory()->create();

    $day = now()->addDays(3);

    // Pengecoh 2: approved 10:00-12:00 milik user C di FASILITAS LAIN
    Reservation::factory()->approved()->create([
        'facility_id' => $facilityLain->id,
        'user_id' => $userC->id,
        'start_time' => (clone $day)->setTime(10, 0, 0),
        'end_time' => (clone $day)->setTime(12, 0, 0),
    ]);

    // Pengecoh 1: approved 07:00-09:00 milik user B di fasilitas yang sama
    Reservation::factory()->approved()->create([
        'facility_id' => $facility->id,
        'user_id' => $userB->id,
        'start_time' => (clone $day)->setTime(7, 0, 0),
        'end_time' => (clone $day)->setTime(9, 0, 0),
    ]);

    // Approved 10:00-12:00 milik user A di fasilitas yang sama
    Reservation::factory()->approved()->create([
        'facility_id' => $facility->id,
        'user_id' => $userA->id,
        'start_time' => (clone $day)->setTime(10, 0, 0),
        'end_time' => (clone $day)->setTime(12, 0, 0),
    ]);

    // Pending 11:00-13:00 milik pengguna yang bentrok dengan user A
    $resBentrok = Reservation::factory()->pending()->create([
        'facility_id' => $facility->id,
        'start_time' => (clone $day)->setTime(11, 0, 0),
        'end_time' => (clone $day)->setTime(13, 0, 0),
    ]);

    $this->actingAs($officer)
        ->patch(route('officer.reservations.approve', $resBentrok))
        ->assertRedirect()
        ->assertSessionHas('error', "Gagal menyetujui: jadwal bertumpuk dengan reservasi oleh {$userA->name} (10:00 – 12:00).");

    $errorMessage = session('error');
    expect($errorMessage)
        ->toContain($userA->name)
        ->toContain('10:00 – 12:00')
        ->not->toContain($userB->name)
        ->not->toContain($userC->name);

    expect($resBentrok->fresh()->status)->toBe('pending');

    // Happy path reservasi bersambung: approved 08:00-10:00, lalu approve pending 10:00-12:00 harus berhasil
    $facilityBersambung = Facility::factory()->create();
    Reservation::factory()->approved()->create([
        'facility_id' => $facilityBersambung->id,
        'start_time' => (clone $day)->setTime(8, 0, 0),
        'end_time' => (clone $day)->setTime(10, 0, 0),
    ]);
    $resPendingBersambung = Reservation::factory()->pending()->create([
        'facility_id' => $facilityBersambung->id,
        'start_time' => (clone $day)->setTime(10, 0, 0),
        'end_time' => (clone $day)->setTime(12, 0, 0),
    ]);

    $this->actingAs($officer)
        ->patch(route('officer.reservations.approve', $resPendingBersambung))
        ->assertRedirect(route('officer.reservations.index'))
        ->assertSessionHas('success', 'Reservasi disetujui.');

    expect($resPendingBersambung->fresh()->status)->toBe('approved');

    // Sisi batas sebaliknya: approved 10:00-12:00, approve pending bersambung sebelum jam mulai (08:00-10:00) harus berhasil
    $resPendingSebelum = Reservation::factory()->pending()->create([
        'facility_id' => $facilityBersambung->id,
        'start_time' => (clone $day)->setTime(6, 0, 0),
        'end_time' => (clone $day)->setTime(8, 0, 0),
    ]);

    $this->actingAs($officer)
        ->patch(route('officer.reservations.approve', $resPendingSebelum))
        ->assertRedirect(route('officer.reservations.index'))
        ->assertSessionHas('success', 'Reservasi disetujui.');

    expect($resPendingSebelum->fresh()->status)->toBe('approved');
});
