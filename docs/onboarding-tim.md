# Panduan Onboarding — Sistem Reservasi & Pelaporan Fasilitas Kampus

**Untuk:** 3 anggota tim selain PM
**Repo:** https://github.com/PPK-06/reservasi-fasilitas-kampus
**Versi dokumen:** 1.5 — 23 September 2026
**Tujuan:** dari nol sampai aplikasi jalan di komputermu, lalu siap mulai ngoding modul masing-masing.

Baca sampai habis sebelum mulai. Kalau ada langkah yang gagal, lapor di grup — jangan cari solusi sendiri lalu diam-diam mengubah konfigurasi, karena konfigurasi yang berbeda antar anggota adalah sumber masalah paling mahal di proyek ini.

---

## Perubahan dari v1.4

| Bagian | Perubahan |
|---|---|
| 4 | + `APP_LOCALE=id` masuk daftar baris `.env` yang wajib seragam di semua komputer. Dasarnya D17 `keputusan-m1-tahap2.md` |
| 4 | Kalimat "Baris lain jangan diubah" diperjelas. Sebelumnya bisa terbaca sebagai larangan mengubah `APP_LOCALE` pada `.env` yang sudah terlanjur ada |
| 4 | + subbagian **"Kenapa `APP_LOCALE` mudah terlewat"** — gejalanya, dan `php artisan config:clear` setelah mengubahnya |
| 6 | Perintah pengisian data demo ditulis sebagai keadaan sekarang, bukan "nanti setelah `DatabaseSeeder` selesai". Seeder-nya sudah selesai |
| 12 | + `keputusan-m1-tahap2.md` masuk daftar. Jumlah dokumen jadi enam, dan rujukan "Yang terakhir" diganti nama berkasnya supaya tidak ikut bergeser |

---

## Perubahan dari v1.3

| Bagian | Perubahan |
|---|---|
| 12 | Dokumen tim sekarang ada di folder `docs/` di dalam repo, bukan dibagikan terpisah. Nama filenya tidak lagi memuat nomor versi — versinya ada di dalam file, dan rujukan antar-dokumen jadi stabil |
| 12 | + `keputusan-m1-tahap1.md` masuk daftar |
| 13 | + cara menunjuk folder `docs/` ke AI coding tool |

---

## Perubahan dari v1.1

Satu perubahan, semuanya di **bagian 12**: daftar dokumen rujukan diperbarui — `Dasar-Proyek` naik ke v1.5, dan `Pembagian-Modul-dan-Urutan-Kerja` (v1.2) masuk daftar. Tidak ada langkah setup yang berubah, jadi kalau aplikasimu sudah jalan, tidak ada yang perlu diulang.

*(v1.2 tidak diedarkan — nomor versi yang dirujuknya keburu berubah.)*

---

## Perubahan dari v1.0 → v1.1

Kalau kamu sudah pernah menjalankan panduan v1.0, baca daftar ini juga.

| Bagian | v1.0 | v1.1 |
|---|---|---|
| **6** | "JANGAN jalankan `php artisan migrate`" | **Terbalik** — migration sudah ada di `main`, sekarang wajib dijalankan. Langkahnya jadi bagian 6 yang baru |
| **4** | `APP_TIMEZONE` di `.env` dianggap cukup | + penjelasan bahwa `config/app.php` juga harus menjemputnya, dan cara memverifikasi |
| **0** | PHP 8.3+, MySQL 8.0+ | + catatan versi yang sudah terbukti jalan, + peringatan soal MySQL versi baru |
| **8** | "Jangan pernah push langsung ke `main`" | + satu pengecualian tertulis untuk commit fondasi oleh PM |
| **12** | — | Bagian baru: dokumen rujukan yang harus dibaca sebelum ngoding |

---

## 0. Yang harus sudah ada di komputermu

| Syarat | Versi minimum | Cara cek |
|---|---|---|
| PHP | **8.3+** | `php -v` |
| Composer | 2.x | `composer -V` |
| MySQL / MariaDB | MySQL 8.0+ atau MariaDB 10.6+ | `mysql --version` |
| Git | apa saja | `git --version` |

**Bebas mau pakai XAMPP, Laragon, Herd, atau instalasi manual** — tidak perlu seragam dengan yang lain. Yang penting versinya memenuhi. Komputer PM memakai Herd Lite dengan PHP 8.5 dan MySQL 9.7, dan itu berjalan normal.

**Node.js / npm TIDAK dibutuhkan sama sekali.** Proyek ini tanpa build pipeline.

