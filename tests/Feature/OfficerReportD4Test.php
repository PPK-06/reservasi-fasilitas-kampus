<?php

use App\Models\Facility;
use App\Models\Report;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;

uses(DatabaseTransactions::class);

it('O5 menampilkan reservasi approved mendatang dalam modal D4 sebelum perubahan status', function (): void {
    $this->travelTo(Carbon::parse('2030-04-08 08:00:00'));

    $petugas = buatAkunDenganRole('petugas');
    $pelapor = buatAkunDenganRole('pengguna');
    $pemohon = buatAkunDenganRole('pengguna');
    $facility = Facility::factory()->create();

    $report = new Report([
        'facility_id' => $facility->id,
        'category' => 'kerusakan_alat',
        'description' => 'Proyektor rusak dan fasilitas perlu ditandai dalam perbaikan.',
        'status' => 'baru',
    ]);
    $report->user_id = $pelapor->id;
    $report->save();

    $reservation = Reservation::factory()
        ->approved()
        ->for($facility)
        ->for($pemohon)
        ->create();

    $response = $this->actingAs($petugas)
        ->get(route('officer.reports.show', $report));

    $response->assertOk();

    $document = new DOMDocument;
    $document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
    $xpath = new DOMXPath($document);
    $modalId = 'statusModal-'.$facility->id;
    $modal = $xpath->query('//div[@id="'.$modalId.'"]')->item(0);

    expect($modal)->not->toBeNull();

    $trigger = $xpath->query(
        '//button[@type="button" and @data-bs-toggle="modal" and @data-bs-target="#'.$modalId.'"]'
    )->item(0);

    expect($trigger)->not->toBeNull();
    expect($xpath->query('ancestor::form', $trigger)->length)->toBe(0);
    expect($xpath->evaluate('normalize-space(.)', $trigger))->toBe('Tandai Dalam Perbaikan');

    $warning = $xpath->query(
        './/div[contains(concat(" ", normalize-space(@class), " "), " alert-warning ")][.//table]',
        $modal
    )->item(0);

    expect($warning)->not->toBeNull();
    expect($warning->textContent)->toContain('reservasi yang sudah disetujui');
    expect($xpath->evaluate('normalize-space(.//thead/tr/th[3])', $warning))->toBe('Pemohon');

    $reservationRow = $xpath->query(
        './/tbody/tr[.//a[@href="'.route('officer.reservations.show', $reservation).'"]]',
        $warning
    )->item(0);

    expect($reservationRow)->not->toBeNull();
    expect($xpath->evaluate('normalize-space(./td[1])', $reservationRow))->toBe('10 Apr 2030');
    expect($xpath->evaluate('normalize-space(./td[2])', $reservationRow))->toContain('09:00', '11:00');
    expect($xpath->evaluate('normalize-space(./td[3])', $reservationRow))->toBe($pemohon->name);

    $statusForm = $xpath->query(
        './/div[contains(concat(" ", normalize-space(@class), " "), " modal-footer ")]//form[@action="'.
        route('officer.facilities.status', $facility).'"]',
        $modal
    )->item(0);

    expect($statusForm)->not->toBeNull();
    expect(strtoupper($statusForm->getAttribute('method')))->toBe('POST');
    expect($xpath->evaluate('string(.//input[@name="_method"]/@value)', $statusForm))->toBe('PATCH');
    expect($xpath->evaluate('string(.//input[@name="status"]/@value)', $statusForm))->toBe('under_maintenance');
    expect($xpath->evaluate('normalize-space(.//button[@type="submit"])', $statusForm))->toBe('Tandai Dalam Perbaikan');

    $this->assertDatabaseHas('reservations', [
        'id' => $reservation->id,
        'status' => 'approved',
    ]);
    $this->assertDatabaseHas('facilities', [
        'id' => $facility->id,
        'status' => 'active',
    ]);
});

