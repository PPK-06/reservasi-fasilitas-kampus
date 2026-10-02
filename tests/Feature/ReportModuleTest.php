<?php

use App\Models\Facility;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    if (
        ! Schema::hasTable('users') ||
        ! Schema::hasTable('facilities') ||
        ! Schema::hasTable('reports')
    ) {
        $this->markTestSkipped('Database project belum siap.');
    }

    $this->pengguna = buatAkunDenganRole('pengguna');
    $this->penggunaLain = buatAkunDenganRole('pengguna');
    $this->petugas = buatAkunDenganRole('petugas');

    $this->facility = Facility::create([
        'name' => 'Ruang Test M4',
        'type' => 'Ruang Kelas',
        'location' => 'Gedung A',
        'capacity' => 30,
        'description' => 'Fasilitas untuk pengujian modul laporan.',
        'status' => 'active',
    ]);
});

afterEach(function (): void {
    try {
        if (isset($this->temporaryReportImage) && File::exists($this->temporaryReportImage)) {
            File::delete($this->temporaryReportImage);
        }

        if (isset($this->reportTestPublicPath) && File::isDirectory($this->reportTestPublicPath)) {
            $path = realpath($this->reportTestPublicPath);
            $testingPath = realpath(storage_path('framework/testing'));

            if (
                $path === false ||
                $testingPath === false ||
                ! str_starts_with($path, $testingPath.DIRECTORY_SEPARATOR)
            ) {
                throw new RuntimeException('Direktori upload test berada di luar storage testing.');
            }

            File::deleteDirectory($path);
        }
    } finally {
        if (isset($this->originalPublicPath)) {
            $this->app->usePublicPath($this->originalPublicPath);
        }
    }
});

function createReportForM4Test(
    User $user,
    Facility $facility,
    array $attributes = []
): Report {
    $report = new Report([
        'facility_id' => $facility->id,
        'category' => $attributes['category'] ?? 'kerusakan_alat',
        'description' => $attributes['description']
            ?? 'Kerusakan fasilitas untuk kebutuhan pengujian.',
        'status' => $attributes['status'] ?? 'baru',
        'resolution_note' => $attributes['resolution_note'] ?? null,
    ]);

    $report->user_id = $user->id;
    $report->save();

    return $report;
}

it('mengarahkan tamu ke login ketika membuka halaman laporan pengguna', function (): void {
    $this->get(route('reports.index'))
        ->assertRedirect(route('login'));
});

it('menolak role selain pengguna ketika membuka halaman laporan pengguna', function (): void {
    $this->actingAs($this->petugas)
        ->get(route('reports.index'))
        ->assertForbidden();
});

it('U4 pengguna dapat membuat laporan kerusakan', function (): void {
    $this->originalPublicPath = public_path();
    $this->reportTestPublicPath = storage_path('framework/testing/report-public-'.bin2hex(random_bytes(8)));
    $this->app->usePublicPath($this->reportTestPublicPath);

    $tempImage = tempnam(sys_get_temp_dir(), 'report-test-');

    if ($tempImage === false) {
        throw new RuntimeException('Tidak dapat membuat file sementara untuk upload laporan.');
    }

    $this->temporaryReportImage = $tempImage;

    copy(
        database_path('seeders/sample-photos/foto1.jpg'),
        $tempImage
    );

    $photo = new UploadedFile(
        $tempImage,
        'kerusakan.jpg',
        'image/jpeg',
        null,
        true
    );

    $response = $this
        ->actingAs($this->pengguna)
        ->post(route('reports.store'), [
            'facility_id' => $this->facility->id,
            'category' => 'kerusakan_alat',
            'description' => 'Proyektor di ruang kelas tidak dapat menyala dengan normal.',
            'photos' => [$photo],
        ]);

    $report = Report::query()
        ->where('user_id', $this->pengguna->id)
        ->latest('id')
        ->firstOrFail();

    $response->assertRedirect(route('reports.show', $report));

    expect($report->facility_id)->toBe($this->facility->id)
        ->and($report->category)->toBe('kerusakan_alat')
        ->and($report->status)->toBe('baru');

    $report->load('photos');

    expect($report->photos)->toHaveCount(1);

    $fileName = $report->photos->first()->file_name;

    expect(
        File::exists(public_path('uploads/reports/'.$fileName))
    )->toBeTrue();
});

