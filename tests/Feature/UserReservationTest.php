<?php

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    if (! Schema::hasTable('reservations') || User::where('role', 'pengguna')->doesntExist()) {
        $this->markTestSkipped('Butuh database yang sudah diisi DatabaseSeeder.');
    }
});

it('mengarahkan tamu ke login saat mengakses riwayat reservasi U2', function (): void {
    $this->get(route('reservations.index'))
        ->assertRedirect(route('login'));
});

it('memberi 403 untuk role selain pengguna saat mengakses U2', function (): void {
    $officer = User::where('role', 'petugas')->firstOrFail();
    $this->actingAs($officer)
        ->get(route('reservations.index'))
        ->assertForbidden();
});

it('pengguna dapat melihat riwayat reservasinya di U2', function (): void {
    $user = User::where('role', 'pengguna')->where('status', 'verified')->firstOrFail();
    $this->actingAs($user)
        ->get(route('reservations.index'))
        ->assertOk()
        ->assertViewIs('reservations.index')
        ->assertSee('Riwayat Reservasi');
});

it('mengarahkan tamu ke login saat mengakses detail atau batalkan reservasi U3', function (): void {
    $res = Reservation::firstOrFail();
    $this->get(route('reservations.show', $res))
        ->assertRedirect(route('login'));
    $this->patch(route('reservations.cancel', $res))
        ->assertRedirect(route('login'));
});

it('melarang pengguna lain melihat reservasi yang bukan miliknya di U3', function (): void {
    $userA = User::where('role', 'pengguna')->firstOrFail();
    $userB = User::where('role', 'pengguna')->where('id', '!=', $userA->id)->firstOrFail();

    $res = new Reservation([
        'facility_id' => Facility::where('status', 'active')->firstOrFail()->id,
        'purpose' => 'Seminar Komputasi',
    ]);
    $res->user_id = $userA->id;
    $res->start_time = now()->addDays(2)->setTime(9, 0);
    $res->end_time = now()->addDays(2)->setTime(11, 0);
    $res->status = 'pending';
    $res->save();

    $this->actingAs($userB)
        ->get(route('reservations.show', $res))
        ->assertForbidden();
});

it('pemilik reservasi dapat melihat detail reservasinya di U3', function (): void {
    $user = User::where('role', 'pengguna')->where('status', 'verified')->firstOrFail();
    $res = new Reservation([
        'facility_id' => Facility::where('status', 'active')->firstOrFail()->id,
        'purpose' => 'Diskusi Proyek',
    ]);
    $res->user_id = $user->id;
    $res->start_time = now()->addDays(2)->setTime(13, 0);
    $res->end_time = now()->addDays(2)->setTime(15, 0);
    $res->status = 'pending';
    $res->save();

    $this->actingAs($user)
        ->get(route('reservations.show', $res))
        ->assertOk()
        ->assertViewIs('reservations.show')
        ->assertSee('Diskusi Proyek');
});

it('pemilik dapat membatalkan reservasi pending miliknya di U3', function (): void {
    $user = User::where('role', 'pengguna')->where('status', 'verified')->firstOrFail();
    $res = new Reservation([
        'facility_id' => Facility::where('status', 'active')->firstOrFail()->id,
        'purpose' => 'Rapat Koordinasi Batal',
    ]);
    $res->user_id = $user->id;
    $res->start_time = now()->addDays(3)->setTime(10, 0);
    $res->end_time = now()->addDays(3)->setTime(12, 0);
    $res->status = 'pending';
    $res->save();

    $this->actingAs($user)
        ->patch(route('reservations.cancel', $res), [
            'status_reason' => 'Ada jadwal kuliah pengganti',
        ])
        ->assertRedirect(route('reservations.show', $res));

    expect($res->fresh()->status)->toBe('cancelled_by_user');
});
