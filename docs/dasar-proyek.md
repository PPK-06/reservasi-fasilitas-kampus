# Dasar Proyek — Sistem Reservasi & Pelaporan Fasilitas Kampus

**Mata kuliah:** Pengembangan Platform Khusus (PPK)
**Bentuk:** Proyek besar, dipresentasikan sebagai UTS
**Tenggat pengumpulan:** 11 Oktober 2026, 12.00 WIB via Kulon
**Tim:** 4 mahasiswa
**Versi dokumen:** 1.5 — 17 September 2026 *(keputusan yang muncul saat review PR pertama; F10 dikoreksi sebelum v1.4 sempat diedarkan)*

**Dokumen pendamping:**
- `halaman-navigasi-dan-skema.md` — daftar halaman, peta navigasi, spesifikasi tabel, relasi, index, ERD
- `route-dan-kontrak-form.md` — nama route, konvensi penamaan, aturan validasi tiap form
- `pembagian-modul-dan-urutan-kerja.md` — siapa memegang modul apa, urutan kerja, kepemilikan file

Dokumen ini berisi **aturan**; kedua dokumen pendamping berisi **penerapannya**.

---

## Perubahan dari v1.3

Tiga anggota lain sudah membaca v1.3. Baca daftar ini saja, tidak perlu membaca ulang seluruh dokumen.

Tidak ada aturan bisnis yang berubah. Kedua tambahan di bawah muncul saat **review PR pertama** (layout induk + navbar + perbaikan model `Facility`), yaitu dua pertanyaan yang ternyata belum punya jawaban tertulis: bagaimana `$fillable` ditulis, dan siapa yang memiliki `AppServiceProvider`.

| Bagian | Perubahan |
|---|---|
| **F** | + **F10** bentuk dan isi `$fillable` — attribute `#[Fillable]`, plus aturan penentuan isinya |
| **F** | + **F11** dua penjaga wajib di `AppServiceProvider` |
| **Dokumen pendamping** | + `pembagian-modul-dan-urutan-kerja.md` masuk daftar; kepemilikan file ada di bagian 4 dokumen itu |

**Soal v1.4:** versi itu sempat ditulis dengan F10 yang isinya kebalikan dari yang sekarang — property, bukan attribute — atas dugaan bahwa hanya satu model memakai attribute. Dugaan itu keliru, dan v1.4 dikoreksi jadi v1.5 **sebelum diedarkan**. Kalau kamu tidak pernah menerima v1.4, memang tidak ada yang terlewat.

Alasan tiap perubahan ada di bagiannya masing-masing. Daftar perubahan v1.2 → v1.3 ada di dokumen versi sebelumnya.

---

## Cara memakai dokumen ini

Dokumen ini berisi **keputusan yang sudah diambil** untuk hal-hal yang tidak ditentukan oleh dokumen tugas. Dasarnya adalah Ketentuan Khusus poin 1: *"Silakan tambahkan asumsi ataupun tabel/atribut pada rancangan data jika diperlukan."*

**Untuk anggota tim:** ini rujukan bersama. Kalau kamu menemukan keputusan yang terasa keliru, bahas dengan tim — jangan diam-diam mengerjakan versi yang berbeda. Keputusan di sini boleh berubah, tapi harus berubah untuk semua orang sekaligus, lewat versi dokumen baru seperti yang terjadi pada v1.5 ini.

**Untuk AI/asisten coding yang dipakai anggota tim:** perlakukan isi dokumen ini sebagai keputusan yang mengikat, bukan usulan yang perlu dievaluasi ulang. Jangan menawarkan alternatif arsitektur atau menyarankan pendekatan lain untuk hal yang sudah diputuskan di sini. Kalau sebuah kebutuhan tidak tercakup dokumen ini, **tanyakan ke pengguna**, jangan mengarang asumsi baru — asumsi yang berbeda antar anggota tim adalah masalah utama yang dokumen ini cegah.

---

## 0. Ringkasan sistem

Aplikasi web untuk mengelola penggunaan fasilitas kampus (ruang kelas, aula, laboratorium, alat, lapangan). Dua alur yang menyatu dalam satu sistem:

1. **Reservasi** — pengguna mengecek ketersediaan dan mengajukan pemakaian, petugas memproses
2. **Pelaporan kerusakan** — pengguna melaporkan masalah pada fasilitas, petugas menyelesaikan

Kedua alur beroperasi pada objek yang sama, sehingga saling memengaruhi (lihat D4).

### Aktor

| Aktor | Login | Kemampuan utama |
|---|---|---|
| **Pengunjung** | Tidak | Melihat daftar fasilitas dan ketersediaan per slot, tanpa detail pemohon/tujuan |
| **Pengguna** | Ya | Mengajukan reservasi, membatalkan reservasi sendiri, melihat riwayat, melaporkan kerusakan, melihat status laporan |
| **Petugas** | Ya | Memproses antrian reservasi (approve/reject/cancel), mengubah status laporan + catatan resolusi, menandai fasilitas dalam perbaikan |
| **Admin** | Ya | CRUD fasilitas, rekap lintas fasilitas + export, mendaftarkan akun petugas & pengguna, memverifikasi akun hasil registrasi mandiri |

### Ketentuan waktu (dari dokumen tugas, bukan asumsi)

- Jam operasional **07.00–20.00**
- Slot tetap **30 menit** → **26 slot per hari**
- `start_time` dan `end_time` wajib berada dalam jam operasional dan merupakan kelipatan slot 30 menit
- **Validasi ini wajib ada di sisi server**, bukan hanya di tampilan kalender

---

## 1. Tech stack & lingkungan

| Komponen | Keputusan |
|---|---|
| Framework | **Laravel 13** |
| PHP | **8.3 atau lebih baru** (syarat minimum Laravel 13) |
| Database | **MySQL** — dipilih karena dokumen tugas mewajibkan pengumpulan file `.sql` |
| Storage engine | **InnoDB** — wajib, karena F2 memakai transaksi dan row lock |
| Starter kit | **Tidak dipakai.** Laravel kosong |
| Frontend | **Blade + Bootstrap 5.3 via CDN.** Tanpa React, Vue, Inertia, atau Livewire |
| Build pipeline | **Tidak ada.** Tanpa `npm install`, tanpa `npm run build`, tanpa `node_modules`, tanpa Vite |
| Authentication | **Ditulis sendiri** — tidak memakai Fortify/Breeze/Jetstream |
| Testing | **Pest** (default installer; tidak ada kriteria penilaian yang menyentuh testing) |
| Bantuan AI | **Laravel Boost** dipasang sebagai `--dev` dependency |

### Alasan tidak memakai starter kit dan build pipeline

Bukan karena starter kit jelek, tapi karena tiga hal spesifik proyek ini:

1. Alur registrasi kita tidak standar (status pending + verifikasi admin + field tambahan), jadi auth bawaan tetap harus dibongkar
2. UTS-nya adalah tanya jawab 10–15 menit tentang kode sendiri — kode yang tidak ditulis tim jadi beban, bukan bantuan
3. Tanpa build pipeline, dosen cukup mengatur `.env`, import SQL, lalu `php artisan serve`. Satu kategori kegagalan hilang sepenuhnya

**Catatan dari sesi setup (baru di v1.3):** installer Laravel 13 tetap memasang Vite, Tailwind, dan `package.json` meskipun opsi "tanpa starter kit" dipilih. Ketiganya **sudah dibuang dari repo**. Kalau kamu menemukan `package.json` atau folder `node_modules` muncul di komputermu, berarti ada yang menjalankan perintah npm — lapor di grup, jangan di-commit.

### Konfigurasi lingkungan yang wajib diatur

Empat berkas. Dua di antaranya ikut git, dua lagi tidak.

**`.env`** — tidak ikut git, disalin dari `.env.example`:

```
APP_TIMEZONE=Asia/Jakarta
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
CACHE_STORE=file
```

