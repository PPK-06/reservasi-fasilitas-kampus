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

it('renders user column by default in d4 warning component', function (): void {
    $user = User::factory()->create(['name' => 'Budi Santoso']);
    $facility = new Facility;
    $facility->name = 'Aula Utama';
    $facility->type = 'Aula';
    $facility->location = 'Gedung Serba Guna';
    $facility->capacity = 500;
    $facility->status = 'active';
    $facility->save();

    $res = new Reservation;
    $res->user_id = $user->id;
    $res->facility_id = $facility->id;
    $res->start_time = now()->addDays(1)->setTime(10, 0);
    $res->end_time = now()->addDays(1)->setTime(12, 0);
    $res->purpose = 'Seminar';
    $res->status = 'approved';
    $res->save();

    $res->load('user', 'facility');
    $reservations = collect([$res]);

    $view = $this->blade('<x-d4-warning :reservations="$reservations" />', ['reservations' => $reservations]);

    $view->assertSee('Terdapat 1 reservasi yang sudah disetujui');
    $view->assertSee('Pemohon');
    $view->assertSee('Budi Santoso');
    $view->assertDontSee('Fasilitas</th>', false);
});

it('renders facility column when column attribute is set to facility', function (): void {
    $user = User::factory()->create(['name' => 'Budi Santoso']);
    $facility = new Facility;
    $facility->name = 'Laboratorium Komputer';
    $facility->type = 'Laboratorium';
    $facility->location = 'Gedung B';
    $facility->capacity = 35;
    $facility->status = 'active';
    $facility->save();

    $res = new Reservation;
    $res->user_id = $user->id;
    $res->facility_id = $facility->id;
    $res->start_time = now()->addDays(1)->setTime(10, 0);
    $res->end_time = now()->addDays(1)->setTime(12, 0);
    $res->purpose = 'Praktikum';
    $res->status = 'approved';
    $res->save();

    $res->load('user', 'facility');
    $reservations = collect([$res]);

    $view = $this->blade('<x-d4-warning :reservations="$reservations" column="facility" />', ['reservations' => $reservations]);

    $view->assertSee('Terdapat 1 reservasi yang sudah disetujui');
    $view->assertSee('Fasilitas');
    $view->assertSee('Laboratorium Komputer');
    $view->assertDontSee('Pemohon</th>', false);
});

it('renders nothing when reservations is empty', function (): void {
    $view = $this->blade('<x-d4-warning :reservations="$reservations" />', ['reservations' => collect()]);

    $view->assertDontSee('alert-warning');
    $view->assertDontSee('reservasi yang sudah disetujui');
});

it('only shows link to O3 for officer role and hides it for admin', function (): void {
    $user = User::factory()->create(['name' => 'Budi Santoso']);
    $admin = User::factory()->admin()->create();
    $officer = User::factory()->petugas()->create();

    $facility = new Facility;
    $facility->name = 'Aula Utama';
    $facility->type = 'Aula';
    $facility->location = 'Gedung Serba Guna';
    $facility->capacity = 500;
    $facility->status = 'active';
    $facility->save();

    $res = new Reservation;
    $res->user_id = $user->id;
    $res->facility_id = $facility->id;
    $res->start_time = now()->addDays(1)->setTime(10, 0);
    $res->end_time = now()->addDays(1)->setTime(12, 0);
    $res->purpose = 'Seminar';
    $res->status = 'approved';
    $res->save();

    $res->load('user', 'facility');
    $reservations = collect([$res]);
    $o3Url = route('officer.reservations.show', $res);

    // Saat admin login, tautan ke O3 tidak ditampilkan
    $this->actingAs($admin);
    $viewAdmin = $this->blade('<x-d4-warning :reservations="$reservations" />', ['reservations' => $reservations]);
    $viewAdmin->assertDontSee($o3Url, false);

    // Saat petugas login, tautan ke O3 ditampilkan
    $this->actingAs($officer);
    $viewOfficer = $this->blade('<x-d4-warning :reservations="$reservations" />', ['reservations' => $reservations]);
    $viewOfficer->assertSee($o3Url, false);
});

