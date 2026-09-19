# Pembagian Modul & Urutan Kerja
## Sistem Reservasi & Pelaporan Fasilitas Kampus

**Mata kuliah:** Pengembangan Platform Khusus (PPK)
**Versi dokumen:** 1.3 — 18 September 2026

**Dokumen pendamping:**
- `dasar-proyek.md` — aturan bisnis dan keputusan teknis
- `halaman-navigasi-dan-skema.md` — halaman, navigasi, skema, ERD
- `route-dan-kontrak-form.md` — route, konvensi penamaan, kontrak form
- `onboarding-tim.md` — setup lokal dan git workflow
- `keputusan-m1-tahap1.md` — keputusan perilaku middleware role dan alur login

Ketiga dokumen pertama menjawab **apa yang dibangun**. Dokumen ini menjawab **siapa mengerjakan yang mana, dalam urutan apa, dan file mana yang tidak boleh disentuh sembarangan**.

Dokumen ini **tidak memuat tanggal**. Urutannya yang mengikat, bukan kalendernya.

---

## Perubahan dari v1.2

Perubahan lahir dari pengerjaan tahap 1 dan dari tiga kebocoran protokol merge yang ditemukan saat itu.

| Bagian | Perubahan |
|---|---|
| Header | Nama file dokumen tidak lagi memuat nomor versi. Versi ada di dalam file. Rujukan antar-dokumen jadi stabil dan tidak perlu diperbarui tiap kali versi naik |
| 2 | Perilaku middleware role sudah ditetapkan. Isinya dipindahkan ke `keputusan-m1-tahap1.md`, dengan ringkasan dan satu batasan yang diketahui |
| 3 | Tahap 1 ditandai selesai. Ditambahkan subbagian **"Yang perlu diketahui sebelum tahap 2"** — berisi hal yang berubah di `main` selama tahap 1 dan memengaruhi pekerjaan semua orang |
| 4 | + baris `bootstrap/app.php`. Pemiliknya Elang. Muncul karena pendaftaran alias middleware dan `redirectUsersTo` ada di file itu |
| 6 | Syarat merge ditambah satu baris. PR yang isinya layout, komponen, atau seeder tidak punya "halaman yang ditambahkan", sehingga lolos gerbang tanpa pernah dibuktikan |

---

## Perubahan dari v1.0

Semua perubahan ada di **bagian 4**. Kalau kamu sudah membaca v1.0, baca daftar ini saja.

| Perubahan | Isi |
|---|---|
| + baris `AppServiceProvider.php` | Pemiliknya Elang. Muncul karena dua baris wajib F11 harus punya satu penanggung jawab |
| Baris "Registrasi Policy" diganti | Laravel menemukan policy otomatis lewat konvensi nama, jadi umumnya **tidak ada yang perlu didaftarkan** |
| Baris "Model" ditambah rujukan | Bentuk dan isi `$fillable` mengikuti F10 |

*(v1.1 tidak diedarkan; barisnya soal `$fillable` sempat keliru dan langsung dikoreksi jadi v1.2 — lihat catatan revisi di F10.)*

---

## 0. Pembagian

Pembagian per modul (irisan vertikal), bukan per layer. Setiap orang mengerjakan controller sampai view untuk modulnya sendiri, sesuai permintaan dosen bahwa semua anggota terlibat di backend dan frontend.

| Modul | Halaman | Route | Pemegang |
|---|---|---|---|
| **M1 — Auth & Akun** | P3, P4, A3, A4, A5 | 14 | **Elang** (PM) |
| **M2 — Fasilitas** | P1, P2, O6, A1, A2 | 10 | **Ferdy** |
| **M3 — Reservasi** | U1, U2, U3, O1, O2, O3 | 11 | **Dhimas** |
| **M4 — Laporan** | U4, U5, U6, O4, O5 | 7 | **Fazl** |
| **M5 — Rekap & Export** | A6 | 2 | **Fazl** |
| **`DatabaseSeeder`** | — | — | **Elang** |

