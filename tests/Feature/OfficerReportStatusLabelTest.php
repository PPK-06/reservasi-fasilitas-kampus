<?php

use App\Models\Facility;
use App\Models\Report;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('O5 menampilkan label status fasilitas dalam perbaikan', function (): void {
    $petugas = buatAkunDenganRole('petugas');
    $pengguna = buatAkunDenganRole('pengguna');

    $facility = Facility::create([
        'name' => 'Ruang Test Label O5',
        'type' => 'Ruang Kelas',
        'location' => 'Gedung A',
        'capacity' => 30,
        'description' => 'Fasilitas untuk pengujian label status pada O5.',
        'status' => 'under_maintenance',
    ]);

    $report = new Report([
        'facility_id' => $facility->id,
        'category' => 'kerusakan_alat',
        'description' => 'Proyektor tidak menyala dan fasilitas sedang dalam perbaikan.',
        'status' => 'baru',
    ]);

    $report->user_id = $pengguna->id;
    $report->save();

    $this->actingAs($petugas)
        ->get(route('officer.reports.show', $report))
        ->assertOk()
        ->assertViewIs('officer.reports.show')
        ->assertSeeText('Dalam Perbaikan')
        ->assertDontSeeText('under_maintenance');
});