it('filters upcoming approved reservations by facility model and int ID', function (): void {
    $user = User::factory()->create();
    $facility1 = new Facility;
    $facility1->name = 'Fasilitas 1';
    $facility1->type = 'Ruang Kelas';
    $facility1->location = 'Gedung A';
    $facility1->capacity = 30;
    $facility1->status = 'active';
    $facility1->save();

    $facility2 = new Facility;
    $facility2->name = 'Fasilitas 2';
    $facility2->type = 'Ruang Kelas';
    $facility2->location = 'Gedung A';
    $facility2->capacity = 30;
    $facility2->status = 'active';
    $facility2->save();

    $res1 = new Reservation;
    $res1->user_id = $user->id;
    $res1->facility_id = $facility1->id;
    $res1->start_time = now()->addDays(1)->setTime(9, 0);
    $res1->end_time = now()->addDays(1)->setTime(11, 0);
    $res1->purpose = 'Kegiatan 1';
    $res1->status = 'approved';
    $res1->save();

    $res2 = new Reservation;
    $res2->user_id = $user->id;
    $res2->facility_id = $facility2->id;
    $res2->start_time = now()->addDays(2)->setTime(9, 0);
    $res2->end_time = now()->addDays(2)->setTime(11, 0);
    $res2->purpose = 'Kegiatan 2';
    $res2->status = 'approved';
    $res2->save();

    // Filter per Facility model
    $byModel = Reservation::upcomingApproved($facility1);
    expect($byModel->has($facility1->id))->toBeTrue();
    expect($byModel->has($facility2->id))->toBeFalse();
    expect($byModel->get($facility1->id)->pluck('id')->all())->toContain($res1->id);

    // Filter per int ID
    $byId = Reservation::upcomingApproved($facility2->id);
    expect($byId->has($facility2->id))->toBeTrue();
    expect($byId->has($facility1->id))->toBeFalse();
    expect($byId->get($facility2->id)->pluck('id')->all())->toContain($res2->id);
});

it('filters upcoming approved reservations by user model and int ID and groups by facility', function (): void {
    $user1 = User::factory()->create(['name' => 'User Pertama']);
    $user2 = User::factory()->create(['name' => 'User Kedua']);

    $facility1 = new Facility;
    $facility1->name = 'Fasilitas A';
    $facility1->type = 'Ruang Kelas';
    $facility1->location = 'Gedung A';
    $facility1->capacity = 30;
    $facility1->status = 'active';
    $facility1->save();

    $facility2 = new Facility;
    $facility2->name = 'Fasilitas B';
    $facility2->type = 'Ruang Kelas';
    $facility2->location = 'Gedung B';
    $facility2->capacity = 30;
    $facility2->status = 'active';
    $facility2->save();

    // Reservasi user 1 di facility 1
    $res1 = new Reservation;
    $res1->user_id = $user1->id;
    $res1->facility_id = $facility1->id;
    $res1->start_time = now()->addDays(1)->setTime(8, 0);
    $res1->end_time = now()->addDays(1)->setTime(10, 0);
    $res1->purpose = 'Kegiatan User 1 Fac 1';
    $res1->status = 'approved';
    $res1->save();

    // Reservasi user 1 di facility 2
    $res2 = new Reservation;
    $res2->user_id = $user1->id;
    $res2->facility_id = $facility2->id;
    $res2->start_time = now()->addDays(2)->setTime(8, 0);
    $res2->end_time = now()->addDays(2)->setTime(10, 0);
    $res2->purpose = 'Kegiatan User 1 Fac 2';
    $res2->status = 'approved';
    $res2->save();

    // Reservasi user 2 di facility 1
    $res3 = new Reservation;
    $res3->user_id = $user2->id;
    $res3->facility_id = $facility1->id;
    $res3->start_time = now()->addDays(3)->setTime(8, 0);
    $res3->end_time = now()->addDays(3)->setTime(10, 0);
    $res3->purpose = 'Kegiatan User 2 Fac 1';
    $res3->status = 'approved';
    $res3->save();

    // Filter per User model: harus berisi res1 dan res2, dikelompokkan per facility_id
    $byUserModel = Reservation::upcomingApproved(null, $user1);
    expect($byUserModel->has($facility1->id))->toBeTrue();
    expect($byUserModel->has($facility2->id))->toBeTrue();
    expect($byUserModel->get($facility1->id)->pluck('id')->all())->toContain($res1->id);
    expect($byUserModel->get($facility2->id)->pluck('id')->all())->toContain($res2->id);
    expect($byUserModel->get($facility1->id)->pluck('id')->all())->not->toContain($res3->id);

    // Filter per User int ID
    $byUserId = Reservation::upcomingApproved(null, $user2->id);
    expect($byUserId->has($facility1->id))->toBeTrue();
    expect($byUserId->has($facility2->id))->toBeFalse();
    expect($byUserId->get($facility1->id)->pluck('id')->all())->toContain($res3->id);
    expect($byUserId->get($facility1->id)->pluck('id')->all())->not->toContain($res1->id);
});

