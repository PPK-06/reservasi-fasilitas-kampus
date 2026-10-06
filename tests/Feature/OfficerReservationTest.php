<?php

use App\Models\Facility;
use App\Models\Reservation;
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

    $start = now()->addDays(3)->setTime(10, 0, 0);
    $end = now()->addDays(3)->setTime(12, 0, 0);

    // Reservasi yang sudah approved di fasilitas dan jam tersebut
    $existing = Reservation::factory()->approved()->create([
        'facility_id' => $facility->id,
        'start_time' => $start,
        'end_time' => $end,
    ]);

    // Reservasi pending baru yang bentrok pada jam yang sama
    $resBentrok = Reservation::factory()->pending()->create([
        'facility_id' => $facility->id,
        'start_time' => $start,
        'end_time' => $end,
    ]);

    $this->actingAs($officer)
        ->patch(route('officer.reservations.approve', $resBentrok))
        ->assertRedirect()
        ->assertSessionHas('error', "Gagal menyetujui: jadwal bertumpuk dengan reservasi oleh {$existing->user->name} ({$existing->start_time->format('H:i')} – {$existing->end_time->format('H:i')}).");

    expect($resBentrok->fresh()->status)->toBe('pending');
});
