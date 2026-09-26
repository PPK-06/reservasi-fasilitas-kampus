<?php

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use App\Support\Slot;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    if (! Schema::hasTable('facilities') || Facility::count() === 0) {
        $this->markTestSkipped('Butuh database yang sudah diisi DatabaseSeeder.');
    }
});

it('mengembalikan tepat 26 slot untuk fasilitas dan tanggal yang sah', function (): void {
    $facility = Facility::where('status', 'active')->firstOrFail();
    $targetDate = Carbon::now()->addDays(2)->toDateString();

    $slots = Slot::availability($facility, $targetDate);

    expect($slots)->toBeArray()
        ->and(count($slots))->toBe(26)
        ->and(array_keys($slots))->toBe(Slot::startTimes());

    $firstSlot = $slots['07:00'];
    expect($firstSlot)->toHaveKeys(['start', 'end', 'status', 'is_available', 'is_booked', 'is_past_limit'])
        ->and($firstSlot['start'])->toBe('07:00')
        ->and($firstSlot['end'])->toBe('07:30');
});

it('mengisi slot dengan benar sesuai batas jam reservasi approved dan menyisakan batas akhir kosong', function (): void {
    $facility = Facility::where('status', 'active')->firstOrFail();
    $user = User::where('role', 'pengguna')->firstOrFail();
    $targetDate = Carbon::now()->addDays(5)->toDateString();

    // Buat reservasi approved: 08:00 - 10:00 (4 slot: 08:00, 08:30, 09:00, 09:30)
    $res = new Reservation([
        'facility_id' => $facility->id,
        'start_time' => "{$targetDate} 08:00:00",
        'end_time' => "{$targetDate} 10:00:00",
        'purpose' => 'Kegiatan seminar uji coba ketersediaan slot',
    ]);
    $res->user_id = $user->id;
    $res->status = 'approved';
    $res->save();

    $slots = Slot::availability($facility, $targetDate);

    // Slot sebelum 08:00 harus available
    expect($slots['07:30']['status'])->toBe('available')
        ->and($slots['07:30']['is_available'])->toBeTrue();

    // Slot 08:00 sampai 09:30 harus booked
    expect($slots['08:00']['status'])->toBe('booked')
        ->and($slots['08:00']['is_booked'])->toBeTrue()
        ->and($slots['08:30']['status'])->toBe('booked')
        ->and($slots['09:00']['status'])->toBe('booked')
        ->and($slots['09:30']['status'])->toBe('booked');

    // Slot 10:00 harus tetap available (aturan batas slot F1/US 1)
    expect($slots['10:00']['status'])->toBe('available')
        ->and($slots['10:00']['is_available'])->toBeTrue()
        ->and($slots['10:00']['is_booked'])->toBeFalse();
});

it('hanya memperhitungkan reservasi berstatus approved dan mengabaikan status pending atau lainnya', function (): void {
    $facility = Facility::where('status', 'active')->firstOrFail();
    $user = User::where('role', 'pengguna')->firstOrFail();
    $targetDate = Carbon::now()->addDays(6)->toDateString();

    // Pending tidak boleh membuat slot terisi
    $resPending = new Reservation([
        'facility_id' => $facility->id,
        'start_time' => "{$targetDate} 11:00:00",
        'end_time' => "{$targetDate} 13:00:00",
        'purpose' => 'Uji pending slot',
    ]);
    $resPending->user_id = $user->id;
    $resPending->status = 'pending';
    $resPending->save();

    // Cancelled juga tidak boleh membuat slot terisi
    $resCancelled = new Reservation([
        'facility_id' => $facility->id,
        'start_time' => "{$targetDate} 14:00:00",
        'end_time' => "{$targetDate} 16:00:00",
        'purpose' => 'Uji cancelled slot',
    ]);
    $resCancelled->user_id = $user->id;
    $resCancelled->status = 'cancelled_by_user';
    $resCancelled->save();

    $slots = Slot::availability($facility, $targetDate);

    expect($slots['11:00']['status'])->toBe('available')
        ->and($slots['11:30']['status'])->toBe('available')
        ->and($slots['14:00']['status'])->toBe('available');
});

it('mematuhi privasi F4 dengan hanya men-select facility_id, start_time, dan end_time', function (): void {
    $facility = Facility::where('status', 'active')->firstOrFail();
    $targetDate = Carbon::now()->addDays(3)->toDateString();

    DB::enableQueryLog();
    Slot::availability($facility, $targetDate);
    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    $reservationQuery = collect($queries)->first(function ($q) {
        return str_contains($q['query'], 'reservations');
    });

    expect($reservationQuery)->not->toBeNull();
    $sql = strtolower($reservationQuery['query']);

    expect($sql)->toContain('facility_id')
        ->and($sql)->toContain('start_time')
        ->and($sql)->toContain('end_time')
        ->and($sql)->not->toContain('purpose')
        ->and($sql)->not->toContain('user_id');
});

it('memberikan status visual past_limit untuk tanggal yang sudah lewat batas A3', function (): void {
    $facility = Facility::where('status', 'active')->firstOrFail();
    $yesterday = Carbon::now()->subDay()->toDateString();

    $slots = Slot::availability($facility, $yesterday);

    expect($slots['08:00']['status'])->toBe('past_limit')
        ->and($slots['08:00']['is_past_limit'])->toBeTrue()
        ->and($slots['08:00']['is_available'])->toBeFalse();
});

it('endpoint reservations.availability mengembalikan format JSON ketersediaan', function (): void {
    $user = User::where('role', 'pengguna')->where('status', 'verified')->firstOrFail();
    $facility = Facility::where('status', 'active')->firstOrFail();
    $targetDate = Carbon::now()->addDays(4)->toDateString();

    $response = $this->actingAs($user)->getJson(route('reservations.availability', [
        'facility_id' => $facility->id,
        'date' => $targetDate,
    ]));

    $response->assertOk()
        ->assertJsonStructure([
            'facility_id',
            'date',
            'slots' => [
                '07:00' => ['start', 'end', 'status', 'is_available', 'is_booked', 'is_past_limit'],
            ],
        ]);
});

it('halaman detail fasilitas P2 menampilkan grid ketersediaan 26 slot dengan outline biru dan abu-abu', function (): void {
    $facility = Facility::where('status', 'active')->firstOrFail();
    $targetDate = Carbon::now()->addDays(7)->toDateString();

    $response = $this->get(route('facilities.show', [
        'facility' => $facility->id,
        'date' => $targetDate,
    ]));

    $response->assertOk()
        ->assertSee('Ketersediaan Jadwal')
        ->assertSee('btn-outline-primary')
        ->assertDontSee('Grid ketersediaan akan segera tersedia');
});
