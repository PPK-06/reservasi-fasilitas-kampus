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

it('O5 menampilkan form tandai dalam perbaikan untuk fasilitas aktif', function (): void {
    $petugas = buatAkunDenganRole('petugas');
    $pengguna = buatAkunDenganRole('pengguna');

    $facility = Facility::create([
        'name' => 'Ruang Test Active O5',
        'type' => 'Ruang Kelas',
        'location' => 'Gedung A',
        'capacity' => 30,
        'description' => 'Fasilitas aktif untuk pengujian aksi status pada O5.',
        'status' => 'active',
    ]);

    $report = new Report([
        'facility_id' => $facility->id,
        'category' => 'kerusakan_alat',
        'description' => 'Proyektor tidak menyala dan perlu ditindaklanjuti petugas.',
        'status' => 'baru',
    ]);

    $report->user_id = $pengguna->id;
    $report->save();

    $response = $this->actingAs($petugas)
        ->get(route('officer.reports.show', $report));

    $response->assertOk()
        ->assertSeeText('Tandai Dalam Perbaikan');

    $document = new DOMDocument();
    $document->loadHTML($response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);

    $xpath = new DOMXPath($document);
    $form = $xpath->query(
        '//form[@action="'.route('officer.facilities.status', $facility).'"]'
    )->item(0);

    expect($form)->not->toBeNull();
    expect(strtoupper($form->getAttribute('method')))->toBe('POST')
        ->and($xpath->evaluate('string(.//input[@name="_method"]/@value)', $form))->toBe('PATCH')
        ->and($xpath->evaluate('string(.//input[@name="status"]/@value)', $form))->toBe('under_maintenance')
        ->and($xpath->evaluate('normalize-space(.//button[@type="submit"])', $form))->toBe('Tandai Dalam Perbaikan');
});

it('O5 menampilkan label Nonaktif untuk fasilitas nonaktif', function (): void {
    $petugas = buatAkunDenganRole('petugas');
    $pengguna = buatAkunDenganRole('pengguna');

    $facility = Facility::create([
        'name' => 'Ruang Test Inactive O5',
        'type' => 'Ruang Kelas',
        'location' => 'Gedung A',
        'capacity' => 30,
        'description' => 'Fasilitas nonaktif untuk pengujian label status pada O5.',
        'status' => 'inactive',
    ]);

    $report = new Report([
        'facility_id' => $facility->id,
        'category' => 'kerusakan_alat',
        'description' => 'Laporan kerusakan pada fasilitas yang sudah dinonaktifkan.',
        'status' => 'baru',
    ]);

    $report->user_id = $pengguna->id;
    $report->save();

    $this->actingAs($petugas)
        ->get(route('officer.reports.show', $report))
        ->assertOk()
        ->assertViewIs('officer.reports.show')
        ->assertSeeText('Nonaktif')
        ->assertDontSeeText('inactive');
});