Cek juga extension PHP ini aktif:

```bash
php -m
```

Harus ada: `pdo_mysql`, `fileinfo`, `mbstring`, `openssl`, `curl`, `zip`, `tokenizer`, `xml`.
`fileinfo` yang paling sering mati dan paling penting — dipakai validasi upload foto laporan.

**Catatan soal versi MySQL.** Boleh berbeda antar anggota, tapi **lapor di grup versi berapa yang kamu pakai.** File `.sql` yang dikumpulkan nanti dibuat dari salah satu komputer, dan dump dari MySQL versi baru bisa gagal di-import di MySQL versi lebih lama — termasuk di komputer penguji. Ini diurus menjelang pengumpulan, tapi datanya dikumpulkan sekarang.

---

## 1. Clone repo

```bash
git clone https://github.com/PPK-06/reservasi-fasilitas-kampus.git
cd reservasi-fasilitas-kampus
```

---

## 2. Install dependency

```bash
composer install
```

Ini membaca `composer.lock` dan mengunduh folder `vendor/` (berisi Laravel dan seluruh package). Folder itu sengaja tidak ikut di git karena bisa dibangun ulang dari file lock — makanya langkah ini wajib.

**Jangan jalankan `npm install`.** Tidak ada `package.json` di proyek ini.

---

## 3. Buat database

Masuk ke MySQL:

```bash
mysql -u root -p
```

Lalu:

```sql
CREATE DATABASE reservasi_fasilitas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit
```

Charset `utf8mb4` bukan opsional — itu ketentuan skema proyek. Jangan pakai `utf8` biasa.

---

## 4. Siapkan `.env`

```bash
copy .env.example .env
php artisan key:generate
```

(Di Mac/Linux: `cp .env.example .env`)

Lalu **buka `.env` dan isi `DB_PASSWORD`** sesuai password MySQL di komputermu. Kalau kosong (umumnya XAMPP), biarkan kosong.

Kelima baris berikut sudah diputuskan tim dan **harus sama persis di semua komputer**:

```
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
CACHE_STORE=file
```

**Baris di luar daftar ini jangan diubah.** Sebaliknya, kalau `.env`-mu sudah terlanjur ada dan salah satu dari kelima baris di atas bernilai lain, **ubah supaya sama** — larangan itu berlaku untuk baris di luar daftar, bukan untuk daftar ini. `.env.example` sudah memakai nilai yang benar, tapi ia tidak menimpa `.env` yang sudah ada.

`SESSION_DRIVER`, `QUEUE_CONNECTION`, dan `CACHE_STORE` adalah alasan kenapa database proyek ini hanya berisi lima tabel. Kalau salah satunya diubah ke `database`, Laravel akan mencari tabel yang tidak pernah kita buat dan aplikasi error di halaman mana pun.

### Kenapa `APP_LOCALE` mudah terlewat

Baru di v1.5. Dasarnya **D17 `keputusan-m1-tahap2.md`**: pesan validasi Bahasa Indonesia disediakan lewat berkas `lang/id` untuk seluruh proyek. Berkas itu hanya dipakai Laravel kalau locale-nya `id`.

Gejala kalau nilainya masih `en`: **pesan validasi tetap Bahasa Inggris walau `lang/id` sudah ada, dan tidak ada error apa pun.** Berkas lang-nya ada, isinya benar, tidak ada yang gagal — mudah disangka berkas lang-nya yang tidak bekerja, padahal `.env`-mu yang belum disetel.

Sifatnya sama persis dengan `APP_TIMEZONE` di bawah: wajib seragam, tidak ikut git, dan gagal tanpa gejala. `.env.example` sudah memakai `id`, tapi `.env` yang sudah terlanjur ada **tidak akan tertimpa olehnya** — itu sebabnya perubahannya harus manual.

Setelah mengubahnya:

```bash
php artisan config:clear
```

Verifikasi cepat: buka `/register`, tekan Daftar dengan form kosong. Pesannya harus berbunyi "Kolom Nama wajib diisi.", bukan "The name field is required."

### Kenapa `APP_TIMEZONE` paling kritis

Kalau nilainya salah, seluruh validasi waktu reservasi bergeser 7 jam dan aturan H-1 salah hitung. Gejalanya **tidak berupa error** — pengajuan yang seharusnya sah ditolak, dan tidak ada pesan yang menunjukkan sebabnya.

Yang perlu kamu tahu: nilai di `.env` hanya berpengaruh kalau `config/app.php` menjemputnya.

