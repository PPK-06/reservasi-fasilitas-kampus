# Wiyata: Sistem Reservasi Fasilitas Kampus Undip Tembalang

Wiyata adalah platform web terpadu untuk peminjaman sarana dan prasarana kampus serta pelaporan kerusakan fasilitas di lingkungan Universitas Diponegoro (Undip), Kampus Tembalang.

## Fitur Utama

- **Katalog Sarana & Prasarana**: Pencarian dan penyaringan ruangan (aula, laboratorium, ruang kelas), lapangan olahraga, serta peralatan (proyektor, sound system).
- **Alur Reservasi Terstruktur**: Pengecekan ketersediaan slot waktu secara real-time untuk mencegah konflik jadwal.
- **Validasi & Verifikasi Multi-Peran**:
  - **Pengguna (Civitas Akademika)**: Eksplorasi katalog, pengajuan peminjaman, tracking status, dan pelaporan kendala fasilitas.
  - **Petugas**: Pemeriksaan antrian reservasi, persetujuan/penolakan jadwal, dan tindak lanjut laporan kerusakan.
  - **Admin**: Manajemen data master fasilitas, verifikasi akun civitas, serta rekapitulasi data.
- **Desain Institusional Modern**: Mengusung identitas visual Universitas Diponegoro (Deep Undip Navy & Diponegoro Warm Gold) yang bersih, responsif, aksesibel, dan bebas dari pola AI generik.

## Persyaratan Sistem

- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL / MariaDB

## Instalasi & Menjalankan Aplikasi

1. **Clone repositori dan pasang dependensi**:
   ```bash
   composer install
   npm install
   ```

2. **Konfigurasi Environment**:
   Salin berkas `.env.example` ke `.env`:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Pastikan pengaturan `APP_NAME="Wiyata"` dan koneksi database MySQL sudah disesuaikan pada file `.env`.

3. **Migrasi dan Seeding Database**:
   ```bash
   php artisan migrate --seed
   ```

4. **Kompilasi Aset Frontend**:
   ```bash
   npm run build
   ```

5. **Jalankan Server Lokal**:
   ```bash
   php artisan serve
   ```

## Pengujian

Jalankan test suite menggunakan Pest:
```bash
DB_CONNECTION=mysql DB_DATABASE=reservasi_fasilitas vendor/bin/pest
```

## Lisensi

Proyek ini dikembangkan untuk keperluan akademik di lingkungan Universitas Diponegoro.
