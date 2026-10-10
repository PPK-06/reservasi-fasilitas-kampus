<?php

use App\Models\Facility;
use App\Models\Report;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    if (
        ! Schema::hasTable('users') ||
        ! Schema::hasTable('facilities') ||
        ! Schema::hasTable('reservations') ||
        ! Schema::hasTable('reports')
    ) {
        $this->markTestSkipped('Database project belum siap.');
    }

    $this->admin = buatAkunDenganRole('admin');
    $this->pengguna = buatAkunDenganRole('pengguna');

    $this->facility = Facility::create([
        'name' => 'Ruang Rekap Test',
        'type' => 'Ruang Kelas',
        'location' => 'Gedung A',
        'capacity' => 40,
        'description' => 'Fasilitas untuk pengujian rekap.',
        'status' => 'active',
    ]);
});

it('mengarahkan tamu ke login ketika membuka A6', function (): void {
    $this->get(route('admin.recap.index'))
        ->assertRedirect(route('login'));
});

it('A6 menolak pengguna yang bukan admin', function (): void {
    $this->actingAs($this->pengguna)
        ->get(route('admin.recap.index'))
        ->assertForbidden();
});

it('A6 admin dapat membuka halaman rekap', function (): void {
    $this->actingAs($this->admin)
        ->get(route('admin.recap.index'))
        ->assertOk();
});

it('A6 menghitung rekap untuk rentang tanggal yang dipilih', function (): void {
    // Data sebelum dan sesudah periode menjadi kontrol untuk kedua batas tanggal.
    foreach (['2026-09-30', '2026-10-01', '2026-10-02'] as $date) {
        $reservation = new Reservation();

        $reservation->facility_id = $this->facility->id;
        $reservation->user_id = $this->pengguna->id;
        $reservation->start_time = $date.' 08:00:00';
        $reservation->end_time = $date.' 10:00:00';
        $reservation->purpose =
            'Reservasi untuk pengujian rekap okupansi.';
        $reservation->status = 'approved';
        $reservation->save();

        $report = new Report([
            'facility_id' => $this->facility->id,
            'category' => 'kerusakan_alat',
            'description' =>
                'Laporan kerusakan untuk pengujian rekap admin.',
            'status' => 'baru',
        ]);

        $report->user_id = $this->pengguna->id;
        $report->created_at = $date.' 09:00:00';
        $report->updated_at = $date.' 09:00:00';
        $report->save();
    }

    $this->actingAs($this->admin)
        ->get(route('admin.recap.index', [
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-01',
        ]))
        ->assertOk()
        ->assertViewHas('occupancyRows', function (Collection $rows): bool {
            $row = $rows->firstWhere('facility_id', $this->facility->id);

            expect($row)->not->toBeNull();
            expect($row->reservation_count)->toBe(1)
                ->and($row->used_slots)->toBe(4.0);

            return true;
        })
        ->assertViewHas('damageRows', function (Collection $rows): bool {
            $row = $rows->firstWhere('facility_id', $this->facility->id);

            expect($row)->not->toBeNull();
            expect($row->report_count)->toBe(1);

            return true;
        });
});

it('A6 admin dapat export CSV menggunakan filter tanggal', function (): void {
    $response = $this
        ->actingAs($this->admin)
        ->get(route('admin.recap.export', [
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-01',
        ]));

    $response->assertOk();

    expect($response->headers->get('content-disposition'))
        ->toContain('attachment');

    expect($response->headers->get('content-type'))
        ->toContain('text/csv');
});

it('A6 validasi menolak tanggal mulai lebih besar dari tanggal selesai', function (): void {
    $this->actingAs($this->admin)
        ->from(route('admin.recap.index'))
        ->get(route('admin.recap.index', [
            'start_date' => '2026-10-15',
            'end_date' => '2026-10-10',
        ]))
        ->assertRedirect(route('admin.recap.index'))
        ->assertSessionHasErrors('end_date');
});