```php
'timezone' => env('APP_TIMEZONE', 'UTC'),
```

Baris itu **sudah ada di repo**, jadi kamu tidak perlu mengubah apa pun. Tapi kalau suatu saat jam reservasi terasa bergeser, periksa baris ini lebih dulu — ini pernah terjadi dan butuh waktu lama untuk ditemukan.

Verifikasi cepat:

```bash
php artisan tinker
>>> now()
```

Harus menampilkan jam dinding kamu sekarang dengan penanda `Asia/Jakarta (+07:00)`, bukan UTC. Keluar dengan `exit`.

---

## 5. Atur `php.ini`

Cari file php.ini yang benar-benar dipakai PHP CLI:

```bash
php --ini
```

Lihat baris **"Loaded Configuration File"** — itu file yang harus kamu edit. Jangan asal buka php.ini lewat menu Config di XAMPP Control Panel; itu sering file yang berbeda dan perubahanmu tidak akan terpakai.

Pastikan dua nilai ini (tambahkan kalau belum ada):

```ini
upload_max_filesize=3M
post_max_size=12M
```

Verifikasi benar-benar terpakai:

```bash
php -i | findstr "upload_max_filesize post_max_size"
```

(Mac/Linux: ganti `findstr` dengan `grep -E`)

Kalau nilainya masih 2M/8M, berarti kamu mengedit file yang salah. Upload foto laporan akan gagal diam-diam tanpa pesan error yang jelas.

---

## 6. Jalankan migration

**Berubah total di v1.1.** Panduan lama melarang langkah ini karena skema belum final. Skema sekarang sudah final dan kelima migration sudah ada di `main`.

```bash
php artisan migrate
```

Harus muncul lima baris DONE, urut:

```
create_users_table ............. DONE
create_facilities_table ........ DONE
create_reservations_table ...... DONE
create_reports_table ........... DONE
create_report_photos_table ..... DONE
```

Verifikasi tabelnya benar-benar terbentuk:

```bash
php artisan db:table reservations
```

Perhatikan `start_time` dan `end_time` harus bertipe **datetime**, bukan timestamp. Kalau ternyata timestamp, ada yang salah — lapor di grup, jangan diperbaiki sendiri.

**Kalau migrate gagal**, tempelkan pesan errornya di grup. Jangan mengedit file migration untuk memperbaikinya.

### Aturan yang tidak boleh dilanggar

**Jangan pernah mengedit file migration yang sudah ada di `main`.** Database anggota lain sudah menjalankannya; mengedit isinya membuat database mereka berbeda tanpa ada yang sadar, dan tidak akan ada error yang memberi tahu. Perubahan skema berikutnya lewat **migration baru**, dan dibahas di grup dulu.

`DatabaseSeeder` sudah selesai, jadi perintah yang dipakai sekarang **bukan lagi `php artisan migrate` di atas**, melainkan:

```bash
php artisan migrate:fresh --seed
```

Ia menghapus seluruh tabel, menjalankan ulang kelima migration, lalu mengisi data demo: sembilan akun, dua belas fasilitas, delapan belas reservasi, delapan laporan, dan lima belas foto laporan. Aman dijalankan berulang kali selama kamu tidak keberatan data lokalmu terhapus — memang itu gunanya.

Syaratnya folder `database/seeders/sample-photos/` ikut ter-pull. Kalau kosong atau tidak ada, seeder berhenti dengan `RuntimeException` sebelum menulis satu baris pun, dan pesan errornya menyebutkan apa yang harus dilakukan.

---

## 7. Jalankan aplikasi

```bash
php artisan serve
```

Buka `http://127.0.0.1:8000`.

**Berhasil kalau:** muncul navbar gelap dan kotak hijau bertuliskan "Setup berhasil" beserta versi Laravel dan PHP.

Hentikan server dengan Ctrl+C.

---

## 8. Mulai kerja: branch

Nama branch sudah ditentukan per modul. Cari punyamu:

| Modul | Branch | Halaman yang dikerjakan |
|---|---|---|
| Auth & Akun | `feature/auth-akun` | P3, P4, A3, A4, A5 |
| Fasilitas | `feature/fasilitas` | P1, P2, A1, A2, O6 |
| Reservasi | `feature/reservasi` | U1, U2, U3, O1, O2, O3 |
| Laporan & Rekap | `feature/laporan-rekap` | U4, U5, U6, O4, O5, A6 |

Kode halaman (P1, U1, dst) mengacu ke dokumen `halaman-navigasi-dan-skema.md`.