it('excludes reservations with non-approved status in upcomingApproved', function (): void {
    $user = User::factory()->create();
    $facility = new Facility;
    $facility->name = 'Fasilitas Status Test';
    $facility->type = 'Ruang Kelas';
    $facility->location = 'Gedung A';
    $facility->capacity = 30;
    $facility->status = 'active';
    $facility->save();

    $statuses = ['pending', 'rejected', 'cancelled_by_user', 'cancelled_by_officer'];
    $createdIds = [];

    foreach ($statuses as $idx => $st) {
        $res = new Reservation;
        $res->user_id = $user->id;
        $res->facility_id = $facility->id;
        $res->start_time = now()->addDays($idx + 1)->setTime(8, 0);
        $res->end_time = now()->addDays($idx + 1)->setTime(10, 0);
        $res->purpose = 'Test status '.$st;
        $res->status = $st;
        $res->save();
        $createdIds[] = $res->id;
    }

    $results = Reservation::upcomingApproved($facility);
    $foundIds = $results->get($facility->id, collect())->pluck('id')->all();

    foreach ($createdIds as $id) {
        expect($foundIds)->not->toContain($id);
    }
});

it('excludes reservations where start_time is in the past in upcomingApproved', function (): void {
    $user = User::factory()->create();
    $facility = new Facility;
    $facility->name = 'Fasilitas Waktu Test';
    $facility->type = 'Ruang Kelas';
    $facility->location = 'Gedung A';
    $facility->capacity = 30;
    $facility->status = 'active';
    $facility->save();

    // Reservasi di masa lalu tapi approved
    $pastRes = new Reservation;
    $pastRes->user_id = $user->id;
    $pastRes->facility_id = $facility->id;
    $pastRes->start_time = now()->subDays(2)->setTime(8, 0);
    $pastRes->end_time = now()->subDays(2)->setTime(10, 0);
    $pastRes->purpose = 'Reservasi yang sudah lewat';
    $pastRes->status = 'approved';
    $pastRes->save();

    // Reservasi di masa depan dan approved
    $futureRes = new Reservation;
    $futureRes->user_id = $user->id;
    $futureRes->facility_id = $facility->id;
    $futureRes->start_time = now()->addDays(2)->setTime(8, 0);
    $futureRes->end_time = now()->addDays(2)->setTime(10, 0);
    $futureRes->purpose = 'Reservasi masa depan';
    $futureRes->status = 'approved';
    $futureRes->save();

    $results = Reservation::upcomingApproved($facility);
    $foundIds = $results->get($facility->id, collect())->pluck('id')->all();

    expect($foundIds)->not->toContain($pastRes->id);
    expect($foundIds)->toContain($futureRes->id);
});

it('displays d4 warning on admin facilities index page with factory data', function (): void {
    $admin = User::factory()->admin()->create();

    $facility = new Facility;
    $facility->name = 'Gedung Pertemuan Khusus Admin';
    $facility->type = 'Aula';
    $facility->location = 'Gedung Serba Guna';
    $facility->capacity = 200;
    $facility->status = 'active';
    $facility->save();

    $pemohon = User::factory()->create(['name' => 'Siti Aminah']);

    $res = new Reservation;
    $res->user_id = $pemohon->id;
    $res->facility_id = $facility->id;
    $res->start_time = now()->addDays(5)->setTime(13, 0);
    $res->end_time = now()->addDays(5)->setTime(15, 0);
    $res->purpose = 'Pelantikan Pengurus';
    $res->status = 'approved';
    $res->save();

    $response = $this->actingAs($admin)->get(route('admin.facilities.index'));

    $response->assertOk();
    $response->assertSee('Terdapat 1 reservasi yang sudah disetujui');
    $response->assertSee('Siti Aminah');
    $response->assertSee('Gedung Pertemuan Khusus Admin');
});

it('displays d4 warning on officer facilities index page with factory data', function (): void {
    $officer = User::factory()->petugas()->create();

    $facility = new Facility;
    $facility->name = 'Laboratorium Riset Khusus Petugas';
    $facility->type = 'Laboratorium';
    $facility->location = 'Gedung B';
    $facility->capacity = 40;
    $facility->status = 'active';
    $facility->save();

    $pemohon = User::factory()->create(['name' => 'Ahmad Dahlan']);

    $res = new Reservation;
    $res->user_id = $pemohon->id;
    $res->facility_id = $facility->id;
    $res->start_time = now()->addDays(4)->setTime(9, 0);
    $res->end_time = now()->addDays(4)->setTime(11, 0);
    $res->purpose = 'Riset Kolaborasi';
    $res->status = 'approved';
    $res->save();

    $response = $this->actingAs($officer)->get(route('officer.facilities.index'));

    $response->assertOk();
    $response->assertSee('Terdapat 1 reservasi yang sudah disetujui');
    $response->assertSee('Ahmad Dahlan');
    $response->assertSee('Laboratorium Riset Khusus Petugas');
});