Total 22 halaman, 45 route (44 di tabel + satu redirect `/` → `/facilities`).

**Jumlah route bukan ukuran kesulitan.** M1 punya route terbanyak tapi paling repetitif — lima aksi PATCH mengikuti satu matriks transisi yang sama. M5 punya route paling sedikit tapi harus menerjemahkan tiga definisi metrik jadi query yang benar.

Selain modulnya, **Elang bertanggung jawab atas merge, review PR, dan revisi dokumen tim.**

---

## 1. Lingkup tiap modul

Yang tertulis di sini adalah **batas kepemilikan**, bukan spesifikasi. Spesifikasinya ada di tiga dokumen pendamping.

### M1 — Auth & Akun · Elang

| | |
|---|---|
| **Halaman** | P3 Login · P4 Registrasi · A3 Daftar Akun · A4 Form Tambah Akun · A5 Detail Akun |
| **Controller** | `AuthController`, `RegisterController`, `Admin\UserController` |
| **Model** | `User` |
| **Aturan mengikat** | C1–C6, H1 (percabangan landing setelah login) |
| **Komponen bersama** | Middleware role |

**Titik rawan:** matriks transisi status C2. Lima aksi (`verify`, `reject`, `suspend`, `activate`, `reset-password`) memetakan langsung ke matriks itu, dan **setiap method wajib memeriksa status asal di server.** Tombol yang tidak ditampilkan di Blade bukan penjaga.

A4 punya field bersyarat: `identity_number` dan `user_type` hanya untuk role `pengguna`, divalidasi `required_if:role,pengguna` — bukan disembunyikan JavaScript.

### M2 — Fasilitas · Ferdy

| | |
|---|---|
| **Halaman** | P1 Daftar Fasilitas · P2 Detail + Grid · O6 Kelola Ketersediaan · A1 Daftar Fasilitas (Admin) · A2 Form Fasilitas |
| **Controller** | `FacilityController`, `Officer\FacilityController`, `Admin\FacilityController` |
| **Model** | `Facility` |
| **Aturan mengikat** | D1–D6, F6 (`type` dan `location` VARCHAR + konstanta PHP, bukan ENUM) |
| **Komponen bersama** | Layout induk + navbar |

**Titik rawan:** P2 adalah layar tersulit di seluruh proyek — 26 slot × banyak tanggal, tiga tingkat visibilitas data (F4), tiga status visual slot (D5), plus banner fasilitas dalam perbaikan. **Data grid-nya datang dari M3**, lihat bagian 2.

Dua route status **sengaja dipisah**: `admin.facilities.status` menangani `active` ↔ `inactive`, `officer.facilities.status` menangani `active` ↔ `under_maintenance`. Kalau digabung, petugas memperoleh wewenang milik admin.

`type` dan `location` tidak dijaga database sama sekali — penjaganya konstanta PHP + `Rule::in()` + dropdown di form A2.

### M3 — Reservasi · Dhimas

| | |
|---|---|
| **Halaman** | U1 Form Ajukan · U2 Riwayat Saya · U3 Detail Saya · O1 Dashboard Antrian · O2 Antrian Reservasi · O3 Detail (Petugas) |
| **Controller** | `ReservationController`, `Officer\ReservationController`, `Officer\DashboardController` |
| **Model** | `Reservation` |
| **Aturan mengikat** | A1–A8, F1 (penyimpanan slot), F2 (pencegahan bentrok), F4 (tingkat visibilitas) |
| **Komponen bersama** | Class konstanta `Slot`, **query ketersediaan**, komponen peringatan D4 |

**Modul tersulit.** Tiga hal yang kalau salah, salahnya tidak memunculkan error:

1. **F2** — approve menjalankan transaksi + `lockForUpdate()`. Tanpa InnoDB, kuncinya gagal diam-diam
2. **A8** — status kedaluwarsa diturunkan saat ditampilkan, bukan disimpan. Server **wajib menolak** approve dan reject untuk `pending` yang `start_time`-nya sudah lewat, bukan cuma men-*disable* tombolnya
3. **U1** — tujuh aturan validasi F1 plus A2 dan A3, dan validasi client harus mencerminkannya tanpa jadi sumber kebenaran kedua