Tiga baris terakhir **baru ditulis di v1.3**, meski sudah dijalankan sejak sesi setup. Ketiganya menentukan apakah Laravel menyimpan session, antrian job, dan cache di **database atau di file**. Karena semuanya diarahkan ke file dan `sync`, database proyek ini tidak butuh tabel `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, maupun `failed_jobs` — dan itulah yang membuat jumlah tabel tetap lima, sesuai ERD. Lihat F8.

**`php.ini`** — tidak ikut git. Karena batas upload foto 3 MB × maksimal 3 foto (lihat B2), nilai bawaan PHP tidak cukup:

```ini
upload_max_filesize = 3M    ; bawaan 2M
post_max_size = 12M         ; bawaan 8M
```

**`config/app.php`** — ikut git, sudah diperbaiki:

```php
'timezone' => env('APP_TIMEZONE', 'UTC'),
```

**Baru di v1.3, dan ini koreksi terhadap v1.2 yang keliru.** Versi sebelumnya menyebut `APP_TIMEZONE` di `.env` seolah itu langkah terakhir. Ternyata tidak — tanpa baris di atas, `config('app.timezone')` tetap mengembalikan `UTC` meski `.env` sudah benar, dan seluruh perbandingan waktu bergeser 7 jam. Penjelasan lengkap di F3.

**`config/database.php`** — ikut git, sudah diperbaiki. Pada connection `mysql` **dan** `mariadb`:

```php
'engine' => 'InnoDB',
```

Nilai bawaannya `null`, yang berarti "terserah server". Ditegaskan karena F2 memakai `lockForUpdate()` yang **tidak berfungsi di MyISAM dan tidak mengeluarkan error apa pun saat gagal**. Connection `mariadb` ikut diisi karena onboarding membolehkan MariaDB dan XAMPP sebenarnya membawa MariaDB meski menyebutnya MySQL.

Keempatnya **wajib masuk ke bagian "informasi setting yang diperlukan untuk menjalankan program"** di dokumen Word yang dikumpulkan.

### Catatan versi MySQL (baru di v1.3)

Versi MySQL boleh berbeda antar anggota tim — yang penting memenuhi syarat minimum. Tapi ada satu risiko yang perlu diurus menjelang pengumpulan:

**File `.sql` hasil dump dari MySQL versi baru bisa gagal di-import di MySQL versi lebih lama**, termasuk di komputer penguji. Penyebabnya sintaks atau atribut yang belum dikenal versi lama.

Yang dilakukan: kumpulkan informasi versi MySQL tiap anggota sekarang, dan saat menyiapkan dump untuk pengumpulan, buat dari versi **paling rendah** yang ada di tim, atau gunakan opsi kompatibilitas pada `mysqldump`. Jangan ditinggal sampai hari terakhir.

### Struktur folder — pemetaan ke tuntutan dokumen tugas

Dokumen tugas mensyaratkan: `/public`, `/app` (model/controller), `/views`, `/config`.

| Tuntutan | Di Laravel | Status |
|---|---|---|
| `/public` | `public/` | Persis sama |
| `/app` (model/controller) | `app/Models/`, `app/Http/Controllers/` | Persis sama, lebih terstruktur |
| `/views` | `resources/views/` | **Berbeda lokasi** |
| `/config` | `config/` | Persis sama |

**Penjelasan yang dipakai di dokumen dan presentasi:** struktur Laravel memenuhi maksud aturan tersebut — pemisahan antara koneksi database, tampilan, dan logika proses — melalui konvensi framework. Memindahkan folder view ke root justru melanggar konvensi Laravel tanpa manfaat apa pun. Siapkan jawaban ini, jangan sampai terlihat sebagai kelalaian.

### Aturan repo

- **Satu orang saja** yang menjalankan `laravel new` dan push sebagai commit awal. Tiga anggota lain clone. **Sudah selesai**
- **Kelima migration dibuat satu orang dalam satu commit di awal**, setelah skema disepakati. **Sudah selesai** — sudah ada di `main`, dan anggota tim menjalankan `php artisan migrate` saat onboarding
- **Jangan pernah mengedit file migration yang sudah di-push.** Anggota lain sudah menjalankannya; mengedit isinya membuat database mereka berbeda tanpa ada yang sadar. Perubahan skema sesudah itu lewat migration baru
- `.env` tidak masuk git. `.env.example` **selalu** diperbarui setiap ada konfigurasi baru — file ini yang jadi dasar "informasi setting" saat pengumpulan
- **Folder yang isinya di-*gitignore* tetap harus ada di repo**, diisi berkas `.gitkeep` kosong (baru ditulis di v1.3). Berlaku untuk `public/uploads/reports/`. Tanpa itu, folder tidak terbawa saat clone dan upload foto gagal dengan error yang tidak jelas
- Semua anggota wajib commit dengan pesan yang jelas (syarat dokumen tugas)
- Pembagian kerja **per modul (irisan vertikal), bukan per layer**. Setiap anggota mengerjakan migration sampai view untuk modulnya, sehingga commit tiap orang menyentuh `app/Http/Controllers/` dan `resources/views/` — sesuai permintaan dosen bahwa semua anggota terlibat di backend dan frontend
- **Satu `DatabaseSeeder` bersama** dibuat berbarengan dengan migration. Reservasi dan laporan butuh `users` dan `facilities` yang sudah ada, dan setiap orang butuh data untuk menguji modulnya
- **Push langsung ke `main` hanya untuk commit fondasi oleh PM** (setup awal, migration, seeder bersama, dokumen tim), dan berakhir begitu pembagian modul dimulai. Setelah itu seluruh perubahan lewat PR tanpa kecuali

### Catatan penggunaan AI

Laravel Boost dipasang dan AI dipakai. Yang perlu disadari tim: penilaian mata kuliah ini menekankan kejelasan kontribusi per anggota, dan **UTS-nya adalah tanya jawab tentang kode kalian sendiri**. Pakai AI untuk mencari dokumentasi, memeriksa skema, dan mengoreksi error. Hindari meminta AI membangun fitur utuh yang kemudian tidak bisa kalian bedah sendiri saat ditanya.

Kalau memakai AI, **berikan ketiga dokumen tim sebagai konteks.** Tanpa itu, AI akan mengarang asumsi sendiri yang berbeda dari asumsi anggota lain.

---

## A. Aturan Reservasi

### A1 — Pembatalan mandiri oleh pengguna

| Status reservasi | Aturan |
|---|---|
| `pending` | Bebas dibatalkan kapan saja |
| `approved` | Boleh selama `CURDATE() < DATE(start_time)`, yaitu sampai hari berganti ke hari acara |

Satu kalimat menjelaskan A1 dan A3 sekaligus: **pengajuan dan pembatalan mandiri sama-sama ditutup saat tengah malam sebelum hari acara.** Ini yang dipakai saat presentasi.

Keuntungan simetri ini: slot yang dibatalkan pada malam sebelum acara masih bisa dipesan orang lain sampai tengah malam, karena batas pengajuan menutup pada momen yang sama. Pada aturan lama, slot yang dibatalkan di menit terakhir sudah tidak bisa dipesan siapa pun.

**Konsekuensi yang diterima:** reservasi yang jatuh **hari ini** tidak bisa dibatalkan pengguna sendiri sama sekali. Pengguna dalam kondisi itu meminta petugas membatalkan lewat US 10.

### A2 — Durasi maksimal satu reservasi

**Maksimal 26 slot** (sehari penuh, praktis tanpa batas atas). Alasan: ada kegiatan yang memang berlangsung sehari penuh. Pencegahan monopoli diserahkan ke penilaian petugas saat approval, bukan ke sistem.

**Aturan turunan yang wajib divalidasi di server:**

```
DATE(start_time) = DATE(end_time)     -- satu reservasi tidak boleh melintasi tanggal
end_time > start_time
```

Tanpa aturan pertama, pengajuan 19.00 hari ini sampai 08.00 besok bisa lolos pengecekan jam operasional yang dilakukan per-waktu.

### A3 — Booking window

```
DATE(start_time) >= CURDATE() + INTERVAL 1 DAY     -- batas bawah: minimal H-1
DATE(start_time) <= CURDATE() + INTERVAL 30 DAY    -- batas atas
```

Alasan batas bawah memakai satuan kalender, bukan jam: petugas memeriksa antrian pada saat dia berada di kantor — sore sebelum pulang, pagi saat datang, dan waktu lain di sela pekerjaan. Yang dibutuhkan adalah jaminan bahwa pengajuan sudah masuk **sebelum hari-H dimulai**, bukan jarak 24 jam yang dihitung dari jam pengajuan.

**Konsekuensi yang diterima:** tenggang waktu nyata jadi bervariasi, antara 11 jam (ajukan pukul 20.00 untuk esok pukul 07.00) sampai 31 jam (ajukan pukul 07.00 untuk esok pukul 14.00). Ini harga dari aturan yang cocok dengan cara petugas bekerja dan mudah dijelaskan ke pengguna.

Pengajuan untuk waktu yang sudah lewat ditolak server dalam kondisi apa pun.

### A4 — Kapasitas fasilitas

**Informatif saja.** Ditampilkan dan bisa difilter saat pencarian (US 2), **tidak divalidasi** terhadap jumlah peserta. Tidak ada field `jumlah_peserta` di reservasi.

Kolom `kapasitas` **boleh `NULL`**, dan untuk fasilitas bertipe `alat` kolom ini tidak ditampilkan (kapasitas sebuah proyektor tidak punya makna).

**Aturan filter:** filter kapasitas hanya diterapkan pada baris yang `kapasitas IS NOT NULL`. Tanpa aturan ini, memfilter "kapasitas minimal 20" akan menghilangkan seluruh fasilitas bertipe Alat dari hasil pencarian, padahal filter itu tidak relevan bagi mereka. Di UI diberi catatan kecil: *"filter kapasitas tidak berlaku untuk tipe Alat"*.

### A5 — Batas jumlah reservasi aktif per pengguna

**Tanpa batas.** Pembatasan kuota tidak diimplementasikan; kontrolnya ada di tahap approval petugas.

Ini keputusan sadar, bukan kelalaian — jawab demikian kalau ditanya saat tanya jawab.

### A6 — Hari operasional

**Setiap hari, Senin–Minggu**, termasuk akhir pekan dan hari libur nasional. Kalender hari libur tidak diimplementasikan (butuh tabel terpisah, tidak diminta user story manapun).

### A7 — Status reservasi

```
pending | approved | rejected | cancelled_by_user | cancelled_by_officer
```

Dua jenis pembatalan sengaja dibedakan supaya riwayat dan rekap tidak ambigu.

**`completed` tidak disimpan sebagai status** — diturunkan dari `end_time < now`. Menyimpannya akan butuh scheduler atau perubahan data saat halaman dibuka; keduanya menambah komponen yang tidak diminta.

**Kolom alasan: `status_reason`.**

| `status` | Pengisi | Kewajiban |
|---|---|---|
| `rejected` | Petugas | **Wajib**, minimal 10 karakter |
| `cancelled_by_officer` | Petugas | **Wajib**, minimal 10 karakter |
| `cancelled_by_user` | Pengguna | Opsional |
| `pending`, `approved` | — | **Harus NULL** |

**Batas atas 500 karakter** untuk semua kasus (baru di v1.3, lihat F9).

Baris terakhir bukan formalitas: tanpa aturan itu, reservasi yang pernah ditolak lalu statusnya diubah lagi akan menyisakan alasan lama yang menyesatkan.

Alasan penolakan dibuat wajib dengan argumen yang sama seperti B3 pada laporan: pemohon berhak tahu kenapa permintaannya tidak dipenuhi. US 9 sendiri tidak menyebut alasan penolakan sama sekali — ini penambalan atas lubang tersebut.

### A8 — Reservasi pending yang terlewat

**Status kedaluwarsa diturunkan, bukan disimpan.** Data tetap `pending`; sistem membandingkan `status = pending AND start_time < now` saat menampilkan. Tidak ada nilai enum `expired`, tidak ada scheduler, tidak ada data yang berubah sendiri.

Cara menampilkannya:

| Tempat | Perlakuan |
|---|---|
| Antrian utama petugas | Hanya `pending` yang `start_time`-nya **belum** lewat |
| Tab "Terlewat" (petugas) | Read-only. Tombol approve/reject **dimatikan, termasuk divalidasi di server** |
| Riwayat pengguna | Ditandai "Tidak sempat diproses" |
| Rekap admin | Dihitung sebagai metrik terpisah, lihat E5 |

Menyetujui acara yang sudah lewat tidak ada artinya — kalau hanya tombolnya yang di-*disable* di tampilan, itu celah yang mudah ditembak saat tanya jawab.

**Catatan untuk seeder:** data semacam ini **tidak dapat dibuat lewat form**, karena A3 menolak pengajuan untuk masa lalu. Tab Terlewat hanya bisa diuji lewat `DatabaseSeeder`. Jangan menonaktifkan validasi A3 sementara demi membuat data uji.

---

## B. Aturan Laporan Kerusakan

### B1 — Kategori laporan (enum)

Nilai yang disimpan di database berupa slug; label tampilannya didefinisikan sebagai konstanta di kode.

| Nilai tersimpan | Label tampilan |
|---|---|
| `kerusakan_alat` | Kerusakan Alat/Perangkat |
| `kelistrikan` | Kelistrikan |
| `pendingin_ruangan` | Pendingin Ruangan |
| `furnitur` | Furnitur |
| `kebersihan` | Kebersihan |
| `lainnya` | Lainnya |

Slug dipakai supaya perbandingan di kode tidak bergantung pada teks berspasi dan bergaris miring.

### B2 — Foto laporan

| Aspek | Keputusan |
|---|---|
| Kewajiban | **Wajib**, seragam untuk semua kategori |
| Jumlah | Minimal 1, maksimal 3 |
| Format | jpg, jpeg, png |
| Ukuran | **Maksimal 3 MB per foto** |
| Validasi | Aturan Laravel `mimes:jpg,jpeg,png` + `max:3072` — memeriksa MIME type file sebenarnya di server, **bukan** ekstensi nama file |
| Lokasi simpan | `public/uploads/reports/` (lihat F5) |

**Foto disimpan sebagai file, bukan di database.** Database hanya menyimpan nama file.

**Konsekuensi skema:** karena boleh lebih dari satu foto, butuh tabel terpisah **`report_photos`** (relasi one-to-many ke `reports`).

Batas 1–3 tidak bisa dijaga database. Minimal 1 dijaga aturan `required`, maksimal 3 dijaga `max:3` pada input array. Sebut ini terang-terangan di dokumen, jangan sampai terlihat sebagai celah yang terlewat.

**Konsekuensi yang diterima:** kategori seperti Pendingin Ruangan atau Kelistrikan kadang kerusakannya tidak kasat mata, sehingga "bukti foto" bisa jadi formalitas. Wajib seragam tetap dipilih karena validasi bersyarat di dua sisi (client dan server) adalah sumber bug yang klasik.

### B3 — Status laporan & catatan resolusi

Status (dari dokumen tugas, bukan asumsi):

```
baru | diproses | selesai | ditolak
```

Catatan resolusi (`resolution_note`) — kolom teks yang diisi petugas menjelaskan tindakan yang diambil:

| Status | Kewajiban |
|---|---|
| `baru` | — |
| `diproses` | Opsional |
| `selesai` | **Wajib** |
| `ditolak` | **Wajib** |

**Panjang: minimal 10, maksimal 1000 karakter** saat diisi. Deskripsi laporan dari pelapor juga **minimal 10, maksimal 1000** (baru di v1.3, lihat F9).

Alasan `ditolak` juga wajib: pelapor berhak tahu kenapa laporannya tidak ditindaklanjuti, dan tanpa itu laporan yang sama akan masuk lagi minggu depan.

Contoh isi: *"Kabel HDMI diganti, sudah diuji dan berfungsi normal."* atau *"Proyektor normal saat dicek; kemungkinan salah input sumber pada laptop pengguna."*

### B4 — Siapa yang boleh melapor

**Pengguna login boleh melaporkan fasilitas mana pun**, tanpa syarat pernah mereservasi fasilitas tersebut. Orang bisa melihat kerusakan tanpa pernah memesan ruangannya.

Konsekuensi ke skema: `reports` **tidak** punya foreign key ke `reservations`. Ketiadaan relasi itu adalah penerapan aturan ini.

### B5 — Pembatalan oleh petugas ("kondisi mendesak")

**Sistem tidak memvalidasi keadaan mendesak.** Petugas yang menilai. Sistem hanya mewajibkan kolom alasan diisi, **minimal 10 karakter**, disimpan di `reservations.status_reason`.

---

## C. Akun & Role

### C1 — Registrasi mandiri

**Ada, dengan verifikasi admin.** Akun hasil registrasi mandiri berstatus `pending` dan tidak bisa login sampai diverifikasi admin (US 15 dikerjakan).

Alasan tetap menyediakan form registrasi: Aturan Implementasi poin 1 mewajibkan "Registrasi, login, logout" tanpa syarat.

### C2 — Status akun

```
pending | verified | rejected | suspended
```

| Aturan | |
|---|---|
| Akun `pending`, `rejected`, `suspended` | Ditolak saat login dengan **pesan yang spesifik**, bukan "password salah" — ini bukan masalah kredensial |
| Akun yang dibuat langsung oleh admin (US 13 & 14) | Otomatis `verified` |

**Matriks transisi status:**

```
pending    → verified | rejected
rejected   → verified                (orangnya datang mengklarifikasi)
verified   → suspended
suspended  → verified
mana pun   → pending                 DILARANG
```

`pending` adalah keadaan awal, bukan tujuan. Mengembalikan akun ke `pending` akan membuat orang yang sudah pernah login tiba-tiba terkunci tanpa ada yang meminta.

Transisi `rejected → verified` ada karena `email` bersifat UNIQUE dan akun tidak pernah dihapus permanen (C6). Tanpa transisi ini, orang yang registrasinya ditolak tidak akan pernah bisa mendaftar lagi dengan email yang sama.

**`role` tidak bisa diubah setelah akun dibuat.** Akun pengguna yang punya riwayat reservasi lalu diubah jadi petugas akan menghasilkan petugas yang menyetujui reservasinya sendiri, dan riwayatnya jadi tidak masuk akal. Kalau seseorang benar-benar berubah peran, admin membuat akun baru.

### C3 — Role

**Eksklusif — satu akun satu role.** Kolom `role` tunggal di tabel `users`, tanpa tabel pivot.

```
pengguna | petugas | admin
```

*Pengunjung bukan role* — pengunjung adalah pengunjung yang tidak punya akun.

**Petugas tidak melakukan registrasi mandiri dalam kondisi apa pun** (eksplisit di US 13). Akun petugas hanya dibuat admin.

### C4 — Field identitas pengguna

Di luar nama, email, password, role:

| Field | Keterangan |
|---|---|
| `identity_number` | NIM/NIP — jadi bahan yang diverifikasi admin. **UNIQUE**, nullable |
| `user_type` | `mahasiswa` \| `dosen` \| `staf` — **atribut, bukan role**. Nullable |

**Kenapa UNIQUE:** NIM dan NIP unik di dunia nyata, dan justru kolom inilah yang diverifikasi admin, jadi duplikat harus ditolak database. MySQL membolehkan banyak NULL pada kolom UNIQUE, sehingga petugas dan admin yang tidak punya NIM tetap bisa dibuat.

**Kenapa nullable:** keduanya nullable **karena role**, bukan karena opsional. Petugas dan admin tidak punya NIM dan tidak punya `user_type`. Batasan ini tidak bisa diwakili tipe kolom, jadi dijaga di server dengan `required_if:role,pengguna`.

### C5 — Keamanan password

- Password **di-hash** (jangan pernah plaintext, jangan MD5/SHA1)
- Minimal 8 karakter, divalidasi di **client dan server**
- **Tidak ada** reset password via email

**Reset password dilakukan admin.** Bentuknya aksi "Reset Password" di halaman Detail Akun: admin mengetikkan password baru, sistem menyimpan hash-nya. Bukan lewat manipulasi database langsung.

Tidak ada user story yang meminta ini. Keberadaannya adalah konsekuensi keputusan C5 sendiri — kalau reset mandiri ditiadakan, harus ada jalan lain. Catat demikian di dokumen Word supaya tidak terlihat sebagai fitur liar.

Konsekuensi lain: tabel `password_reset_tokens` bawaan Laravel tidak dipakai dan tidak dibuat (F8).

### C6 — Menonaktifkan akun

- **Tidak ada penghapusan permanen.** Akun yang dihapus akan membuat reservasi dan laporan lamanya menggantung tanpa pemilik dan merusak rekap historis. Larangan ini juga dijaga di level database lewat `ON DELETE RESTRICT` (lihat F7)
- Penonaktifan dilakukan lewat status `suspended`
- **Reservasi `approved` milik akun yang di-suspend tidak otomatis batal.** Sistem menampilkan peringatan berisi daftar reservasi terdampak saat admin menekan suspend; kalau perlu dibatalkan, petugas melakukannya lewat US 10 (pola yang sama dengan D4)

**Prioritas: paling akhir.** Tidak ada user story yang meminta fitur ini.

---

## D. Fasilitas & Data Master

### D1 & D2 — Tipe dan lokasi fasilitas

**Enum di level aplikasi.** Daftar nilai sah didefinisikan sebagai konstanta di kode, form admin memakai dropdown (bukan input teks), server menolak nilai di luar daftar. Kolom di database tetap `VARCHAR`. **Tidak ada tabel referensi, tidak ada foreign key.**

Alasan tidak pakai teks bebas: "Laboratorium", "Lab", dan "laboratorium" akan jadi tiga nilai berbeda, merusak filter pencarian (US 2) dan rekap per lokasi (US 17).

**Tipe fasilitas:**
```
Ruang Kelas | Aula | Laboratorium | Lapangan | Alat
```

**Lokasi:**
```
Gedung A | Gedung B | Gedung C | Gedung Serba Guna | Area Olahraga | Gudang Inventaris
```

Jawaban kalau ditanya "bagaimana kalau ada gedung baru?": satu daftar konstanta diubah. Kalau waktu tersisa di akhir, `lokasi` adalah kandidat paling layak dipromosikan jadi tabel referensi.

Kenapa kedua kolom ini VARCHAR sementara kolom status memakai ENUM MySQL: lihat F6.

### D3 — Status fasilitas

```
active | under_maintenance | inactive
```

Dua status non-aktif sengaja dibedakan karena berasal dari aktor dan maksud yang berbeda:

| Status | Asal | Sifat |
|---|---|---|
| `under_maintenance` | US 12, petugas | Sementara, akibat laporan kerusakan |
| `inactive` | US 16, admin | Administratif, misal ruangan dialihfungsikan |

**Matriks wewenang:**

| Transisi | Admin | Petugas |
|---|---|---|
| `active` ↔ `inactive` | ✔ | ✘ |
| `active` ↔ `under_maintenance` | ✔ | ✔ |

Kalau kedua status digabung, petugas jadi punya wewenang yang seharusnya milik admin. Penerapannya berupa dua route terpisah dengan daftar nilai `in:` yang berbeda — lihat dokumen route bagian 22.

### D4 — Dampak status non-aktif terhadap reservasi approved

**Tidak ada pembatalan otomatis.** Sistem menampilkan peringatan berisi daftar reservasi terdampak; petugas membatalkan satu per satu lewat US 10 dengan alasan tertulis.

Alasan: US 10 memang secara khusus menyebut skenario ini — *"dalam kondisi mendesak (mis. fasilitas mendadak tidak bisa dipakai)"*. Pembatalan otomatis juga berbahaya karena tidak bisa diurungkan kalau petugas salah klik.

Peringatan ini muncul di empat tempat: saat petugas menandai fasilitas dalam perbaikan (dua jalur masuk), saat admin menonaktifkan fasilitas, dan saat admin men-suspend akun (C6). Logikanya sama di keempatnya — cari reservasi `approved` yang belum lewat, tampilkan, jangan batalkan otomatis. **Sepakati di awal siapa yang menulis komponen bersama ini**, karena dipakai lintas modul. Cara pengambilan datanya ditetapkan di dokumen route bagian 12.

### D5 — Fasilitas non-aktif di daftar publik

**Tetap muncul dengan penanda jelas**, tombol reservasi dimatikan. Kalau disembunyikan, orang mengira fasilitasnya tidak eksis.

**Validasi penolakan reservasi tetap ada di server**, bukan hanya tombol yang di-*disable*.

**Tampilan grid ketersediaan.** Fasilitas `under_maintenance` bisa tetap punya reservasi `approved` yang sah, karena D4 tidak membatalkannya otomatis. Karena itu:

- Grid **tetap digambar apa adanya** — slot terisi tetap terlihat terisi. Mengabukan seluruh grid akan menyembunyikan fakta bahwa masih ada reservasi aktif di situ
- Di atas grid ditampilkan banner: *"Fasilitas dalam perbaikan, reservasi baru ditutup"*

**Slot punya tiga status visual, bukan dua:**

| Status visual | Arti |
|---|---|
| Kosong & bisa dipesan | Tidak ada reservasi approved, dan tanggalnya memenuhi A3 |
| Kosong tapi sudah lewat batas | Tidak ada reservasi approved, tapi tanggalnya hari ini atau sudah lewat |
| Terisi | Ada reservasi approved |

Tanpa status kedua, pengguna akan mengira slot hari ini bisa dipesan, mengisi form, lalu ditolak server — berulang kali.

### D6 — Penghapusan fasilitas

**Tidak ada hard delete.** US 16 hanya menyebut "tambah/edit/nonaktifkan" — kata "hapus" bahkan tidak dipakai. Cukup ubah status ke `inactive`, supaya reservasi dan laporan historis tetap utuh. Larangan ini juga dijaga `ON DELETE RESTRICT` di level database (F7).

---

## E. Rekap & Export

### E1 — Periode rekap

**Rentang tanggal bebas** — satu form dengan dua input tanggal. Filter yang sama langsung dipakai untuk export.

### E2 — Definisi okupansi

```
okupansi (%) = (jumlah slot terpakai ÷ (26 × jumlah hari dalam periode)) × 100
```

- Hanya reservasi berstatus `approved` yang dihitung
- Jumlah slot terpakai dihitung dari `SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time)) / 30`
- Tampilkan juga **jumlah reservasi mentahnya** supaya persentase punya konteks

**Titik lemah yang diterima:** penyebut mengasumsikan setiap fasilitas tersedia setiap hari. Fasilitas yang sempat `under_maintenance` akan terlihat punya okupansi rendah seolah tidak diminati. Memperbaikinya butuh tabel log riwayat perbaikan — tidak dikerjakan.

**Hafalkan penjelasan ini**, karena wajar ditanyakan saat tanya jawab.

### E3 — Definisi frekuensi kerusakan

**Semua laporan kecuali yang berstatus `ditolak`.** Laporan `baru` dan `diproses` tetap merepresentasikan masalah nyata yang belum selesai; mengecualikannya akan membuat fasilitas bermasalah terlihat baik-baik saja.

### E4 — Format export

**CSV lebih dulu** — bisa dibuka di Excel, jadi praktis menutup dua format sekaligus tanpa library tambahan. PDF ditambahkan hanya kalau waktu masih cukup.

**Jangan jadikan PDF pekerjaan minggu terakhir.**

### E5 — Metrik reservasi tidak sempat diproses

- Ditampilkan sebagai **satu angka tunggal di atas tabel okupansi**, bukan tab ketiga: *"Reservasi tidak sempat diproses pada periode ini: N"*
- Rumus: `status = 'pending' AND start_time < NOW()`, dengan `DATE(start_time)` berada dalam rentang tanggal yang dipilih
- Basisnya `start_time`, **bukan** `created_at`, supaya sebanding dengan okupansi di tabel di bawahnya yang juga berbasis waktu penggunaan
- Ikut masuk ke file export

---

## F. Keputusan desain teknis

### F1 — Cara menyimpan slot waktu

**Pasangan `start_time` dan `end_time` bertipe `DATETIME`. Tidak ada tabel slot.**

Alternatif yang ditolak adalah tabel slot eksplisit (26 baris `time_slots` + tabel pivot per slot terpakai). Daya tarik utamanya — bentrok bisa dijamin lewat `UNIQUE (facility_id, tanggal, slot_id)` — sebagian besar batal karena US 9 menetapkan bentrok dicek saat **approve**, sehingga dua reservasi `pending` yang bertumpuk itu sah dan tidak boleh diblokir constraint. Akalnya (baris slot hanya dibuat saat approve) berarti dua jalur penulisan untuk satu entitas. Ditambah satu reservasi sehari penuh jadi 26 baris.

**Slot tetap ada sebagai konsep, bukan sebagai tabel.** 26 slot didefinisikan sebagai konstanta di kode — pola yang sama dengan enum di D1/D2 — dipakai untuk mengisi dropdown, menggambar grid ketersediaan, dan memvalidasi input. Database hanya menyimpan hasilnya.

Validasi server yang wajib (kumulatif dengan A2 dan A3):

```
menit(start_time) ∈ {0, 30}  dan detik = 0
menit(end_time)   ∈ {0, 30}  dan detik = 0
07:00 ≤ start_time, end_time ≤ 20:00
DATE(start_time) = DATE(end_time)
end_time > start_time
```

Cara ketujuh aturan ini diterjemahkan ke aturan validasi Laravel, beserta pesan error masing-masing, ada di dokumen route bagian 17.

### F2 — Pencegahan bentrok saat dua petugas approve bersamaan

**Transaksi database + row lock pada baris fasilitas, lalu cek bentrok diulang di dalam transaksi.**

Yang dicegah: dua reservasi pending bertumpuk di fasilitas yang sama, dua petugas menekan approve dalam selisih milidetik, keduanya lolos. Pengecekan di level aplikasi saja tidak cukup karena ada jeda antara `SELECT` dan `UPDATE` (pola *check-then-act*). `UNIQUE` constraint tidak tersedia karena F1 memilih pasangan datetime — MySQL tidak punya constraint untuk rentang waktu.

```php
DB::transaction(function () use ($reservation) {
    // Kunci baris fasilitas. Semua approval untuk fasilitas ini mengantre di sini.
    $facility = Facility::whereKey($reservation->facility_id)
        ->lockForUpdate()
        ->firstOrFail();

    // Cek ulang DI DALAM transaksi, setelah lock didapat
    $conflict = Reservation::where('facility_id', $facility->id)
        ->where('status', 'approved')
        ->where('start_time', '<', $reservation->end_time)
        ->where('end_time',   '>', $reservation->start_time)
        ->exists();

    if ($conflict || $facility->status !== 'active') {
        throw new ConflictException();
    }

    $reservation->update(['status' => 'approved']);
});
```

**Kenapa yang dikunci baris fasilitas:** baris yang belum ada tidak bisa dikunci, dan dibutuhkan satu titik tunggal tempat semua approval untuk fasilitas yang sama saling menunggu. Baris fasilitas adalah induk bersama semua reservasi di fasilitas itu.

**Kenapa `<` dan `>`, bukan `<=` dan `>=`:** dua rentang bertumpuk jika dan hanya jika `start_A < end_B` **dan** `end_A > start_B`. Untuk 09.00–09.30 dan 09.30–10.00: `09:00 < 10:00` benar, `09:30 > 09:30` salah → tidak bentrok, sesuai akal sehat. Dengan `>=`, dua reservasi yang bersambung rapi akan salah dianggap bentrok.

Query ini dilayani index `idx_conflict`, lihat dokumen pendamping.

**Prasyarat yang wajib ada:** `lockForUpdate()` hanya berfungsi di InnoDB. Di MyISAM ia tidak error, ia hanya tidak mengunci apa pun. Itulah alasan `'engine' => 'InnoDB'` ditegaskan di `config/database.php` (bagian 1).

### F3 — Penanganan timezone

**Semua waktu WIB, disimpan sebagai waktu lokal, tanpa konversi.** Tiga hal yang wajib dilakukan:

1. **`APP_TIMEZONE=Asia/Jakarta` di `.env`.** Laravel default-nya UTC
2. **`config/app.php` harus menjemput nilai itu (ditambahkan di v1.3):**

   ```php
   'timezone' => env('APP_TIMEZONE', 'UTC'),
   ```

3. **Kolom waktu bisnis bertipe `DATETIME`, bukan `TIMESTAMP`**

**Kenapa poin 2 ditambahkan.** Versi v1.2 hanya menyebut poin 1, dan itu ternyata tidak cukup. `.env` hanya sumber bahan mentah; yang dipakai Laravel saat bootstrap adalah `config('app.timezone')`, dan nilai itu hanya terisi kalau `config/app.php` memanggil `env()`. Kalau tidak, hasilnya:

```
env('APP_TIMEZONE')         = "Asia/Jakarta"   ← .env benar
config('app.timezone')      = "UTC"            ← putus di sini
date_default_timezone_get() = "UTC"            ← akibatnya
```

Seluruh rantai `now()`, Carbon, dan `date()` ikut UTC. Akibatnya validasi A3 **salah sehari** untuk setiap pengajuan yang dibuat setelah pukul 17.00 WIB, dan A1 ikut salah hitung. Tidak ada error yang muncul.

Kejadian nyata: gejalanya ketahuan dari selisih 7 jam antara timestamp nama file migration dan jam pembuatan file menurut sistem operasi. Layak disebut sebagai "kendala yang dihadapi" saat presentasi.

Verifikasi: `php artisan tinker` lalu `now()` — harus menampilkan `Asia/Jakarta (+07:00)`.

**Kenapa poin 3.** Di MySQL, `TIMESTAMP` dikonversi ke UTC saat disimpan dan dibalik saat dibaca berdasarkan timezone sesi koneksi — kalau timezone MySQL di komputer dosen berbeda, semua jam reservasi bergeser. `DATETIME` menyimpan persis apa yang diberikan. Di migration: `$table->dateTime('start_time')`.

Berlaku untuk `start_time`, `end_time`, dan kolom waktu lain yang punya makna bisnis. `created_at`/`updated_at` bawaan Laravel dibiarkan bertipe `TIMESTAMP` karena tidak dipakai untuk logika apa pun — paling jauh hanya untuk pengurutan, yang tetap benar berapa pun timezone-nya.

### F4 — Dua level output pada data ketersediaan

**Dipisahkan di level route dan query, bukan di level tampilan.**

Sebenarnya ada tiga tingkat:

| Siapa | Yang boleh dilihat |
|---|---|
| Pengunjung & pengguna yang bukan pemilik | Hanya status slot: terisi / kosong |
| Pemilik reservasi | Detail lengkap reservasinya sendiri (US 5) |
| Petugas & admin | Detail lengkap semua reservasi |

**Prinsipnya: jangan pernah mengambil data yang tidak boleh ditampilkan.** Cara yang salah tapi menggoda adalah satu query yang mengambil semua kolom, lalu template Blade-nya tidak mencetak `purpose` untuk pengunjung. Datanya tetap ada di objek yang dikirim ke view — satu `@dd` yang lupa dihapus atau satu partial yang dipakai ulang, dan datanya bocor.

Bentuk konkretnya:

- **Route ketersediaan publik** — tanpa auth. Query hanya mengambil `facility_id`, `start_time`, `end_time` dari reservasi `approved`, diubah jadi grid 26 boolean per tanggal. Kolom `user_id` dan `purpose` tidak di-`select` sama sekali. ID reservasi juga tidak dikirim ke tampilan publik
- **Route detail reservasi** — auth, dan kepemilikan diperiksa lewat **Laravel Policy** (`ReservationPolicy::view`): boleh kalau pemiliknya, atau kalau role-nya petugas/admin. Aturan kepemilikan ada di satu tempat, tidak disebar jadi `if` di banyak controller
- **Antrian petugas dan rekap admin** — route terpisah dengan middleware role

Grid ketersediaan selalu **dihitung**, tidak disimpan.

### F5 — Lokasi penyimpanan file foto laporan

**Langsung di `public/uploads/reports/`, bukan lewat `storage/app/public` + `php artisan storage:link`.**

Perlu diluruskan: cara standar Laravel **tidak** lebih aman dari sisi privasi. Disk `public` Laravel juga dapat diakses siapa pun lewat URL, persis sama dengan `public/uploads/`. Privasi sungguhan butuh file di luar `public/` plus controller yang menyalurkannya setelah memeriksa otorisasi — kerja tambahan yang tidak diminta user story mana pun untuk data berupa foto kerusakan fasilitas.

Jadi bedanya cuma satu: pendekatan standar bergantung pada symlink, pendekatan ini tidak. Symlink `public/storage` sering tidak terbawa utuh saat proyek di-zip dari Windows; saat diekstrak hasilnya bisa jadi folder kosong, dan `php artisan storage:link` untuk memperbaikinya justru gagal karena direktorinya dianggap sudah ada. Akibatnya semua foto laporan 404 di komputer dosen.

**Jawaban kalau ditanya:** dipilih demi portabilitas, karena program dijalankan dari salinan yang dipindah lewat Google Drive, bukan dari hasil clone repo.

Tiga aturan turunannya:

1. **Nama file digenerate, bukan nama asli** — `Str::random(40)` plus ekstensi. Nama dari pengguna membawa tabrakan nama, karakter aneh, dan celah path traversal. Nama hasil generate itu yang disimpan di `report_photos`
2. **Validasi tipe lewat aturan Laravel** — `mimes:jpg,jpeg,png` memeriksa MIME type file sebenarnya, bukan tulisan setelah titik di nama file. Digabung `max:3072` sesuai B2
3. **Folder upload masuk `.gitignore`, tapi foldernya sendiri tetap ada di repo** lewat berkas `.gitkeep` kosong. Foto data demo **harus ikut** di zip yang dikumpulkan — zip dibuat dari folder kerja, bukan dari clone

### F6 — ENUM MySQL atau VARCHAR?

Dua perlakuan berbeda, dan bedanya bisa dijelaskan dalam satu kalimat.

| Pendekatan | Kolom |
|---|---|
| **ENUM MySQL** | `users.role`, `users.status`, `users.user_type`, `facilities.status`, `reservations.status`, `reports.category`, `reports.status` |
| **VARCHAR + konstanta PHP** | `facilities.type`, `facilities.location` |

**Alasannya:** daftar tipe dan lokasi diharapkan bertambah selama sistem dipakai (gedung baru, jenis fasilitas baru), jadi perubahan data master tidak boleh butuh `ALTER TABLE`. Sebaliknya, alur status berasal dari dokumen kebutuhan dan tidak bertambah, sehingga jaminan di level database itu gratis.

Harga yang diterima: menambah nilai status baru butuh migration `ALTER`. Itu memang jarang terjadi dan memang seharusnya terasa berat.

Konsekuensi untuk `type` dan `location`: **database tidak menjaga apa pun di kedua kolom itu.** Nilai salah ketik akan diterima. Penjaganya konstanta PHP + `Rule::in()` + dropdown di form admin.

### F7 — Kebijakan foreign key

Semua kolom foreign key bersifat `NOT NULL`, sehingga tidak ada baris anak yang menggantung tanpa induk.

| Relasi | onDelete | onUpdate |
|---|---|---|
| `reservations.user_id` → `users` | RESTRICT | CASCADE |
| `reports.user_id` → `users` | RESTRICT | CASCADE |
| `reservations.facility_id` → `facilities` | RESTRICT | CASCADE |
| `reports.facility_id` → `facilities` | RESTRICT | CASCADE |
| `report_photos.report_id` → `reports` | **CASCADE** | CASCADE |

**Kenapa RESTRICT di empat yang pertama:** C6 dan D6 sama-sama melarang penghapusan permanen. RESTRICT memindahkan larangan itu dari disiplin tim ke database, jadi `->delete()` yang tidak sengaja terpanggil akan gagal dengan error, bukan menghapus data historis.

**Kenapa CASCADE hanya di `report_photos`:** arah kebergantungannya berbeda. Reservasi dan laporan punya nilai historis yang berdiri sendiri, jadi pemiliknya tidak boleh hilang. Foto tidak punya makna apa pun di luar laporannya. Praktisnya laporan juga tidak pernah dihapus, jadi CASCADE ini kemungkinan tidak pernah aktif; ditulis karena benar secara semantik dan nol risikonya.

Yang perlu disadari: CASCADE hanya membersihkan baris database, **bukan file fisik** di `public/uploads/reports/`.

### F8 — Tabel bawaan Laravel yang dibuang (baru di v1.3)

Laravel 13 membawa tiga file migration yang bersama-sama membuat **tujuh tabel**. Hanya `users` yang dipakai. **Enam sisanya dibuang**, dan file migration-nya dihapus dari repo.

| Tabel bawaan | Nasib | Dasar |
|---|---|---|
| `users` | **Dipakai**, isinya ditulis ulang sesuai skema | — |
| `password_reset_tokens` | Dibuang | C5 — tidak ada reset password via email |
| `sessions` | Dibuang | `SESSION_DRIVER=file` |
| `cache`, `cache_locks` | Dibuang | `CACHE_STORE=file` |
| `jobs`, `job_batches`, `failed_jobs` | Dibuang | `QUEUE_CONNECTION=sync` |

Kolom `email_verified_at` pada `users` juga dihapus, karena verifikasi akun di sistem ini dilakukan admin lewat kolom `status` (C1), bukan lewat email.

**Alasan membuang, bukan membiarkan:** hasil `SHOW TABLES` di komputer penguji menampilkan lima tabel kerja ditambah `migrations` — cocok dengan ERD dan dengan isi dokumen Word. Tabel yang tidak dipakai hanya mengundang pertanyaan yang jawabannya "itu bawaan yang tidak kami bersihkan".

**Risiko yang diterima dan cara menutupnya:** kalau `.env` seseorang berisi `SESSION_DRIVER=database`, ia akan kena error `Table 'sessions' doesn't exist` di halaman mana pun. Ditutup lewat `.env.example` dan instruksi onboarding yang mengunci keempat driver. Gejalanya jelas (nama tabel disebut di pesan error) dan perbaikannya satu baris.

