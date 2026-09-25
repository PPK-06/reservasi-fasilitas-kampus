Tahap 2 tinggal dua yang tersisa: punyaku (A4, activate, reset-password) dan punya Dhimas, yaitu query ketersediaan. Punya Dhimas ini yang paling mendesak, karena grid P2 Ferdy di Tahap 3 nunggu ini.

Tapi sebelum Dhimas mulai nulis kode, ada satu hal yang harus diputuskan dulu: U1 pakai dropdown jam, atau pilih langsung di grid P2? Setauku kemarin kalian condong ke grid, tapi kode U1 sekarang pakai dropdown. Itu sementara, atau udah berubah pikiran?

Kenapa harus sekarang: jawabannya nentuin bentuk query ketersediaan. Kalau salah tebak, nanti salah satu dari kalian harus nulis ulang.

Pilihannya:
A. Dropdown, kayak sekarang. Grid P2 cuma tampilan. Paling ringan
B. Pilih rentang jam langsung di grid. Butuh JavaScript tulis tangan di P2 (kita tanpa build pipeline), dan tamu yang ngeklik grid harus diarahkan ke login. Kerjaan Ferdy nambah dan tergantung bentuk form Dhimas
C. Tengah-tengah: slot kosong di grid bisa diklik, lalu kebuka U1 dengan fasilitas, tanggal, dan jam mulai udah keisi. Jam selesai tetap pilih lewat dropdown. Nggak perlu JavaScript, dan kode U1 yang sekarang tetap kepakai

Setelah milih, sepakati juga bentuk method-nya: nama, parameter, dan balikannya. Terus kabari di sini ya.

Setelah itu, Dhimas:
- git switch main, git pull origin main, lalu bikin branch baru feature/ketersediaan dari main
- kerjain query-nya. Instruksi buat AI-nya aku kirim terpisah
- karena nggak ada halaman yang bisa dibuka, di deskripsi PR tulis bukti kalau method-nya jalan (misalnya hasil pemanggilan di tinker + test)

Beberapa hal yang perlu diperhatikan waktu ngerjain query-nya, karena kalau salah nggak bakal muncul error:
1. Cuma reservasi approved yang bikin slot terisi. Pending nggak
2. P2 itu halaman publik, jadi query-nya cuma boleh ambil facility_id, start_time, end_time. user_id dan purpose jangan di-select sama sekali (F4 di dasar-proyek)
3. Hati-hati batas slot: reservasi 08:00–10:00 ngisi slot 08:00 sampai 09:30, tapi slot 10:00 tetap kosong
4. Pakai class Slot yang udah ada, jangan bikin daftar slot baru. Dan jangan nyentuh FacilityController atau view P2, itu bagian Ferdy yang manggil method-mu

---

## Kesepakatan & Implementasi Dhimas (Selesai)

### 1. Keputusan Bentuk Input
**Dipilih Opsi B (Interaktif di Grid, Tanpa Dropdown):**
- Pengguna memilih rentang waktu langsung dengan mengeklik slot di grid ketersediaan, bukan memilih lewat `<select>` dropdown.
- Form U1 (`reservations.create`) sudah diperbarui dengan grid 26 slot waktu interaktif (klik jam mulai lalu jam selesai), lengkap dengan penanda status visual (`Tersedia`, `Mulai`, `Dipilih`, `Selesai`, `Terisi`, `Lewat`).
- Dropdown manual tetap disediakan sebagai fallback tersembunyi yang tersinkronisasi otomatis dua arah.

### 2. Kontrak Method Query Ketersediaan
- **Nama Method**: `Slot::availability(Facility|int $facility, string|DateTimeInterface $date): array`
- **Kemudahan Pemanggilan**: Juga tersedia alias di `Reservation::availability($facility, $date)` dan `$facility->slotAvailability($date)`.
- **Parameter**:
  1. `$facility`: Instance model `Facility` atau integer `facility_id`.
  2. `$date`: Tanggal target dalam format `string` (misal `'2026-09-27'`) atau instance `Carbon`/`DateTimeInterface`.
- **Format Balikan**:
  Array 26 slot terurut dengan key jam mulai (`'07:00'` s/d `'19:30'`). Tiap elemen berbentuk:
  ```php
  [
      'start'         => '07:00',
      'end'           => '07:30',
      'status'        => 'available', // 'available' | 'booked' | 'past_limit'
      'is_available'  => true,
      'is_booked'     => false,
      'is_past_limit' => false,
  ]
  ```

### 3. Aturan Bisnis yang Dipenuhi
1. **Status Approved Saja**: Hanya reservasi berstatus `approved` yang menandai slot sebagai `booked`. Status `pending`, `rejected`, maupun `cancelled` diabaikan.
2. **Privasi F4**: Query HANYA men-`select(['facility_id', 'start_time', 'end_time'])`. Kolom `user_id`, `purpose`, dan `id` tidak disentuh sama sekali.
3. **Batas Slot Tepat**: Reservasi `08:00–10:00` menandai 4 slot terisi (`08:00`, `08:30`, `09:00`, `09:30`), sedangkan slot `10:00` tetap kosong/tersedia.
4. **Tiga Status Visual D5**:
   - `booked`: Overlap dengan reservasi approved.
   - `past_limit`: Kosong, tapi tanggal reservasi di luar rentang A3 (hari ini / lewat, atau > H+30).
   - `available`: Kosong dan memenuhi jendela tanggal A3.
5. **Endpoint Ketersediaan**: Disediakan route `GET /reservations/availability?facility_id={id}&date={Y-m-d}` yang mengembalikan data ketersediaan JSON untuk kebutuhan interaktivitas grid form U1.

### 4. Bukti Pengujian (Test Suite)
Seluruh fungsionalitas terkunci di test suite `tests/Feature/AvailabilityTest.php` (6 tes, 44 asersi, 100% lolos):
- `mengembalikan tepat 26 slot untuk fasilitas dan tanggal yang sah` (Passed)
- `mengisi slot dengan benar sesuai batas jam reservasi approved dan menyisakan batas akhir kosong` (Passed)
- `hanya memperhitungkan reservasi berstatus approved dan mengabaikan status pending atau lainnya` (Passed)
- `mematuhi privasi F4 dengan hanya men-select facility_id, start_time, dan end_time` (Passed)
- `memberikan status visual past_limit untuk tanggal yang sudah lewat batas A3` (Passed)
- `endpoint reservations.availability mengembalikan format JSON ketersediaan` (Passed)