it('U5 pengguna hanya melihat laporan miliknya', function (): void {
    $ownReport = createReportForM4Test(
        $this->pengguna,
        $this->facility,
        [
            'description' =>
                'Laporan milik pengguna pertama untuk pengujian.',
        ]
    );

    $otherReport = createReportForM4Test(
        $this->penggunaLain,
        $this->facility,
        [
            'description' =>
                'Laporan milik pengguna kedua yang tidak boleh terlihat.',
        ]
    );

    $response = $this
        ->actingAs($this->pengguna)
        ->get(route('reports.index'));

    $response
        ->assertOk()
        ->assertSee(route('reports.show', $ownReport), false)
        ->assertDontSee(route('reports.show', $otherReport), false);
});
it('U6 pemilik dapat melihat detail laporannya', function (): void {
    $report = createReportForM4Test(
        $this->pengguna,
        $this->facility
    );

    $this->actingAs($this->pengguna)
        ->get(route('reports.show', $report))
        ->assertOk()
        ->assertSee($report->description);
});

it('U6 pengguna lain tidak dapat melihat laporan yang bukan miliknya', function (): void {
    $report = createReportForM4Test(
        $this->pengguna,
        $this->facility
    );

    $this->actingAs($this->penggunaLain)
        ->get(route('reports.show', $report))
        ->assertForbidden();
});

it('O4 petugas dapat membuka daftar laporan', function (): void {
    $report = createReportForM4Test(
        $this->pengguna,
        $this->facility
    );

    $response = $this
        ->actingAs($this->petugas)
        ->get(route('officer.reports.index'));

    $response
        ->assertOk()
        ->assertSee(
            route('officer.reports.show', $report),
            false
        );
});
it('O4 menolak pengguna biasa', function (): void {
    $this->actingAs($this->pengguna)
        ->get(route('officer.reports.index'))
        ->assertForbidden();
});

it('O5 petugas dapat mengubah laporan menjadi selesai dengan catatan resolusi', function (): void {
    $report = createReportForM4Test(
        $this->pengguna,
        $this->facility,
        ['status' => 'diproses']
    );

    $this->actingAs($this->petugas)
        ->patch(route('officer.reports.update', $report), [
            'status' => 'selesai',
            'resolution_note' =>
                'Kerusakan sudah diperbaiki dan fasilitas dapat digunakan kembali.',
        ])
        ->assertRedirect(route('officer.reports.index'));

    $this->assertDatabaseHas('reports', [
        'id' => $report->id,
        'status' => 'selesai',
        'resolution_note' =>
            'Kerusakan sudah diperbaiki dan fasilitas dapat digunakan kembali.',
    ]);
});

it('O5 pengguna biasa tidak dapat mengubah laporan', function (): void {
    $report = createReportForM4Test(
        $this->pengguna,
        $this->facility,
        ['status' => 'diproses']
    );

    $this->actingAs($this->pengguna)
        ->patch(route('officer.reports.update', $report), [
            'status' => 'selesai',
            'resolution_note' =>
                'Kerusakan sudah diperbaiki dan fasilitas dapat digunakan kembali.',
        ])
        ->assertForbidden();

    $this->assertDatabaseHas('reports', [
        'id' => $report->id,
        'status' => 'diproses',
        'resolution_note' => null,
    ]);
});

it('O5 mewajibkan catatan resolusi untuk status akhir', function (string $status): void {
    $report = createReportForM4Test(
        $this->pengguna,
        $this->facility,
        ['status' => 'diproses']
    );

    $this->actingAs($this->petugas)
        ->from(route('officer.reports.show', $report))
        ->patch(route('officer.reports.update', $report), [
            'status' => $status,
        ])
        ->assertRedirect(route('officer.reports.show', $report))
        ->assertSessionHasErrors('resolution_note');

    $this->assertDatabaseHas('reports', [
        'id' => $report->id,
        'status' => 'diproses',
        'resolution_note' => null,
    ]);
})->with(['selesai', 'ditolak']);