Keputusan ini **tidak mengunci apa pun.** Kalau suatu saat salah satu tabel dibutuhkan, dibuat lewat migration baru — bukan dengan mengedit migration lama.

### F9 — Batas panjang kolom teks (baru di v1.3)

Empat angka yang belum ditetapkan versi sebelumnya. Semuanya divalidasi di aplikasi; **tidak ada yang membutuhkan perubahan skema**, karena kolomnya sudah bertipe `TEXT` atau `VARCHAR` yang cukup longgar.

| Kolom | Batas | Alasan |
|---|---|---|
| `reports.description` | min 10, maks 1000 | Deskripsi "rusak" saja tidak cukup bagi petugas untuk menindaklanjuti |
| `reports.resolution_note` | min 10, maks 1000 | Disamakan dengan `status_reason` yang A7 tetapkan minimal 10 |
| `reservations.status_reason` | maks 500 | A7 sudah menetapkan batas bawah 10; batas atasnya belum ada |
| `facilities.description` | maks 1000 | Kolomnya TEXT, tapi textarea tanpa batas mengundang paste sembarangan |

Pertama kali dicatat di dokumen route bagian 27; dipindahkan ke sini sebagai rumah kanoniknya.

### F10 — Deklarasi `$fillable`: bentuknya dan isinya (baru di v1.5)