U3 dijaga **Laravel Policy** (`ReservationPolicy::view`), bukan `if` di controller.

### M4 — Laporan · Fazl

| | |
|---|---|
| **Halaman** | U4 Form Lapor · U5 Daftar Saya · U6 Detail Saya · O4 Daftar Laporan · O5 Detail (Petugas) |
| **Controller** | `ReportController`, `Officer\ReportController` |
| **Model** | `Report`, `ReportPhoto` |
| **Aturan mengikat** | B1–B5, F5 (lokasi penyimpanan foto) |

**Titik rawan:** unggah 1–3 foto. Batas 1–3 **tidak dijaga database** — hanya validasi aplikasi. Butuh `php.ini` benar (`upload_max_filesize=3M`, `post_max_size=12M`); kalau salah, form gagal tanpa pesan yang jelas.

F5 menetapkan tiga aturan turunan: nama file digenerate `Str::random(40)` bukan nama asli, validasi `mimes:jpg,jpeg,png` + `max:3072`, dan folder upload masuk `.gitignore` tapi foldernya tetap ada lewat `.gitkeep`.

Di O5, kewajiban `resolution_note` **berubah mengikuti status** (B3). O5 juga memicu peringatan D4 lewat aksi tandai fasilitas dalam perbaikan.

U6 dijaga **Laravel Policy**.

### M5 — Rekap & Export · Fazl

| | |
|---|---|
| **Halaman** | A6 Rekap & Export |
| **Controller** | `Admin\RecapController` |
| **Model** | — (membaca `reservations`, `reports`, `facilities`) |
| **Aturan mengikat** | E1–E5 |

**Read-only.** Tidak menulis apa pun, sehingga tidak bisa merusak data modul lain — inilah alasannya aman dikerjakan paling akhir.

Query string `admin.recap.export` **identik** dengan `admin.recap.index`. Itulah yang menjamin export memakai rentang tanggal yang sama persis (E1).

---

## 2. Komponen bersama dan pemiliknya

Lima hal dipakai lintas modul. Kalau tidak ditunjuk pemiliknya, akan ditulis dua kali oleh dua orang lalu bentrok saat merge.

| Komponen | Dipakai di | Pemilik |
|---|---|---|
| Middleware role | Semua route pengguna, petugas, admin | **Elang** (M1) |
| Layout induk + navbar | Semua halaman | **Ferdy** (M2) |
| Class konstanta `Slot` | U1, P2, validasi reservasi | **Dhimas** (M3) |
| **Query ketersediaan** | P2, validasi U1 | **Dhimas** (M3) |
| Komponen peringatan D4 | O5, O6, A1, A5 | **Dhimas** (M3) |

Empat baris pertama sudah ditetapkan dokumen route bagian 7. Baris keempat baru.

### Query ketersediaan — keputusan baru

P2 milik Ferdy, tapi isi grid-nya data reservasi. Kalau dibiarkan, Ferdy menulis query ketersediaan versinya sendiri dan Dhimas menulis versi lain untuk validasi U1 — lalu tampilan grid dan hasil validasi bisa berbeda, dan bedanya baru ketahuan saat modul digabung.

**Keputusan: query ketersediaan milik Dhimas.** Satu method yang menerima fasilitas dan tanggal, mengembalikan status per slot. Ferdy memanggilnya dari `FacilityController@show` dan me-render hasilnya.

**Ferdy tidak menyentuh tabel `reservations` sama sekali.**

Prinsip di baliknya sama dengan `Slot`: semua yang membaca `reservations` ada di satu orang. Ini juga yang menjaga F4 — query publik hanya mengambil `facility_id`, `start_time`, `end_time` dari reservasi `approved`; `user_id` dan `purpose` tidak di-`select` sama sekali, bukan sekadar tidak dicetak di Blade.

### Prasyarat: bentuk U1 harus diputuskan lebih dulu

