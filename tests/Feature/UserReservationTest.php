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

it('mengarahkan tamu ke login saat mengakses riwayat reservasi U2', function (): void {
    $this->get(route('reservations.index'))
        ->assertRedirect(route('login'));
});

it('memberi 403 untuk role selain pengguna saat mengakses U2', function (): void {
    $officer = buatAkunDenganRole('petugas');
    $this->actingAs($officer)
        ->get(route('reservations.index'))
        ->assertForbidden();
});

it('pengguna dapat melihat riwayat reservasinya di U2', function (): void {
    $user = buatAkunDenganRole('pengguna');
    $this->actingAs($user)
        ->get(route('reservations.index'))
        ->assertOk()
        ->assertViewIs('reservations.index')
        ->assertSee('Riwayat Reservasi');
});

it('mengarahkan tamu ke login saat mengakses detail atau batalkan reservasi U3', function (): void {
    $res = Reservation::factory()->create();
    $this->get(route('reservations.show', $res))
        ->assertRedirect(route('login'));
    $this->patch(route('reservations.cancel', $res))
        ->assertRedirect(route('login'));
});

it('melarang pengguna lain melihat reservasi yang bukan miliknya di U3', function (): void {
    $userA = buatAkunDenganRole('pengguna');
    $userB = buatAkunDenganRole('pengguna');

    $res = Reservation::factory()->for($userA)->create([
        'purpose' => 'Seminar Komputasi',
    ]);

    $this->actingAs($userB)
        ->get(route('reservations.show', $res))
        ->assertForbidden();
});

it('pemilik reservasi dapat melihat detail reservasinya di U3', function (): void {
    $user = buatAkunDenganRole('pengguna');
    $res = Reservation::factory()->for($user)->create([
        'purpose' => 'Diskusi Proyek',
    ]);

    $this->actingAs($user)
        ->get(route('reservations.show', $res))
        ->assertOk()
        ->assertViewIs('reservations.show')
        ->assertSee('Diskusi Proyek');
});

it('pemilik dapat membatalkan reservasi pending miliknya di U3', function (): void {
    $user = buatAkunDenganRole('pengguna');
    $res = Reservation::factory()->for($user)->pending()->create([
        'purpose' => 'Rapat Koordinasi Batal',
    ]);

    $this->actingAs($user)
        ->patch(route('reservations.cancel', $res), [
            'status_reason' => 'Ada jadwal kuliah pengganti',
        ])
        ->assertRedirect(route('reservations.show', $res));

    expect($res->fresh()->status)->toBe('cancelled_by_user');
});

it('melarang pengguna lain membatalkan reservasi yang bukan miliknya di U3', function (): void {
    $userA = buatAkunDenganRole('pengguna');
    $userB = buatAkunDenganRole('pengguna');

    $res = Reservation::factory()->for($userA)->pending()->create();

    $this->actingAs($userB)
        ->patch(route('reservations.cancel', $res), [
            'status_reason' => 'Mencoba batalkan milik orang lain',
        ])
        ->assertForbidden();

    expect($res->fresh()->status)->toBe('pending');
});

it('menolak pengajuan reservasi dengan tujuan kosong dan mempertahankan input lama', function (): void {
    $user = buatAkunDenganRole('pengguna');
    $facility = Facility::factory()->create();
    $targetDate = now()->addDays(2)->toDateString();

    $this->actingAs($user)
        ->from(route('facilities.show', ['facility' => $facility->id, 'date' => $targetDate]))
        ->post(route('reservations.store'), [
            'facility_id' => $facility->id,
            'date' => $targetDate,
            'start_slot' => '08:00',
            'end_slot' => '10:00',
            'purpose' => '',
        ])
        ->assertRedirect(route('facilities.show', ['facility' => $facility->id, 'date' => $targetDate]))
        ->assertSessionHasErrors(['purpose' => 'Tujuan penggunaan wajib diisi.'])
        ->assertSessionDoesntHaveErrors(['facility_id', 'date', 'start_slot', 'end_slot'])
        ->assertSessionHasInput('facility_id', $facility->id)
        ->assertSessionHasInput('date', $targetDate)
        ->assertSessionHasInput('start_slot', '08:00')
        ->assertSessionHasInput('end_slot', '10:00');

    $this->assertDatabaseMissing('reservations', ['facility_id' => $facility->id]);
});