Dua hal terpisah yang sering tertukar. Yang pertama soal cara menulis, yang kedua soal kolom mana yang masuk — dan yang kedua jauh lebih mahal kalau salah.

#### Bentuk: attribute, bukan property

**Kelima model memakai `#[Fillable([...])]`** di level class, bukan `protected $fillable = [...]`.

```php
#[Fillable(['name', 'type', 'location', 'capacity', 'description', 'status'])]
class Facility extends Model
```

Alasannya: itu bentuk yang dibawa stub Laravel 13. Model `User` hasil generate sudah memakainya, berdampingan dengan `#[Hidden]` dan `protected function casts()`. Melawan bawaan framework menimbulkan pertanyaan "kenapa ditulis ulang?" yang lebih sulit dijawab saat tanya jawab daripada "ini bawaan Laravel 13".

Berlaku juga untuk attribute Eloquent sejenis: `#[Hidden]`, `#[Unguarded]`, `#[WithoutTimestamps]`, `#[RouteKey]`.

Yang perlu disadari: attribute tidak terjaring kalau kamu mencari kata `fillable` di badan class. Cari di baris sebelum `class`.

#### Isi: satu aturan, tanpa pengecualian

**Sebuah kolom masuk `$fillable` kalau dan hanya kalau ia muncul sebagai field di salah satu kontrak form** (Bagian III dokumen route).