**Diputuskan Dhimas dan Ferdy bersama, sebelum Dhimas menulis query ketersediaan.** Ini keputusan pertama yang harus diambil di tahap 2.

Dokumen skema bagian 4 meninggalkan satu pertanyaan terbuka: **U1 memakai dua dropdown slot, atau slot dipilih langsung di grid P2?** Pertanyaan itu terlihat seperti soal tampilan, padahal ia menentukan bentuk method yang menghubungkan dua modul.

| | Dua dropdown | Pilih langsung di grid |
|---|---|---|
| Grid di P2 | Tampilan saja, read-only | Menjadi input |
| Query ketersediaan | Boolean per slot sudah cukup | Hasilnya perlu dikirim juga ke form U1 |
| JavaScript | Tidak perlu | Vanilla JS tulis tangan — proyek ini tanpa build pipeline |
| P2 sebagai halaman publik | Tidak ada masalah | Tamu yang mengklik grid harus diarahkan ke login |
| Beban Ferdy | Kecil | Bertambah, dan bergantung pada bentuk form Dhimas |

Kalau query ketersediaan ditulis dengan asumsi dropdown lalu belakangan diputuskan grid interaktif, method-nya ditulis ulang — dan P2 sudah terlanjur dibangun di atasnya.

**Keputusannya ditulis di grup dan masuk versi berikutnya dokumen ini.**

### Aturan navbar

Ferdy menulis navbar **lengkap di awal**, termasuk link ke route yang belum dibuat:

| Role | Isi navbar |
|---|---|
| Tamu | P1 Daftar Fasilitas · Login |
| Pengguna | P1 · U1 Ajukan Reservasi · U2 Riwayat Reservasi · U4 Lapor Kerusakan · U5 Daftar Laporan · Logout |
| Petugas | O1 Dashboard · O2 Antrian Reservasi · O4 Daftar Laporan · O6 Kelola Ketersediaan · Logout |
| Admin | A1 Daftar Fasilitas · A3 Daftar Akun · A6 Rekap · Logout |

Semua memakai `route('nama.route')` dari Bagian II dokumen route. Link akan error sampai modulnya jadi — **itu wajar dan justru berguna**, karena salah ketik nama route ketahuan hari itu juga.

Setelah navbar lengkap, **tiga orang lain tidak menyentuh file ini sama sekali.**

### Tiga contoh komponen, di commit yang sama

Begitu layout masuk `main`, konvensi tampilan sudah terbentuk apakah direncanakan atau tidak — tiga orang akan meniru apa pun yang ada di situ.

Karena itu Ferdy menyertakan **tiga contoh** di commit yang sama: satu badge status, satu tabel daftar, satu modal konfirmasi. Bukan dokumen panduan, cuma kode yang bisa disalin. Tanpa ini, empat orang mengarang konvensinya sendiri dan hasilnya baru dirapikan di minggu terakhir.

### Keputusan yang menyertai middleware role

Middleware tidak hanya memeriksa role, tapi juga **status akun**. Kalau admin men-suspend akun yang sedang login, session-nya masih hidup — pengecekan saat login saja tidak cukup.

**Perilakunya sudah ditetapkan.** Rinciannya beserta alasan tiap pilihan ada di `keputusan-m1-tahap1.md`. Ringkasnya:

| | Perilaku |
|---|---|
| Urutan pemeriksaan | Status akun dulu, baru role |
| Status yang lolos | Hanya `verified`. Allow-list, bukan deny-list |
| Status tidak lolos | Logout penuh, session dihancurkan, dilempar ke P3 dengan pesan spesifik C2 |
| Role tidak cocok | `abort(403)` |

Alias-nya `role`, dipakai sebagai `role:pengguna`, `role:petugas`, `role:admin`, sesuai bagian 7 dokumen route. Kerangka ketiga grup sudah ada di `routes/web.php` — **isi blok masing-masing di dalam grup yang sudah ada, jangan membuat grup baru.**