Alur kerjanya:

```bash
git checkout main
git pull
git checkout -b feature/nama-modulmu
```

Kerja, commit sesering mungkin, lalu:

```bash
git push origin feature/nama-modulmu
```

Buka **Pull Request** di GitHub ke `main`. Tunggu minimal satu orang lain melihat sebelum merge.

### Jangan push langsung ke `main`

**PR bukan formalitas** — itu jejak kontribusi per orang yang akan ditunjuk saat presentasi UTS.

**Satu pengecualian, dan hanya berlaku untuk PM:** commit fondasi yang memblokir semua orang — setup awal Laravel, kelima migration, `DatabaseSeeder` bersama, dan dokumen tim. Ketiganya tidak bisa menunggu review karena tiga orang lain tidak dapat mulai apa pun sebelum ada. Setiap kali dipakai, diumumkan di grup.

Pengecualian ini **berakhir begitu pembagian modul dimulai.** Setelah itu seluruh perubahan kode lewat PR tanpa kecuali, termasuk milik PM.

---

## 9. Format commit message

```
<tipe>: <ringkasan singkat, imperatif>
```

| Tipe | Untuk |
|---|---|
| `feat` | Fitur baru |
| `fix` | Perbaikan bug |
| `style` | Tampilan/CSS tanpa ubah logika |
| `refactor` | Rapikan kode tanpa ubah perilaku |
| `docs` | Dokumen |
| `chore` | Setup, config, hal teknis |

Contoh:

```
feat: tambah form ajukan reservasi (U1)
fix: validasi slot 30 menit tidak jalan di server
style: rapikan tabel antrian petugas (O2)
```

Sertakan kode halaman kalau relevan — memudahkan menyusun bagian "pembagian tugas" di dokumen Word nanti.

**Jangan pakai tag `Co-authored-by:`.** Tiap orang kerja di branch sendiri; co-author bikin kontribusi jadi ambigu, padahal itu yang dinilai.

---

## 10. Aturan yang tidak boleh dilanggar

- **Jangan edit file migration yang sudah di-push.** Perubahan skema lewat migration baru, dan dibahas di grup dulu.
- **Jangan commit `.env`.** Sudah masuk `.gitignore`, jangan dipaksa masuk.
- **`.env.example` selalu diperbarui** setiap ada konfigurasi baru. File ini yang jadi dasar "informasi setting" saat pengumpulan.
- **Jangan pasang starter kit, React, Vue, Livewire, Inertia, atau Tailwind.** Sudah diputuskan: Blade + Bootstrap 5.3 via CDN, titik.
- **Jangan jalankan `npm install` atau menambahkan `package.json`.** Build pipeline bawaan installer sudah sengaja dibuang dari repo ini.
- **Jangan tambah dependency Composer** tanpa dibahas di grup dulu.
- **Jangan menulis URL literal di Blade.** Selalu `route('nama.route')` — lihat dokumen route bagian 4.
- **Sering `git pull` dari `main`** ke branch-mu. `routes/web.php` dan `layouts/app.blade.php` disentuh semua orang — konflik kecil yang sering jauh lebih mudah daripada satu konflik besar di akhir.
- **Di `routes/web.php`, hanya tambah di blok modulmu sendiri.** Jangan merapikan blok orang lain; itu penyebab konflik merge paling sering.

---

## 11. Hal yang wajar, bukan error

**Warning `intl` di setiap perintah artisan.** Kalau kamu pakai Herd Lite, akan muncul:

```
Warning: PHP Startup: Unable to load dynamic library 'intl'...
```

Abaikan. Extension itu tidak dipakai proyek ini, dan perintahnya tetap berjalan sampai selesai. Kalau kamu pakai XAMPP, warning ini tidak muncul sama sekali.

**Warning CRLF saat `git add`.** Soal perbedaan akhir baris Windows vs Linux. Git menanganinya otomatis, aman diabaikan.

**Index bernama `..._foreign` di `report_photos`.** Dibuat MySQL sendiri karena InnoDB mewajibkan setiap kolom foreign key punya index. Bukan buatan kita, dan tidak perlu dihapus.

---

## 12. Dokumen yang harus kamu baca

Enam dokumen di folder **`docs/`** di dalam repo, jadi ikut ter-clone dan ikut terbarui saat kamu `git pull`. Bacanya sesuai kebutuhan, bukan sekaligus.