Kolom yang berubah lewat aksi khusus — status yang punya route PATCH tersendiri — **tidak masuk**, dan controllernya menetapkannya eksplisit (`$model->status = ...` lalu `save()`).

Tiga kasus yang sudah diperiksa, sebagai acuan membaca aturannya:

| Kolom | Masuk? | Dasar |
|---|---|---|
| `facilities.status` | **ya** | field di form A2, bagian 23 — `required\|in:active,under_maintenance,inactive` |
| `users.role` | **ya** | field di form A4, bagian 24 — `required\|in:pengguna,petugas` |
| `users.status` | **tidak** | tidak pernah jadi field form. C2 menetapkan akun buatan admin langsung `verified`, dan lima transisinya punya route PATCH sendiri-sendiri |

**Yang perlu ditegaskan:** `$fillable` menjaga **mass assignment**, bukan **wewenang**. Mengeluarkan `facilities.status` dari daftar tidak membuat petugas kehilangan akses ke `inactive` — dia tinggal menulis `$facility->status = 'inactive'; $facility->save();`. Penegak matriks D3 adalah daftar `in:` yang berbeda di dua Form Request (bagian 22), dan hanya itu. Mengeluarkan kolom dari `$fillable` demi "keamanan" memberi nol perlindungan sambil mematikan form yang sah.

