# Baseline Pengujian Wiyata Sebelum Perbaikan Bug

- **START_SHA**: `1b7d562fd9a1cc26528fd90e57dd518de2c8e707`
- **Branch**: `ui-redisign`
- **Tanggal/Waktu Eksekusi**: 2026-10-10

## 1. Status Dependensi & Lingkungan
- PHP: 8.5.0 (cli)
- Composer: 2.8.12
- Node.js: v26.7.0 (tidak ada `package.json` di root repository)
- Frontend: Bootstrap 5.3 CDN, Bootstrap Icons CDN, Vanilla CSS di Blade template (tanpa pipeline build Vite/NPM)

## 2. Hasil `npm run build`
- Perintah: `npm run build`
- Status: Gagal / Not Applicable (`ENOENT: no such file or directory, open 'package.json'`)
- Catatan: Proyek Wiyata menggunakan Bootstrap CDN & asset vanilla publik tanpa build step Node/Vite.

## 3. Hasil `php artisan test`

### Skenario A: Default `php artisan test` (SQLite In-Memory)
- Menggunakan konfigurasi `phpunit.xml` default (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`).
- Hasil: 1 lulus, 182 skipped, 30 gagal (Error: `no such table: facilities` / `no such table: users` karena test suite dirancang untuk database MySQL lokal yang telah dimigrasi sesuai catatan di `tests/Feature/RoleMiddlewareTest.php`).

### Skenario B: `DB_CONNECTION=mysql DB_DATABASE=reservasi_fasilitas php artisan test`
- Perintah: `DB_CONNECTION=mysql DB_DATABASE=reservasi_fasilitas php artisan test`
- Total Tests: 213 tests
- Passed: 212 tests
- Failed: 1 test
- Assertions: 957 assertions
- Durasi: ~5.1 detik
- Rincian Kegagalan Eksisting (Baseline Pre-existing Failure - Di luar scope, jangan diubah):
  - Test: `P\Tests\Feature\AdminUserIndexTest::__pest_evaluable_it_mengurutkan_akun_pending_di_atas__lalu_created__at_menurun`
  - Lokasi: `tests/Feature/AdminUserIndexTest.php:183`
  - Pesan: `Failed asserting that two arrays are identical.` (Urutan akun `suspended` vs `rejected` berbeda pada tie-breaker `created_at`).
