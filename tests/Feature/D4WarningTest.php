<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class D4WarningTest extends TestCase
{
    use DatabaseTransactions;

    public function test_d4_warning_component_renders_user_column_by_default(): void
    {
        $user = new User(['name' => 'Budi Santoso']);
        $facility = new Facility(['name' => 'Aula Utama']);

        $res = (new Reservation)->forceFill([
            'id' => 1,
            'start_time' => now()->addDays(1)->setTime(10, 0),
            'end_time' => now()->addDays(1)->setTime(12, 0),
            'purpose' => 'Seminar',
            'status' => 'approved',
        ]);
        $res->setRelation('user', $user);
        $res->setRelation('facility', $facility);

        $reservations = collect([$res]);

        $view = $this->blade('<x-d4-warning :reservations="$reservations" />', ['reservations' => $reservations]);

        $view->assertSee('Terdapat 1 reservasi yang sudah disetujui');
        $view->assertSee('Pemohon');
        $view->assertSee('Budi Santoso');
        $view->assertDontSee('Fasilitas</th>', false);
    }

    public function test_d4_warning_component_renders_facility_column_for_suspend(): void
    {
        $user = new User(['name' => 'Budi Santoso']);
        $facility = new Facility(['name' => 'Laboratorium Komputer']);

        $res = (new Reservation)->forceFill([
            'id' => 2,
            'start_time' => now()->addDays(1)->setTime(10, 0),
            'end_time' => now()->addDays(1)->setTime(12, 0),
            'purpose' => 'Praktikum',
            'status' => 'approved',
        ]);
        $res->setRelation('user', $user);
        $res->setRelation('facility', $facility);

        $reservations = collect([$res]);

        $view = $this->blade('<x-d4-warning :reservations="$reservations" column="facility" />', ['reservations' => $reservations]);

        $view->assertSee('Terdapat 1 reservasi yang sudah disetujui');
        $view->assertSee('Fasilitas');
        $view->assertSee('Laboratorium Komputer');
        $view->assertDontSee('Pemohon</th>', false);
    }

    public function test_d4_warning_renders_nothing_when_reservations_empty(): void
    {
        $view = $this->blade('<x-d4-warning :reservations="$reservations" />', ['reservations' => collect()]);

        $view->assertDontSee('alert-warning');
        $view->assertDontSee('reservasi yang sudah disetujui');
    }

    // Catatan: test_upcoming_approved_* akan diaktifkan kembali setelah PR #[Fillable]
    // dari Dhimas di-merge ke main dan method upcomingApproved ditambahkan ke Reservation.php.

    public function test_admin_facilities_index_displays_d4_warning(): void
    {
        if (! Schema::hasTable('facilities') || User::where('role', 'admin')->doesntExist()) {
            $this->markTestSkipped('Butuh database yang sudah diisi DatabaseSeeder.');
        }

        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get(route('admin.facilities.index'));

        $response->assertOk();
    }

    public function test_officer_facilities_index_displays_d4_warning(): void
    {
        if (! Schema::hasTable('facilities') || User::where('role', 'petugas')->doesntExist()) {
            $this->markTestSkipped('Butuh database yang sudah diisi DatabaseSeeder.');
        }

        $officer = User::where('role', 'petugas')->first();
        $response = $this->actingAs($officer)->get(route('officer.facilities.index'));

        $response->assertOk();
    }
}