Konsekuensi turunannya: karena `status` fillable, controller **wajib** memakai `$request->validated()`, tidak pernah `$request->all()`.

**Setiap pemegang model memeriksa modelnya sendiri terhadap aturan ini lalu melapor di grup.** Saat v1.5 ditulis, baru `Facility` dan `User` yang diperiksa; `Reservation`, `Report`, dan `ReportPhoto` belum.

Kenapa aturan ini perlu ditulis sama sekali: kolom yang hilang dari `$fillable` **dibuang tanpa error**. Lihat F11 baris kedua — kesalahan ini sudah benar-benar terjadi sekali di review PR pertama.

#### Catatan revisi

v1.4 sempat menetapkan kebalikannya (property, bukan attribute), atas dugaan bahwa hanya satu model memakai attribute. Dugaan itu salah — `User` juga memakainya — dan keputusannya dibalik sebelum v1.4 diedarkan. Dicatat di sini, bukan dihapus, karena persis inilah yang bagian "Cara memakai dokumen ini" maksud: keputusan boleh berubah, asal berubah untuk semua orang sekaligus lewat versi baru.

### F11 — Dua penjaga di `AppServiceProvider` (baru di v1.5)

Dua baris wajib di `app/Providers/AppServiceProvider.php`, method `boot()`:

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;