**Satu batasan yang diketahui.** Pemeriksaan status hanya berjalan di route yang memasang middleware `role`. P1 dan P2 adalah halaman publik tanpa middleware itu, jadi akun yang di-suspend masih bisa membukanya sampai ia menyentuh halaman ber-role. Bukan lubang keamanan — tidak ada aksi yang bisa ia lakukan di sana — tapi perlu diketahui supaya tidak dikira bug saat pengujian.

---

## 3. Urutan kerja

Empat tahap. Tahap 2 adalah titik di mana empat orang berjalan paralel penuh.

### Tahap 0 — Model

**Semua orang, sekaligus.** Migration sudah selesai, tapi tabel bukan model. Tanpa model, route model binding tidak resolve dan tidak ada controller yang bisa ditulis.

| Orang | Model |
|---|---|
| Elang | `User` |
| Ferdy | `Facility` |
| Dhimas | `Reservation` |
| Fazl | `Report`, `ReportPhoto` |

**Relasi ditulis lengkap sekalian**, dua arah, sesuai dokumen skema bagian 7:

| Model | Relasi |
|---|---|
| `User` | `reservations()`, `reports()` |
| `Facility` | `reservations()`, `reports()` |
| `Reservation` | `user()`, `facility()` |
| `Report` | `user()`, `facility()`, `photos()` |
| `ReportPhoto` | `report()` |

Alasannya ada di bagian 4: relasi bersifat dua arah, jadi kalau tidak ditulis lengkap di awal, dua orang akan menambah method di file milik orang ketiga.

Pekerjaan ini kecil — tinggal menyalin dari dokumen skema.

### Tahap 1 — Membuka kunci

**Status: bagian Elang selesai. Bagian Ferdy selesai sebagian** — layout, navbar, dan tiga contoh komponen sudah di `main`, tapi `facilities.index` yang dipanggil link brand belum terdaftar, sehingga setiap halaman yang `@extends('layouts.app')` masih error 500. Lihat subbagian di bawah.

Dua hal, **paralel satu sama lain**, tapi keduanya mendahului tahap 2.

| Orang | Kerjakan | Kenapa mendahului |
|---|---|---|
| **Elang** | Middleware role, lalu **P3 Login** | Tiga dari empat modul seluruhnya di balik autentikasi. Tanpa login, Dhimas dan Fazl bisa menulis kode tapi tidak bisa membuka satu halamannya pun untuk dicek |
| **Ferdy** | **Layout + navbar lengkap** | Tanpa layout, tiga orang membuat halaman dengan HTML seadanya lalu merapikannya dua kali |

**Ferdy tidak menyentuh P1/P2/A1/A2/O6 sebelum layout selesai.** Naluri semua orang adalah mengerjakan modulnya sendiri dulu; di kasus ini naluri itu salah, karena biaya keterlambatannya ditanggung tiga orang lain.

Dhimas dan Fazl **tidak menganggur di tahap ini** — lihat tahap 2, sebagian pekerjaan mereka tidak butuh login.

**Klausul ambil alih:** kalau tahap 1 belum selesai sementara yang lain sudah siap paralel, **Elang mengambil alih komponen tersebut.** Disepakati sekarang, bukan saat kejadian. Ini hanya berlaku untuk komponen bersama, tidak untuk modul — Elang tidak mengambil alih modul siapa pun.

### Yang perlu diketahui sebelum tahap 2

Enam hal berubah di `main` selama tahap 1. Semuanya memengaruhi pekerjaan lebih dari satu orang.

**1. Mass assignment sekarang berbunyi.** Dua baris wajib F11 sudah aktif. Kolom yang tidak ada di `#[Fillable]` tidak lagi dibuang diam-diam — Eloquent melempar `MassAssignmentException`. Itu perilaku yang diinginkan. Kalau kena, periksa `#[Fillable]` modelmu terhadap kontrak formnya di Bagian III dokumen route, **jangan matikan penjaganya**.

**2. Kolom yang tidak fillable tidak bisa diisi lewat `create()`.** Konsekuensi langsung dari poin 1, dan ini mengenai setiap orang yang menetapkan kolom di controller — `users.status`, dan kolom lain yang serupa di model masing-masing. Polanya:

```php
$user = new User($request->validated());
$user->status = 'pending';
$user->save();
```

Bukan `User::create($request->validated() + ['status' => 'pending'])`, yang akan melempar exception.

**3. Pemeriksaan F10 untuk `Reservation`, `Report`, dan `ReportPhoto` masih terutang.** Momennya sekarang paling baik justru karena poin 1 — salah isi `#[Fillable]` sekarang berbunyi, bukan diam. Pemegang tiap model memeriksa modelnya sendiri lalu lapor di grup.

**4. `php artisan migrate:fresh --seed` sekarang berhasil.** Sebelumnya tidak pernah, karena `UserFactory` bawaan Laravel masih mengisi `email_verified_at` yang sudah dihapus dari migration (C1/F8). Jalankan sekali setelah `git pull`. Seeder saat ini **baru berisi akun** — fasilitas, reservasi, dan laporan menyusul, lihat bagian 5.

**5. Login sudah jalan**, beserta pemeriksaan status dan percabangan landing H1. Tujuh akun demo tersedia dengan password seragam.

**6. Route navbar yang belum terdaftar menahan render, bukan cuma mematikan link.** Ini yang paling mudah disalahpahami. `route('nama')` untuk nama yang belum terdaftar melempar `RouteNotFoundException`, bukan menghasilkan link kosong — jadi **seluruh halaman** gagal dirender, termasuk halaman yang tidak ada hubungannya dengan link itu. Navbar memanggil 15 nama yang belum ada.

Dua cara menghadapinya, dan keduanya sah:

- Daftarkan route-mu lebih awal walau controller-nya masih kosong. Ini yang paling menolong orang lain
- Untuk menguji halamanmu sendiri sementara route orang lain belum ada, pasang route stub di `routes/web.php` **lalu buang sebelum commit**. Pasang hanya di atas working tree yang sudah bersih, supaya `git checkout -- routes/web.php` cukup untuk membuangnya

Yang **tidak** boleh: mendaftarkan route milik modul orang lain ke `main`, atau mengubah navbar supaya link-nya hilang.

### Tahap 2 — Paralel penuh

Empat orang jalan bersamaan, masing-masing di modulnya.

| Orang | Kerjakan |
|---|---|
| Elang | A3, A4, A5 · P4 Registrasi |
| Ferdy | P1, A1, A2, O6 |
| Dhimas | **Putuskan bentuk U1 bersama Ferdy** → class `Slot` → query ketersediaan → U1, U2, U3 → O1, O2, O3 |
| Fazl | U4, U5, U6 → O4, O5 |

**Class `Slot` dan query ketersediaan didahulukan Dhimas**, karena Ferdy menunggu keduanya untuk P2.

**Tiga route didahulukan karena menahan render halaman orang lain**, bukan karena halamannya penting:

| Route | Pemilik | Yang tertahan |
|---|---|---|
| `facilities.index` (P1) | Ferdy | Link brand navbar dipanggil tanpa syarat, jadi ini menahan **semua halaman untuk semua role** |
| `officer.dashboard` (O1) | Dhimas | Landing petugas. Tanpa ini, percabangan H1 untuk petugas tidak bisa dibuktikan |
| `admin.users.index` (A3) | Elang | Landing admin, dengan alasan yang sama |

Ketiganya cukup terdaftar dengan controller seadanya untuk melepas orang lain; halamannya sendiri tetap dikerjakan sesuai urutan modul masing-masing.

Dhimas dan Fazl boleh **memulai controller dan Form Request** sebelum login jadi — yang tidak bisa dilakukan hanya membuka halamannya di browser.

### Tahap 3 — Yang bergantung pada tahap 2