| Dokumen | Isi | Kapan dibaca |
|---|---|---|
| `dasar-proyek.md` | Aturan bisnis dan keputusan teknis yang mengikat | Sebelum mulai — minimal bagian yang menyangkut modulmu |
| `pembagian-modul-dan-urutan-kerja.md` | Siapa memegang modul apa, urutan kerja, **file mana yang tidak boleh disentuh sembarangan** | Sebelum mulai — bagian 1 dan 4 |
| `halaman-navigasi-dan-skema.md` | 22 halaman, peta navigasi, spesifikasi tabel, ERD | Saat merancang halamanmu |
| `route-dan-kontrak-form.md` | Nama route, konvensi penamaan, aturan validasi tiap form | **Setiap hari saat ngoding** |
| `keputusan-m1-tahap1.md` | Perilaku middleware role dan alur login, beserta alasannya | Saat menulis blok route modulmu, atau saat heran kenapa kamu dilempar ke halaman login |
| `keputusan-m1-tahap2.md` | Keputusan M1 tahap 2: bentuk halaman A3, bahasa pesan validasi, dan catatan pembagian aksi status akun di A5 | Saat menulis Form Request modulmu, atau saat pesan validasimu muncul dalam Bahasa Inggris |

`route-dan-kontrak-form.md` paling sering dibuka. Nama route di situ final, dan kamu akan memakai nama route milik modul orang lain (tombol di P2 mengarah ke U1 dan U4) — jangan menunggu modul itu jadi, nama route-nya sudah bisa dipakai sekarang.

**Nama file tidak memuat nomor versi.** Versinya ada di baris "Versi dokumen" di dalam tiap file, dan riwayatnya dijaga git. Ini disengaja supaya rujukan antar-dokumen tidak basi tiap kali salah satunya naik versi.

Kalau ada yang terasa keliru di salah satu dokumen, **bahas di grup.** Keputusan boleh berubah, tapi harus berubah untuk semua orang sekaligus lewat versi dokumen baru.

---

## 13. Opsional: AI coding tool

Repo ini sudah berisi guidelines Laravel 13 dari Laravel Boost (`CLAUDE.md`, `AGENTS.md`, `.claude/skills/`).

- **Pakai Claude Code?** Sudah siap pakai, tidak perlu apa-apa.
- **Pakai Cursor / Copilot / lainnya?** Jalankan `php artisan boost:install` lalu pilih tool-mu. File-nya ditambahkan berdampingan, tidak menimpa punya orang lain. `AGENTS.md` juga sudah dibaca banyak tool secara default.
- **Tidak pakai AI sama sekali?** Tidak masalah, file-file itu diabaikan saja.

Kalau kamu pakai AI, **tunjuk folder `docs/` sebagai konteks.** Kelima dokumen di bagian 12 ada di situ dan ikut ter-clone, jadi kamu tidak perlu mengunggahnya satu per satu tiap sesi. Tanpa konteks itu, AI akan mengarang asumsi sendiri yang berbeda dari asumsi anggota tim lain — dan itu masalah utama yang dokumen-dokumen tersebut cegah.

Dua kalimat yang layak kamu tempel di tiap sesi, karena keduanya paling sering dilanggar AI:

- *"Isi `docs/` adalah keputusan yang mengikat, bukan usulan. Jangan menawarkan konvensi atau arsitektur alternatif untuk hal yang sudah diputuskan di situ."*
- *"Kalau ada kebutuhan yang tidak tercakup dokumen, tanyakan ke saya, jangan mengarang asumsi."*

Yang kedua bunyinya sama dengan catatan "Untuk AI/asisten coding" di `route-dan-kontrak-form.md`.

Satu pengingat dari dokumen tim: **UTS-nya adalah tanya jawab 10–15 menit tentang kode kalian sendiri.** Pakai AI untuk cari dokumentasi, cek skema, dan perbaiki error. Hindari meminta AI membangun fitur utuh yang lalu tidak bisa kamu bedah sendiri saat ditanya dosen.

---

## Ringkasan cepat (untuk yang sudah paham)

```bash
git clone https://github.com/PPK-06/reservasi-fasilitas-kampus.git
cd reservasi-fasilitas-kampus
composer install
mysql -u root -p -e "CREATE DATABASE reservasi_fasilitas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
copy .env.example .env
php artisan key:generate
# isi DB_PASSWORD di .env
# pastikan php.ini: upload_max_filesize=3M, post_max_size=12M
php artisan migrate
php artisan serve
```

Jangan `npm install`. Jangan push ke `main`. Jangan edit migration yang sudah ada.
