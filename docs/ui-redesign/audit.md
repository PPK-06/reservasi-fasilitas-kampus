# Audit Proyek & Pemetaan UI — Reservasi Fasilitas Kampus Undip

## 1. Arsitektur & Stack Frontend
- **Backend Framework**: Laravel 13 (PHP 8.3+)
- **Database**: MySQL (`reservasi_fasilitas`), 213 automated tests (Pest / PHPUnit) aktif dan 100% lulus.
- **Frontend Stack**:
  - Blade Templates engine murni (server-side rendering).
  - Bootstrap 5.3.3 via CDN (`jsdelivr`).
  - Bootstrap Icons 1.11.3 via CDN.
  - CSS styling kustom via `<style>` di `resources/views/layouts/app.blade.php` dan `@stack('styles')`.
  - Tidak ada dependency build asset Node/Vite (tidak ada `package.json`).
  - **Keputusan**: Pertahankan stack Blade + Bootstrap 5.3 + custom CSS tokens & modular modern styling tanpa menambah framework compile baru yang membebani proyek.

## 2. Pemetaan Routes, Halaman & Hak Akses

### A. Publik & Otentikasi
1. `/` (GET): Saat ini redirect ke `/facilities`. Akan dibangun Landing Page publik premium bertema Undip Tembalang.
2. `/login` (GET/POST) — `login`: Formulir masuk pengguna/petugas/admin (`resources/views/login.blade.php`).
3. `/register` (GET/POST) — `register`: Registrasi mahasiswa, dosen, dan staf (`resources/views/register.blade.php`).
4. `/facilities` (GET) — `facilities.index`: Katalog fasilitas publik dengan filter nama, tipe, lokasi, dan kapasitas min (`resources/views/facilities/index.blade.php`).
5. `/facilities/{facility}` (GET) — `facilities.show`: Halaman detail fasilitas, galeri foto, status aktif/perbaikan/nonaktif, dan matriks ketersediaan slot 7 hari (`resources/views/facilities/show.blade.php`).

### B. Pengguna Mahasiswa/Dosen/Staf (`role:pengguna`)
1. `/reservations/create` (GET/POST) — `reservations.create`, `reservations.store`: Formulir permohonan reservasi fasilitas kampus (`resources/views/reservations/create.blade.php`).
2. `/reservations/availability` (GET) — `reservations.availability`: AJAX ketersediaan slot waktu.
3. `/reservations` (GET) — `reservations.index`: Riwayat dan status reservasi pengguna (`resources/views/reservations/index.blade.php`).
4. `/reservations/{reservation}` (GET) — `reservations.show`: Detail permohonan reservasi (`resources/views/reservations/show.blade.php`).
5. `/reservations/{reservation}/cancel` (PATCH) — `reservations.cancel`: Pembatalan reservasi pending oleh pengguna.
6. `/reports` (GET/POST) — `reports.index`, `reports.store`: Daftar dan pengajuan laporan kerusakan fasilitas (`resources/views/reports/index.blade.php`).
7. `/reports/create` (GET) — `reports.create`: Formulir pelaporan kerusakan dengan unggah foto (`resources/views/reports/create.blade.php`).
8. `/reports/{report}` (GET) — `reports.show`: Detail tindak lanjut laporan (`resources/views/reports/show.blade.php`).

### C. Petugas Sarana & Prasarana (`role:petugas`)
1. `/officer` (GET) — `officer.dashboard`: Ringkasan metrik reservasi, antrian persetujuan, dan fasilitas perbaikan (`resources/views/officer/dashboard.blade.php`).
2. `/officer/reservations` (GET) — `officer.reservations.index`: Antrian validasi reservasi (`resources/views/officer/reservations/index.blade.php`).
3. `/officer/reservations/{reservation}` (GET) — `officer.reservations.show`: Verifikasi detail, cek bentrok jadwal, tombol setujui/tolak (`resources/views/officer/reservations/show.blade.php`).
4. `/officer/reservations/{reservation}/approve` (PATCH) — `officer.reservations.approve`: Aksi persetujuan reservasi.
5. `/officer/reservations/{reservation}/reject` (PATCH) — `officer.reservations.reject`: Aksi penolakan reservasi.
6. `/officer/reports` (GET) — `officer.reports.index`: Antrian laporan kerusakan sarana (`resources/views/officer/reports/index.blade.php`).
7. `/officer/reports/{report}` (GET) — `officer.reports.show`: Tindak lanjut laporan dan penandaan fasilitas dalam perbaikan (`resources/views/officer/reports/show.blade.php`).
8. `/officer/facilities` (GET) — `officer.facilities.index`: Pengelolaan ketersediaan/status operasional fasilitas (`resources/views/officer/facilities/index.blade.php`).

### D. Administrator Kampus (`role:admin`)
1. `/admin/facilities` (GET/POST) — `admin.facilities.index`, `admin.facilities.store`: Master data fasilitas (`resources/views/admin/facilities/index.blade.php`).
2. `/admin/facilities/create` (GET) — `admin.facilities.create`: Tambah fasilitas baru (`resources/views/admin/facilities/form.blade.php`).
3. `/admin/facilities/{facility}/edit` (GET/PATCH) — `admin.facilities.edit`, `admin.facilities.update`: Edit data fasilitas & penanganan dampak reservasi (D4) (`resources/views/admin/facilities/form.blade.php`).
4. `/admin/users` (GET/POST) — `admin.users.index`, `admin.users.store`: Manajemen akun civitas akademika (`resources/views/admin/users/index.blade.php`).
5. `/admin/users/create` (GET) — `admin.users.create`: Buat akun baru (`resources/views/admin/users/create.blade.php`).
6. `/admin/users/{user}` (GET) — `admin.users.show`: Verifikasi identitas civitas (NIM/NIP), aktivasi, suspend (D4 modal), reset password (`resources/views/admin/users/show.blade.php`).
7. `/admin/recap` (GET) — `admin.recap.index`: Rekap data statistik reservasi & laporan (`resources/views/admin/recap/index.blade.php`).
8. `/admin/recap/export` (GET) — `admin.recap.export`: Ekspor data CSV/Excel.

## 3. Komponen UI Eksisting & Snippets
- Layout Utama: `resources/views/layouts/app.blade.php` (navbar, flash message wrapper, header halaman, main container, footer).
- Komponen Peringatan D4: `resources/views/components/d4-warning.blade.php`.
- Snippets UI: `resources/views/components/ui-snippets.blade.php`.

## 4. Evaluasi Visual Awal & Masalah Utama
1. **Identitas Visual Lemah**: Menggunakan default dark-gray navbar (`#1a202c`) dan Bootstrap gray background (`#f5f6f8`), belum memancarkan aura resmi maupun modernitas kampus Universitas Diponegoro (Deep Undip Navy & Warm Gold/Amber).
2. **Tidak Ada Landing Page Publik**: Route `/` langsung mengalihkan ke `/facilities` sehingga pengunjung baru tidak mendapatkan konteks gedung, alur peminjaman, aturan pakai, maupun panduan lokasi kampus Tembalang.
3. **Kartu & Tabel Terlalu Padat / Kaku**: Elemen form dan tabel menggunakan styling Bootstrap default tanpa hierarki tipografi modern, radius yang kaku, serta minim diferensiasi elevasi.
4. **Foto Fasilitas Belum Ada / Placeholder Kosong**: Seluruh card fasilitas saat ini hanya menggunakan placeholder ikon generik tanpa foto asli arsitektur Undip Tembalang.
5. **Aksesibilitas & Feedback**: Kontras warna badge status dan focus ring input form perlu distandarisasi ke WCAG AA.
