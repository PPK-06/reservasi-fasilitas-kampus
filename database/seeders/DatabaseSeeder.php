<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Report;
use App\Models\ReportPhoto;
use App\Models\Reservation;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use SplFileInfo;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Folder berisi foto contoh yang disalin ke public/uploads/reports/.
     */
    private const SAMPLE_PHOTO_DIR = 'seeders/sample-photos';

    /**
     * Tujuan penyalinan foto laporan (F5).
     */
    private const UPLOAD_DIR = 'uploads/reports';

    /**
     * Ekstensi foto yang diterima B2.
     *
     * @var list<string>
     */
    private const PHOTO_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    /**
     * Seed the application's database.
     *
     * Akun demo mengikuti bagian 5 dokumen pembagian modul: 1 admin,
     * 2 petugas, dan pengguna yang mencakup keempat status C2. Seluruhnya
     * memakai satu password seragam, yaitu UserFactory::DEMO_PASSWORD.
     *
     * Urutan penyisipan mengikuti arah foreign key: users → facilities →
     * reservations → reports → report_photos.
     *
     * Foto contoh diperiksa lebih dulu, sebelum satu baris pun ditulis.
     * Tanpa berkas contoh, B2 (setiap laporan wajib punya foto) tidak dapat
     * dipenuhi, dan berhenti di awal lebih baik daripada meninggalkan
     * separuh data.
     */
    public function run(): void
    {
        $samplePhotos = $this->samplePhotoFiles();

        $this->seedUsers();

        $facilities = $this->seedFacilities();
        $this->seedReservations($facilities);
        $reports = $this->seedReports($facilities);
        $this->seedReportPhotos($reports, $samplePhotos);
    }

    /**
     * Akun demo. Status diisi lewat state factory — factory berjalan
     * unguarded, sehingga kolom status yang tidak fillable (F10) tetap terisi
     * tanpa melanggar aturan mass assignment.
     */
    private function seedUsers(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin Kampus',
            'email' => 'admin@kampus.test',
        ]);

        User::factory()->petugas()->create([
            'name' => 'Petugas Sarana',
            'email' => 'petugas1@kampus.test',
        ]);

        User::factory()->petugas()->create([
            'name' => 'Petugas Laboratorium',
            'email' => 'petugas2@kampus.test',
        ]);

        User::factory()->create([
            'name' => 'Pengguna Verified',
            'email' => 'pengguna.verified@kampus.test',
            'identity_number' => '2311000001',
            'user_type' => 'mahasiswa',
        ]);

        User::factory()->create([
            'name' => 'Pengguna Verified Kedua',
            'email' => 'pengguna.verified2@kampus.test',
            'identity_number' => '2311000005',
            'user_type' => 'dosen',
        ]);

        User::factory()->create([
            'name' => 'Pengguna Verified Ketiga',
            'email' => 'pengguna.verified3@kampus.test',
            'identity_number' => '2311000006',
            'user_type' => 'staf',
        ]);

        User::factory()->pending()->create([
            'name' => 'Pengguna Pending',
            'email' => 'pengguna.pending@kampus.test',
            'identity_number' => '2311000002',
            'user_type' => 'mahasiswa',
        ]);

        User::factory()->rejected()->create([
            'name' => 'Pengguna Rejected',
            'email' => 'pengguna.rejected@kampus.test',
            'identity_number' => '2311000003',
            'user_type' => 'dosen',
        ]);

        User::factory()->suspended()->create([
            'name' => 'Pengguna Suspended',
            'email' => 'pengguna.suspended@kampus.test',
            'identity_number' => '2311000004',
            'user_type' => 'staf',
        ]);

        $this->command->info('Akun demo dibuat. Password seragam: '.UserFactory::DEMO_PASSWORD);
    }

    /**
     * Data master fasilitas: kelima tipe D1, keenam lokasi D2, dan ketiga
     * status D3. Tipe Alat ber-capacity NULL sesuai A4.
     *
     * Kolom diisi lewat penetapan properti, bukan mass assignment, karena
     * penjaga F11 aktif dan #[Fillable] milik modul lain tidak boleh diubah.
     *
     * @return array<string, Facility> dikunci nama pendek agar terbaca saat
     *                                 dipakai reservasi dan laporan
     */
    private function seedFacilities(): array
    {
        /** @var list<array{key: string, name: string, type: string, location: string, capacity: int|null, description: string, status: string}> $rows */
        $rows = [
            [
                'key' => 'kelas_a101',
                'name' => 'Ruang Kelas A-101',
                'type' => 'Ruang Kelas',
                'location' => 'Gedung A',
                'capacity' => 40,
                'description' => 'Ruang kuliah reguler dengan proyektor terpasang dan papan tulis putih.',
                'status' => 'active',
            ],
            [
                'key' => 'kelas_a102',
                'name' => 'Ruang Kelas A-102',
                'type' => 'Ruang Kelas',
                'location' => 'Gedung A',
                'capacity' => 40,
                'description' => 'Ruang kuliah reguler bersebelahan dengan A-101.',
                'status' => 'active',
            ],
            [
                'key' => 'kelas_b201',
                'name' => 'Ruang Kelas B-201',
                'type' => 'Ruang Kelas',
                'location' => 'Gedung B',
                'capacity' => 30,
                'description' => 'Ruang kuliah kecil untuk kelas teori berkapasitas terbatas.',
                'status' => 'under_maintenance',
            ],
            [
                'key' => 'kelas_c301',
                'name' => 'Ruang Kelas C-301',
                'type' => 'Ruang Kelas',
                'location' => 'Gedung C',
                'capacity' => 60,
                'description' => 'Ruang kuliah besar berundak, cocok untuk kelas gabungan.',
                'status' => 'active',
            ],
            [
                'key' => 'aula_utama',
                'name' => 'Aula Utama',
                'type' => 'Aula',
                'location' => 'Gedung Serba Guna',
                'capacity' => 500,
                'description' => 'Aula utama kampus dengan panggung, tata suara, dan pendingin ruangan.',
                'status' => 'active',
            ],
            [
                'key' => 'aula_lantai2',
                'name' => 'Aula Serbaguna Lantai 2',
                'type' => 'Aula',
                'location' => 'Gedung Serba Guna',
                'capacity' => 200,
                'description' => 'Aula kedua yang sedang dialihfungsikan menjadi ruang arsip.',
                'status' => 'inactive',
            ],
            [
                'key' => 'lab_komputer',
                'name' => 'Laboratorium Komputer 1',
                'type' => 'Laboratorium',
                'location' => 'Gedung B',
                'capacity' => 35,
                'description' => 'Laboratorium dengan 35 unit komputer dan jaringan kabel.',
                'status' => 'active',
            ],
            [
                'key' => 'lab_jaringan',
                'name' => 'Laboratorium Jaringan',
                'type' => 'Laboratorium',
                'location' => 'Gedung B',
                'capacity' => 25,
                'description' => 'Laboratorium praktikum jaringan, instalasi listriknya sedang diperbaiki.',
                'status' => 'under_maintenance',
            ],
            [
                'key' => 'lapangan_basket',
                'name' => 'Lapangan Basket',
                'type' => 'Lapangan',
                'location' => 'Area Olahraga',
                'capacity' => 100,
                'description' => 'Lapangan basket luar ruangan dengan tribun kecil.',
                'status' => 'active',
            ],
            [
                'key' => 'lapangan_futsal',
                'name' => 'Lapangan Futsal',
                'type' => 'Lapangan',
                'location' => 'Area Olahraga',
                'capacity' => 80,
                'description' => 'Lapangan futsal beralas sintetis dengan penerangan malam.',
                'status' => 'active',
            ],
            [
                'key' => 'proyektor',
                'name' => 'Proyektor Epson EB-X51',
                'type' => 'Alat',
                'location' => 'Gudang Inventaris',
                'capacity' => null,
                'description' => 'Proyektor portabel beserta kabel HDMI dan VGA.',
                'status' => 'active',
            ],
            [
                'key' => 'sound_system',
                'name' => 'Sound System Portabel',
                'type' => 'Alat',
                'location' => 'Gudang Inventaris',
                'capacity' => null,
                'description' => 'Perangkat tata suara bergerak yang ditarik dari peredaran.',
                'status' => 'inactive',
            ],
        ];

        $facilities = [];

        foreach ($rows as $row) {
            $facility = new Facility;
            $facility->name = $row['name'];
            $facility->type = $row['type'];
            $facility->location = $row['location'];
            $facility->capacity = $row['capacity'];
            $facility->description = $row['description'];
            $facility->status = $row['status'];
            $facility->save();

            $facilities[$row['key']] = $facility;
        }

        $this->command->info('Fasilitas dibuat: '.count($facilities).' baris.');

        return $facilities;
    }

    /**
     * Reservasi yang mencakup kelima status A7, termasuk `pending` yang
     * start_time-nya sudah lewat (A8) — data yang mustahil dibuat lewat form
     * karena ditolak A3, dan satu-satunya cara menguji tab "Terlewat" di O2.
     *
     * Baris yang belum lewat mengikuti A2 dan A3; baris yang sengaja lewat
     * ditulis datetime-nya langsung di sini, tanpa menyentuh validasi mana pun.
     *
     * Tiga baris `approved` pada Aula Utama sengaja ditumpuk di masa depan
     * supaya peringatan D4 punya data saat fasilitas itu dinonaktifkan.
     *
     * @param  array<string, Facility>  $facilities
     */
    private function seedReservations(array $facilities): void
    {
        $verified = $this->userByEmail('pengguna.verified@kampus.test');
        $verifiedKedua = $this->userByEmail('pengguna.verified2@kampus.test');
        $verifiedKetiga = $this->userByEmail('pengguna.verified3@kampus.test');
        $suspended = $this->userByEmail('pengguna.suspended@kampus.test');

        /** @var list<array{user: User, facility: string, days: int, start: string, end: string, purpose: string, status: string, reason: string|null}> $rows */
        $rows = [
            // pending yang sudah lewat — A8, bahan tab "Terlewat" di O2
            [
                'user' => $verified,
                'facility' => 'lab_komputer',
                'days' => -6,
                'start' => '09:00',
                'end' => '11:00',
                'purpose' => 'Praktikum tambahan mata kuliah Basis Data.',
                'status' => 'pending',
                'reason' => null,
            ],
            [
                'user' => $verifiedKedua,
                'facility' => 'kelas_a101',
                'days' => -3,
                'start' => '13:00',
                'end' => '15:30',
                'purpose' => 'Diskusi kelompok tugas besar Rekayasa Perangkat Lunak.',
                'status' => 'pending',
                'reason' => null,
            ],
            [
                'user' => $suspended,
                'facility' => 'lapangan_futsal',
                'days' => -1,
                'start' => '16:00',
                'end' => '18:00',
                'purpose' => 'Latihan rutin tim futsal fakultas.',
                'status' => 'pending',
                'reason' => null,
            ],

            // pending yang belum lewat — antrian utama petugas
            [
                'user' => $verifiedKetiga,
                'facility' => 'kelas_a102',
                'days' => 1,
                'start' => '08:00',
                'end' => '10:00',
                'purpose' => 'Kuliah pengganti mata kuliah Jaringan Komputer.',
                'status' => 'pending',
                'reason' => null,
            ],
            [
                'user' => $verifiedKedua,
                'facility' => 'aula_utama',
                'days' => 2,
                'start' => '13:00',
                'end' => '16:00',
                'purpose' => 'Seminar nasional himpunan mahasiswa informatika.',
                'status' => 'pending',
                'reason' => null,
            ],
            [
                'user' => $suspended,
                'facility' => 'lapangan_basket',
                'days' => 5,
                'start' => '09:30',
                'end' => '11:30',
                'purpose' => 'Seleksi anggota baru unit kegiatan bola basket.',
                'status' => 'pending',
                'reason' => null,
            ],
            [
                'user' => $verified,
                'facility' => 'kelas_c301',
                'days' => 12,
                'start' => '07:00',
                'end' => '20:00',
                'purpose' => 'Pelatihan sertifikasi sehari penuh untuk mahasiswa tingkat akhir.',
                'status' => 'pending',
                'reason' => null,
            ],

            // approved menumpuk di satu fasilitas — bahan peringatan D4
            [
                'user' => $verifiedKetiga,
                'facility' => 'aula_utama',
                'days' => 3,
                'start' => '08:00',
                'end' => '12:00',
                'purpose' => 'Wisuda periode ganjil tingkat fakultas.',
                'status' => 'approved',
                'reason' => null,
            ],
            [
                'user' => $suspended,
                'facility' => 'aula_utama',
                'days' => 4,
                'start' => '13:00',
                'end' => '17:00',
                'purpose' => 'Gladi bersih acara dies natalis kampus.',
                'status' => 'approved',
                'reason' => null,
            ],
            [
                'user' => $verifiedKedua,
                'facility' => 'aula_utama',
                'days' => 7,
                'start' => '09:00',
                'end' => '11:00',
                'purpose' => 'Rapat koordinasi seluruh program studi.',
                'status' => 'approved',
                'reason' => null,
            ],
            [
                'user' => $verified,
                'facility' => 'lapangan_basket',
                'days' => 6,
                'start' => '15:00',
                'end' => '17:00',
                'purpose' => 'Pertandingan persahabatan antar fakultas.',
                'status' => 'approved',
                'reason' => null,
            ],

            // approved yang sudah selesai — "completed" diturunkan dari end_time (A7)
            [
                'user' => $verifiedKetiga,
                'facility' => 'lab_komputer',
                'days' => -10,
                'start' => '09:00',
                'end' => '12:00',
                'purpose' => 'Praktikum Pemrograman Web pertemuan kesembilan.',
                'status' => 'approved',
                'reason' => null,
            ],

            // rejected — status_reason wajib, minimal 10 karakter (A7)
            [
                'user' => $verifiedKedua,
                'facility' => 'aula_utama',
                'days' => 8,
                'start' => '10:00',
                'end' => '12:00',
                'purpose' => 'Acara temu alumni angkatan 2020.',
                'status' => 'rejected',
                'reason' => 'Bentrok dengan kegiatan akreditasi program studi pada tanggal yang sama.',
            ],
            [
                'user' => $suspended,
                'facility' => 'kelas_b201',
                'days' => -15,
                'start' => '08:00',
                'end' => '10:00',
                'purpose' => 'Kelas tambahan persiapan ujian tengah semester.',
                'status' => 'rejected',
                'reason' => 'Ruangan sedang diperbaiki pendingin ruangannya sampai waktu yang belum ditentukan.',
            ],

            // cancelled_by_user — alasan opsional (A7)
            [
                'user' => $verifiedKetiga,
                'facility' => 'kelas_a101',
                'days' => 9,
                'start' => '14:00',
                'end' => '16:00',
                'purpose' => 'Sosialisasi program kerja himpunan mahasiswa.',
                'status' => 'cancelled_by_user',
                'reason' => 'Acara ditunda ke semester berikutnya.',
            ],
            [
                'user' => $verified,
                'facility' => 'lapangan_futsal',
                'days' => 11,
                'start' => '08:00',
                'end' => '09:30',
                'purpose' => 'Latihan bersama tim futsal antar jurusan.',
                'status' => 'cancelled_by_user',
                'reason' => null,
            ],

            // cancelled_by_officer — status_reason wajib, minimal 10 karakter (A7, B5)
            [
                'user' => $verifiedKedua,
                'facility' => 'kelas_a102',
                'days' => 6,
                'start' => '13:00',
                'end' => '15:00',
                'purpose' => 'Rapat panitia kegiatan orientasi mahasiswa baru.',
                'status' => 'cancelled_by_officer',
                'reason' => 'Dibatalkan karena ruangan dipakai untuk kegiatan mendesak tingkat fakultas.',
            ],
            [
                'user' => $suspended,
                'facility' => 'lab_jaringan',
                'days' => -8,
                'start' => '10:00',
                'end' => '12:00',
                'purpose' => 'Uji coba perangkat jaringan untuk keperluan tugas akhir.',
                'status' => 'cancelled_by_officer',
                'reason' => 'Laboratorium ditutup sementara karena perbaikan instalasi listrik.',
            ],
        ];

        foreach ($rows as $row) {
            $reservation = new Reservation;
            $reservation->user_id = $row['user']->id;
            $reservation->facility_id = $facilities[$row['facility']]->id;
            $reservation->start_time = $this->slotAt($row['days'], $row['start']);
            $reservation->end_time = $this->slotAt($row['days'], $row['end']);
            $reservation->purpose = $row['purpose'];
            $reservation->status = $row['status'];
            $reservation->status_reason = $row['reason'];
            $reservation->save();
        }

        $this->command->info('Reservasi dibuat: '.count($rows).' baris.');
    }

    /**
     * Laporan kerusakan yang mencakup keempat status B3 dan keenam kategori
     * B1. resolution_note mengikuti B3: wajib untuk `selesai` dan `ditolak`,
     * opsional untuk `diproses`, dan NULL untuk `baru`.
     *
     * Pelapor disebar ke ketiga akun pengguna berstatus `verified` supaya
     * antrian O4 tidak terlihat berasal dari satu orang saja.
     *
     * @param  array<string, Facility>  $facilities
     * @return list<array{report: Report, photos: int}> jumlah foto per laporan,
     *                                                  dipakai seedReportPhotos
     */
    private function seedReports(array $facilities): array
    {
        $reporters = [
            'pertama' => $this->userByEmail('pengguna.verified@kampus.test'),
            'kedua' => $this->userByEmail('pengguna.verified2@kampus.test'),
            'ketiga' => $this->userByEmail('pengguna.verified3@kampus.test'),
        ];

        /** @var list<array{reporter: string, facility: string, category: string, description: string, status: string, note: string|null, photos: int}> $rows */
        $rows = [
            [
                'reporter' => 'pertama',
                'facility' => 'lab_komputer',
                'category' => 'kerusakan_alat',
                'description' => 'Monitor pada komputer nomor 12 tidak menyala meskipun kabel daya sudah diganti.',
                'status' => 'baru',
                'note' => null,
                'photos' => 2,
            ],
            [
                'reporter' => 'kedua',
                'facility' => 'kelas_a101',
                'category' => 'kelistrikan',
                'description' => 'Dua stop kontak di sisi belakang ruangan tidak mengalirkan listrik sejak pekan lalu.',
                'status' => 'baru',
                'note' => null,
                'photos' => 1,
            ],
            [
                'reporter' => 'ketiga',
                'facility' => 'kelas_b201',
                'category' => 'pendingin_ruangan',
                'description' => 'AC ruangan mengeluarkan bunyi keras dan tidak mendinginkan ruangan sama sekali.',
                'status' => 'diproses',
                'note' => 'Teknisi sudah memeriksa unit, menunggu suku cadang kompresor datang.',
                'photos' => 3,
            ],
            [
                'reporter' => 'pertama',
                'facility' => 'kelas_c301',
                'category' => 'furnitur',
                'description' => 'Sepuluh kursi kuliah patah pada bagian sandaran dan tidak aman dipakai.',
                'status' => 'diproses',
                'note' => null,
                'photos' => 1,
            ],
            [
                'reporter' => 'kedua',
                'facility' => 'aula_utama',
                'category' => 'kebersihan',
                'description' => 'Sisa sampah acara kemarin belum dibersihkan dan lantai panggung masih licin.',
                'status' => 'selesai',
                'note' => 'Aula sudah dibersihkan petugas kebersihan pada hari yang sama.',
                'photos' => 2,
            ],
            [
                'reporter' => 'ketiga',
                'facility' => 'lapangan_basket',
                'category' => 'lainnya',
                'description' => 'Garis lapangan sudah pudar sehingga batas area sulit dikenali saat pertandingan.',
                'status' => 'selesai',
                'note' => 'Garis lapangan sudah dicat ulang dan dikeringkan selama dua hari.',
                'photos' => 1,
            ],
            [
                'reporter' => 'pertama',
                'facility' => 'proyektor',
                'category' => 'kerusakan_alat',
                'description' => 'Proyektor menampilkan warna kehijauan pada seluruh layar saat dihubungkan ke laptop.',
                'status' => 'ditolak',
                'note' => 'Proyektor diuji ulang dan berfungsi normal; kemungkinan kabel HDMI milik pengguna yang rusak.',
                'photos' => 2,
            ],
            [
                'reporter' => 'kedua',
                'facility' => 'lab_jaringan',
                'category' => 'kelistrikan',
                'description' => 'Panel listrik laboratorium berbunyi dan sempat memicu korsleting saat praktikum.',
                'status' => 'ditolak',
                'note' => 'Laporan ganda; sudah ditangani lewat laporan sebelumnya pada fasilitas yang sama.',
                'photos' => 3,
            ],
        ];

        $reports = [];

        foreach ($rows as $row) {
            $report = new Report;
            $report->user_id = $reporters[$row['reporter']]->id;
            $report->facility_id = $facilities[$row['facility']]->id;
            $report->category = $row['category'];
            $report->description = $row['description'];
            $report->status = $row['status'];
            $report->resolution_note = $row['note'];
            $report->save();

            $reports[] = ['report' => $report, 'photos' => $row['photos']];
        }

        $this->command->info('Laporan dibuat: '.count($reports).' baris.');

        return $reports;
    }

    /**
     * Foto laporan. Setiap laporan memperoleh 1 sampai 3 foto sesuai B2, dan
     * berkas fisiknya benar-benar disalin ke public/uploads/reports/ supaya
     * U6 dan O5 tidak menampilkan gambar rusak.
     *
     * Nama berkas digenerate Str::random(40) + ekstensi (F5), dan yang
     * disimpan di kolom file_name hanya nama itu tanpa path (skema 6.5).
     *
     * @param  list<array{report: Report, photos: int}>  $reports
     * @param  list<SplFileInfo>  $samplePhotos
     */
    private function seedReportPhotos(array $reports, array $samplePhotos): void
    {
        $uploadDirectory = public_path(self::UPLOAD_DIR);
        File::ensureDirectoryExists($uploadDirectory);
        $this->emptyUploadDirectory($uploadDirectory);

        $sampleCount = count($samplePhotos);
        $copied = 0;

        foreach ($reports as $entry) {
            for ($index = 0; $index < $entry['photos']; $index++) {
                $sample = $samplePhotos[$copied % $sampleCount];
                $copied++;

                $fileName = Str::random(40).'.'.strtolower($sample->getExtension());
                File::copy($sample->getPathname(), $uploadDirectory.DIRECTORY_SEPARATOR.$fileName);

                $photo = new ReportPhoto;
                $photo->report_id = $entry['report']->id;
                $photo->file_name = $fileName;
                $photo->save();
            }
        }

        $this->command->info('Foto laporan dibuat: '.$copied.' berkas di public/'.self::UPLOAD_DIR.'/.');
    }

    /**
     * Mengosongkan folder upload laporan sebelum foto contoh disalin.
     *
     * migrate:fresh mengosongkan tabel report_photos, tapi tidak menyentuh
     * berkas fisiknya. Tanpa langkah ini setiap seeding meninggalkan berkas
     * yatim yang tidak lagi dirujuk baris mana pun (F5). Foldernya sendiri
     * dan .gitkeep dipertahankan karena keduanya dilacak git.
     */
    private function emptyUploadDirectory(string $uploadDirectory): void
    {
        foreach (File::files($uploadDirectory, true) as $file) {
            if ($file->getFilename() !== '.gitkeep') {
                File::delete($file->getPathname());
            }
        }
    }

    /**
     * Berkas foto contoh yang tersedia untuk disalin.
     *
     * Seeder tidak pernah membuat atau mengunduh gambar sendiri: kalau
     * foldernya kosong, yang benar adalah berhenti dan memberi tahu, bukan
     * menghasilkan laporan tanpa foto yang melanggar B2.
     *
     * @return list<SplFileInfo>
     */
    private function samplePhotoFiles(): array
    {
        $directory = database_path(self::SAMPLE_PHOTO_DIR);

        $files = File::isDirectory($directory)
            ? array_values(array_filter(
                File::files($directory),
                fn (SplFileInfo $file): bool => in_array(strtolower($file->getExtension()), self::PHOTO_EXTENSIONS, true),
            ))
            : [];

        if ($files === []) {
            throw new RuntimeException(
                'Tidak ada foto contoh di database/'.self::SAMPLE_PHOTO_DIR.'/. '
                .'B2 mewajibkan setiap laporan punya minimal satu foto, dan seeder ini tidak '
                .'membuat gambar sendiri. Letakkan beberapa berkas '.implode('/', self::PHOTO_EXTENSIONS)
                .' di folder itu, lalu jalankan ulang php artisan migrate:fresh --seed.'
            );
        }

        return $files;
    }

    /**
     * Satu titik waktu reservasi relatif terhadap hari ini, mengikuti F1:
     * menit 00 atau 30, detik 0, dan berada di rentang 07:00–20:00.
     *
     * Carbon memakai timezone aplikasi (F3, Asia/Jakarta), jadi tidak ada
     * offset yang ditulis manual di sini.
     *
     * @param  int  $dayOffset  jumlah hari dari hari ini; negatif berarti masa lalu
     * @param  string  $time  jam dalam bentuk HH:MM
     */
    private function slotAt(int $dayOffset, string $time): Carbon
    {
        return Carbon::today()->addDays($dayOffset)->setTimeFromTimeString($time.':00');
    }

    /**
     * Akun demo yang sudah dibuat seedUsers(), dicari lewat email supaya
     * bagian akun tidak perlu diubah sama sekali.
     */
    private function userByEmail(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }
}