| Pekerjaan | Menunggu |
|---|---|
| **P2 Detail Fasilitas + Grid** (Ferdy) | `Slot` dan query ketersediaan dari Dhimas |
| **Komponen peringatan D4** (Dhimas) | Dipakai O5, O6, A1, A5 — disepakati bentuknya sebelum ditulis |
| **`DatabaseSeeder`** (Elang) | Kelima model sudah ada |
| **M5 Rekap & Export** (Fazl) | M4 selesai; datanya dari modul lain |

**`DatabaseSeeder` adalah prasyarat pengujian, bukan prasyarat penulisan.** Tapi Dhimas dan Fazl tidak bisa menguji modulnya dengan benar tanpa itu, jadi jangan ditunda sampai akhir. Isi minimalnya di bagian 5.

---

## 4. File yang disentuh lebih dari satu orang

Di luar daftar ini, setiap file dimiliki satu orang dan tidak disentuh yang lain.

| File | Aturan |
|---|---|
| `routes/web.php` | Setiap orang **hanya menambah di blok modulnya sendiri**, dan **tidak merapikan blok orang lain**. Konflik di file ini paling sering terjadi karena alasan kosmetik |
| `layouts/app.blade.php` | Hanya Ferdy |
| `DatabaseSeeder.php` | Hanya Elang. Permintaan data lewat grup, jangan edit langsung |
| `AppServiceProvider.php` | **Hanya Elang.** Isinya perilaku seluruh aplikasi, bukan kode modul — isi wajibnya ditetapkan F11. Permintaan baris baru lewat grup, jangan edit langsung |
| `bootstrap/app.php` | **Hanya Elang.** Alasannya sama dengan baris di atas: satu file untuk seluruh aplikasi. Isinya pendaftaran alias middleware dan tujuan redirect. Sejak Laravel 11 `app/Http/Kernel.php` dihapus dan seluruh konfigurasi middleware pindah ke sini |
| Model | Ditulis lengkap di tahap 0. Setelah itu **tidak ada yang menyentuh model orang lain**. Bentuk dan isi `$fillable` mengikuti **F10**: attribute `#[Fillable]`, dan isinya ditentukan kontrak form — **pemegang tiap model memeriksa modelnya sendiri lalu lapor di grup** |
| Registrasi Policy | **Umumnya tidak perlu.** Laravel menemukan policy otomatis lewat konvensi nama: `App\Models\Reservation` → `App\Policies\ReservationPolicy`. Selama nama dan lokasinya standar, tidak ada yang didaftarkan. Kalau ternyata butuh registrasi eksplisit, barisnya **diminta ke Elang lewat grup** |

`DatabaseSeeder`, `AppServiceProvider`, dan `bootstrap/app.php` adalah masalah yang sama: satu file untuk seluruh proyek, yang kalau tidak ditunjuk pemiliknya akan diisi empat orang di tempat yang sama.

**Registrasi Policy dulu masuk daftar ini karena alasan serupa**, dengan Dhimas sebagai pendaftar tunggal. Sejak Laravel 11 `AuthServiceProvider` dihapus dan pendaftaran policy pindah ke `AppServiceProvider` — yang berarti aturan lama itu akan bertabrakan dengan baris di atasnya. Auto-discovery membuat tabrakan itu tidak pernah terjadi.

**Dhimas memverifikasi ini saat menulis `ReservationPolicy` pertama** dan melapor di grup. Kalau ternyata auto-discovery tidak jalan, barisnya dikirim ke Elang — bukan diedit sendiri.

---

## 5. `DatabaseSeeder`

**Pemegang: Elang.** Isi minimal:

| Data | Ketentuan |
|---|---|
| Akun | 1 admin, 1–2 petugas, beberapa pengguna yang **mencakup keempat status C2** — tanpa akun `pending` dan `suspended`, A5 tidak bisa diuji |
| Fasilitas | Mencakup semua tipe dan **ketiga status D3**, termasuk minimal satu `under_maintenance` |
| Reservasi | Semua status A7, **plus beberapa `pending` dengan `start_time` sudah lewat** |
| Laporan | Semua status B3, sebagian dengan foto |
| Password | Satu password seragam untuk seluruh akun demo |