public function boot(): void
{
    Paginator::useBootstrapFive();
    Model::preventSilentlyDiscardingAttributes(! app()->isProduction());
}
```

**Baris pertama adalah konsekuensi langsung dari bagian 1.** View paginasi bawaan Laravel memakai Tailwind, dan Tailwind sudah dibuang dari repo. Tanpa baris ini, setiap `{{ $items->links() }}` merender markup yang tidak punya CSS sama sekali — paginasi muncul sebagai deretan angka berantakan, dan gejalanya mudah disalahartikan sebagai "Bootstrap-nya belum ke-load".

**Baris kedua mengubah kegagalan diam menjadi kegagalan berisik.** Secara bawaan, atribut yang tidak ada di `$fillable` **dibuang tanpa error**: `$facility->update(['status' => 'inactive'])` tetap mengembalikan `true`, flash "berhasil" tetap muncul, dan kolomnya tidak berubah. Dengan lima model yang `$fillable`-nya ditulis empat orang berbeda, ini satu baris yang menutup seluruh kelas kesalahan tersebut — dan kesalahan itu sudah benar-benar terjadi sekali di review PR pertama.

`! app()->isProduction()` membuatnya aktif hanya di luar produksi. Proyek ini tidak pernah dideploy (Bagian G), jadi praktisnya selalu aktif; ditulis lengkap karena itu bentuk yang benar dan nol risikonya.

**Pemilik file ini adalah PM** — lihat bagian 4 dokumen pembagian modul. Permintaan baris baru lewat grup, jangan edit langsung.

---

## G. Dinyatakan di luar scope

Ditulis eksplisit di dokumen Word. Menyebut apa yang **tidak** dikerjakan menunjukkan batasan yang disadari, bukan fitur yang terlupakan:

- Notifikasi email atau pengingat H-1
- Notifikasi dalam aplikasi — pengguna mengecek status dengan membuka halaman riwayat
- Reset password mandiri via email (reset dilakukan admin — lihat C5)
- Reservasi berulang, misal setiap Senin selama satu semester
- Approval berjenjang (lebih dari satu tingkat persetujuan)
- Integrasi kalender eksternal (Google Calendar, iCal)
- Pembayaran atau biaya sewa fasilitas
- Multi-role dalam satu akun (lihat C3)
- Kalender hari libur nasional (lihat A6)
- Pembatasan kuota reservasi per pengguna (lihat A5)
- Riwayat perbaikan fasilitas untuk koreksi perhitungan okupansi (lihat E2)
- Audit log lengkap (siapa mengubah apa dan kapan) — yang ada hanya alasan pada `status_reason` dan catatan resolusi sesuai US 10 & 11. Tidak ada kolom pencatat petugas pemroses
- Pelaporan kerusakan yang tidak melekat pada satu fasilitas tertentu (lampu koridor, lift) — setiap laporan wajib menunjuk satu fasilitas
- Unggah dokumen pendukung reservasi, misal surat izin kegiatan — upload file hanya ada di laporan kerusakan
- Visualisasi grafik pada rekap — US 17 meminta rekap dilihat dan diekspor; tabel sudah memenuhi
- Pengubahan reservasi atau laporan yang sudah diajukan — kalau salah, batalkan lalu ajukan ulang
- Deployment ke server atau hosting — aplikasi dijalankan lokal lewat `php artisan serve`

---

## H. Navigasi & halaman

Daftar halaman lengkap dan peta navigasi ada di `halaman-navigasi-dan-skema.md`. Daftar route, konvensi penamaan, dan kontrak form ada di `route-dan-kontrak-form.md`. Yang ditetapkan di sini hanya aturan yang sifatnya mengikat.

### H1 — Landing page setelah login

Landing page setiap role adalah **halaman keadaan**, yaitu halaman yang menampilkan data yang sudah ada, bukan **halaman aksi** yang berupa form kosong.

| Role | Landing |
|---|---|
| Pengguna | Daftar Fasilitas |
| Petugas | Dashboard Antrian |
| Admin | Daftar Akun |

Alasannya tiga: form aksi hampir selalu butuh konteks dari halaman sebelumnya (form reservasi butuh fasilitas yang dipilih dari daftar), orang login bukan cuma untuk satu tujuan (mengecek status reservasi kemarin, bukan memesan lagi), dan form kosong tidak memberi tahu apa pun tentang keadaan sistem.

**Tidak ada halaman dashboard untuk pengguna dan admin.** Keduanya tidak diminta user story manapun, dan landing-nya diarahkan ke halaman yang memang sudah ada.

### H2 — Halaman yang tidak punya user story

Hanya satu, dan keberadaannya wajib dijelaskan di dokumen Word: **aksi Reset Password di halaman Detail Akun**, sebagai konsekuensi C5. Selain itu setiap halaman memetakan ke minimal satu user story.

---

## Ringkasan dampak ke skema database

Kolom dan tabel yang **tidak ada** di hint database dokumen tugas, tapi dibutuhkan oleh keputusan di atas:

| Tabel | Tambahan | Dari keputusan |
|---|---|---|
| `users` | `status` (pending/verified/rejected/suspended) | C1, C2, C6 |
| `users` | `identity_number`, UNIQUE dan nullable | C4 |
| `users` | `user_type` (mahasiswa/dosen/staf), nullable | C4 |
| `users` | `email_verified_at` **dihapus** | C1, F8 |
| `facilities` | `status` (active/under_maintenance/inactive) | D3 |
| `facilities` | `kapasitas` dibuat nullable | A4 |
| `reservations` | `status_reason` | A7, B5 |
| `reservations` | `start_time`, `end_time` bertipe `DATETIME` | F1, F3 |
| `reports` | `resolution_note` | B3 |
| **`report_photos`** | **tabel baru**, one-to-many ke `reports` | B2 |

Hint database dokumen tugas hanya menyebut empat tabel — dengan keputusan di atas, jumlahnya jadi **lima**. Ditambah tabel `migrations` bawaan Laravel yang mencatat riwayat migration, `SHOW TABLES` menampilkan enam baris.

Spesifikasi kolom lengkap, tipe data, relasi, dan index ada di dokumen pendamping.

---

## Jadwal target

Tenggat 11 Oktober 2026, 12.00 WIB.

| Periode | Target |
|---|---|
| 12–18 Sep | ~~Daftar halaman & peta navigasi, skema database~~ **selesai**; ~~setup Laravel & repo~~ **selesai**; ~~kelima migration~~ **selesai**; ~~route list & kontrak form~~ **selesai**; sisa: `DatabaseSeeder` bersama, penetapan siapa memegang modul apa |
| 19–27 Sep | Autentikasi & role; modul fasilitas |
| 28 Sep–5 Okt | Modul reservasi; modul laporan |
| 6–9 Okt | Rekap & export; UI/UX, validasi client, data demo |
| 10–11 Okt | Dokumen Word & persiapan presentasi |

**Fitur fungsional dianggap selesai 5 Oktober**, bukan 10 Oktober. Sisa lima hari bukan buffer mewah — itu ruang untuk bug yang baru muncul saat modul digabung, dan untuk penyusunan dokumen Word yang butuh screenshot setiap fitur.

**`DatabaseSeeder` tidak boleh lewat dari 27 September.** Modul reservasi dan laporan tidak dapat diuji tanpa `users` dan `facilities` yang sudah ada, dan data reservasi pending yang terlewat (A8) mustahil dibuat lewat form.

---

## Yang dikumpulkan

Satu **file Word** (bukan kodenya) berisi:

- Nama dan NIM anggota kelompok
- Pembagian tugas
- Link file program di Google Drive (source code, `.sql`, dan file pendukung)
- **Informasi setting untuk menjalankan program:**
  - PHP 8.3 atau lebih baru
  - `php.ini` — `upload_max_filesize=3M`, `post_max_size=12M`
  - `.env` — disalin dari `.env.example`, isi `DB_PASSWORD`, jangan ubah `APP_TIMEZONE`, `SESSION_DRIVER`, `QUEUE_CONNECTION`, `CACHE_STORE`
  - `config/app.php` — `'timezone' => env('APP_TIMEZONE', 'UTC')` (sudah ada di source code; disebut karena inilah yang membuat `.env` berpengaruh)
  - `config/database.php` — `'engine' => 'InnoDB'` (sudah ada; wajib untuk F2)
  - Tanpa `npm install`, tanpa `node_modules`
  - Langkah menjalankan: buat database → import `.sql` → `php artisan serve`
- Informasi login untuk masing-masing aktor (satu password seragam untuk akun demo)
- Screenshot antarmuka + penjelasan singkat tiap fitur
- **Daftar di luar scope** (Bagian G)
- **Tiga aturan yang ada di ERD tapi tidak dijaga database** (dokumen pendamping bagian 7.10): batas 1–3 foto, larangan bentrok jadwal, dan `identity_number`/`user_type` hanya untuk role pengguna
- **Aksi Reset Password di Detail Akun** sebagai satu-satunya elemen tanpa dasar user story (H2)

Presentasi 10 menit + tanya jawab 10–15 menit, mencakup latar belakang, fitur utama, demo sistem, dan kendala yang dihadapi.