it('O5 tidak menampilkan peringatan D4 tanpa reservasi approved mendatang pada fasilitas laporan', function (): void {
    $this->travelTo(Carbon::parse('2030-04-08 08:00:00'));

    $petugas = buatAkunDenganRole('petugas');
    $pengguna = buatAkunDenganRole('pengguna');
    $facility = Facility::factory()->create();

    $report = new Report([
        'facility_id' => $facility->id,
        'category' => 'kerusakan_alat',
        'description' => 'Proyektor rusak pada fasilitas tanpa reservasi approved mendatang.',
        'status' => 'baru',
    ]);
    $report->user_id = $pengguna->id;
    $report->save();

    $otherFacility = Facility::factory()->create();
    $otherReservation = Reservation::factory()
        ->approved()
        ->for($otherFacility)
        ->for($pengguna)
        ->create();

    $response = $this->actingAs($petugas)
        ->get(route('officer.reports.show', $report));

    $response->assertOk();

    $document = new DOMDocument;
    $document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
    $xpath = new DOMXPath($document);
    $modalId = 'statusModal-'.$facility->id;
    $modal = $xpath->query('//div[@id="'.$modalId.'"]')->item(0);

    expect($modal)->not->toBeNull();
    expect($xpath->query(
        '//button[@type="button" and @data-bs-toggle="modal" and @data-bs-target="#'.$modalId.'"]'
    )->length)->toBe(1);
    expect($modal->textContent)->not->toContain('reservasi yang sudah disetujui');
    expect($xpath->query('.//table', $modal)->length)->toBe(0);
    expect($xpath->query(
        './/a[@href="'.route('officer.reservations.show', $otherReservation).'"]',
        $modal
    )->length)->toBe(0);

    $statusForm = $xpath->query(
        './/div[contains(concat(" ", normalize-space(@class), " "), " modal-footer ")]//form[@action="'.
        route('officer.facilities.status', $facility).'"]',
        $modal
    )->item(0);

    expect($statusForm)->not->toBeNull();
    expect(strtoupper($statusForm->getAttribute('method')))->toBe('POST');
    expect($xpath->evaluate('string(.//input[@name="_method"]/@value)', $statusForm))->toBe('PATCH');
    expect($xpath->evaluate('string(.//input[@name="status"]/@value)', $statusForm))->toBe('under_maintenance');
    expect($xpath->evaluate('normalize-space(.//button[@type="submit"])', $statusForm))->toBe('Tandai Dalam Perbaikan');

    $this->assertDatabaseHas('reservations', [
        'id' => $otherReservation->id,
        'status' => 'approved',
    ]);
    $this->assertDatabaseHas('facilities', [
        'id' => $facility->id,
        'status' => 'active',
    ]);
});

it('O5 mengubah fasilitas menjadi dalam perbaikan tanpa membatalkan reservasi approved setelah submit', function (): void {
    $this->travelTo(Carbon::parse('2030-04-08 08:00:00'));

    $petugas = buatAkunDenganRole('petugas');
    $pengguna = buatAkunDenganRole('pengguna');
    $facility = Facility::factory()->active()->create();

    $report = new Report([
        'facility_id' => $facility->id,
        'category' => 'kerusakan_alat',
        'description' => 'Proyektor rusak dan petugas akan mengubah status fasilitas dari halaman laporan.',
        'status' => 'baru',
    ]);
    $report->user_id = $pengguna->id;
    $report->save();

    $reservation = Reservation::factory()
        ->approved()
        ->for($facility)
        ->for($pengguna)
        ->create();

    $reportUrl = route('officer.reports.show', $report);
    $response = $this->actingAs($petugas)
        ->from($reportUrl)
        ->patch(route('officer.facilities.status', $facility), [
            'status' => 'under_maintenance',
        ]);

    $response->assertRedirect($reportUrl);
    expect($facility->refresh()->status)->toBe('under_maintenance');
    expect($reservation->refresh()->status)->toBe('approved');
});

it('O5 menyembunyikan modal D4 dan aksi tandai perbaikan saat fasilitas sudah dalam perbaikan', function (): void {
    $this->travelTo(Carbon::parse('2030-04-08 08:00:00'));

    $petugas = buatAkunDenganRole('petugas');
    $pengguna = buatAkunDenganRole('pengguna');
    $facility = Facility::factory()->underMaintenance()->create();

    $report = new Report([
        'facility_id' => $facility->id,
        'category' => 'kerusakan_alat',
        'description' => 'Proyektor sedang ditangani pada fasilitas yang sudah dalam perbaikan.',
        'status' => 'baru',
    ]);
    $report->user_id = $pengguna->id;
    $report->save();

    Reservation::factory()
        ->approved()
        ->for($facility)
        ->for($pengguna)
        ->create();

    $response = $this->actingAs($petugas)
        ->get(route('officer.reports.show', $report));

    $response->assertOk()
        ->assertDontSeeText('Tandai Dalam Perbaikan');

    $document = new DOMDocument;
    $document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
    $xpath = new DOMXPath($document);
    $modalId = 'statusModal-'.$facility->id;

    expect($xpath->query('//div[@id="'.$modalId.'"]')->length)->toBe(0);
    expect($xpath->query(
        '//button[@data-bs-toggle="modal" and @data-bs-target="#'.$modalId.'"]'
    )->length)->toBe(0);
    expect($xpath->query(
        '//form[@action="'.route('officer.facilities.status', $facility).'"]'
    )->length)->toBe(0);
    expect($xpath->query(
        '//button[contains(normalize-space(.), "Tandai Dalam Perbaikan")]'
    )->length)->toBe(0);
});