**Baris ketiga adalah alasan utama seeder ini ada.** Reservasi pending yang terlewat mustahil dibuat lewat form karena A3 menolak pengajuan untuk masa lalu. Tab "Terlewat" di O2 hanya bisa diuji lewat seeder. **Jangan menonaktifkan validasi A3 sementara demi membuat data uji.**

---

## 6. Protokol merge

Elang bertanggung jawab atas integrasi. Tiga aturan berikut mengurangi bebannya untuk semua orang.

**PR kecil dan sering.** Satu PR = satu halaman atau satu fitur, bukan "modul saya selesai". PR raksasa sulit di-review dan konfliknya paling parah. Jangan menahan PR sampai terasa rapi.

**Rebase sebelum membuka PR, bukan merge.**

```bash
git pull --rebase origin main
```

Konflik jadi urusan pembuat PR di komputernya sendiri, bukan urusan Elang di `main`.

**`main` harus selalu jalan.** Syarat minimal sebelum sebuah PR digabung:

- `php artisan serve` jalan tanpa error
- `php artisan migrate:fresh --seed` berhasil
- Halaman yang ditambahkan bisa dibuka
- **PR yang tidak menambahkan halaman tetap harus dibuktikan.** Layout, komponen, factory, dan seeder tidak punya halaman yang bisa dibuka, sehingga tiga syarat di atas lolos tanpa isinya pernah dijalankan sekali pun. Untuk PR semacam itu, sebutkan di deskripsinya **apa yang kamu jalankan dan apa hasilnya** — satu halaman yang merender layout, satu perintah artisan, satu pemanggilan di `tinker`

Baris terakhir ditambahkan di v1.3 setelah tiga hal lolos gerbang ini tanpa pernah dijalankan: `migrate:fresh --seed` yang ternyata gagal sejak tahap 0, layout yang tidak pernah dirender sehingga error `RouteNotFoundException`-nya tidak ketahuan, dan file contoh komponen yang tidak pernah dipanggil. Ketiganya bukan kecerobohan orang — polanya struktural, dan yang diperbaiki gerbangnya.

**Minimal satu orang lain melihat sebelum merge.** Berlaku juga untuk PR milik PM. Kalau kamu menggabungkan secara manual tanpa membuka PR, jejak review itu hilang — padahal onboarding bagian 8 menyebutnya sebagai jejak kontribusi yang ditunjuk saat presentasi. Pakai `git merge --no-ff` supaya potongan kerjanya tetap terlihat berkelompok di history, dan minta seseorang membaca commit-nya walau lewat grup.

Selebihnya mengikuti `onboarding-tim.md` bagian 8–10: branch per fitur, format commit message, jangan push ke `main`, jangan edit migration yang sudah di-push.

---

## 7. Yang belum dicakup dokumen ini

Disebut supaya tidak dikira terlupakan. Tidak satu pun menghambat tahap 0 sampai 2.

| Hal | Diputuskan kapan |
|---|---|
| **Rancangan empat layar sulit** — P2, O1, O5, A6. Dokumen skema bagian 4 menetapkan bentuknya perlu dipikirkan sebelum dikoding | Saat pemiliknya mulai mengerjakan halaman itu |
| **Panduan komponen UI selebihnya** — format tampilan tanggal, pola paginasi, tampilan state kosong | Menyusul setelah tiga contoh Ferdy ada; ditambahkan saat benar-benar dibutuhkan |
| **Skenario demo** untuk presentasi | Sebelum `DatabaseSeeder` difinalkan, karena data demo harus mendukung alurnya |
| **Jadwal dengan tanggal** | Setelah tahap 2 berjalan dan kecepatan tiap orang terlihat |

**Bentuk U1 tidak lagi ada di daftar ini.** Ia sudah dipindahkan ke bagian 2 sebagai prasyarat tahap 2, karena menentukan kontrak antara M2 dan M3 — bukan sekadar rancangan layar.

Risiko skenario demo rendah: spesifikasi seeder di bagian 5 sudah mensyaratkan seluruh status tercakup, dan itu superset dari kebutuhan demo apa pun.
