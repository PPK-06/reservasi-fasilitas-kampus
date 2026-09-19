# Route, Konvensi Penamaan & Kontrak Form
## Sistem Reservasi & Pelaporan Fasilitas Kampus

**Mata kuliah:** Pengembangan Platform Khusus (PPK)
**Versi dokumen:** 1.1 — 18 September 2026

**Dokumen induk:** `dasar-proyek.md` — aturan bisnis yang mengikat
**Dokumen pendamping:**
- `halaman-navigasi-dan-skema.md` — daftar halaman, navigasi, skema tabel
- `pembagian-modul-dan-urutan-kerja.md` — kepemilikan file dan urutan kerja
- `keputusan-m1-tahap1.md` — keputusan perilaku middleware role dan alur login

Dokumen ini menerjemahkan keduanya ke **route, nama file, dan aturan validasi**. Kalau ada yang terasa bertentangan, dokumen induk yang menang, dan perbedaannya dilaporkan ke tim supaya salah satunya diperbaiki.

Kode halaman (P1, U1, O1, A1, …) mengacu ke dokumen pendamping.

---

## Perubahan dari v1.0

Semuanya lahir saat P3 Login dikerjakan, dari hal yang ternyata belum tercakup.

| Bagian | Perubahan |
|---|---|
| Header | Nama file dokumen tidak lagi memuat nomor versi; rujukan antar-dokumen jadi stabil |
| 5 | + pengecualian kedua: route yang namanya **tidak punya titik** (`login`, `register`) merender view di akar `resources/views/` |
| 15 | + kunci error bag untuk pesan kegagalan login, dan + penegasan cara kerja tujuan setelah login berhasil |
| 30 | Diperbarui mengikuti keadaan sekarang |

---

## Cara memakai dokumen ini

Dua dokumen sebelumnya adalah **bacaan** — dibaca sekali, jadi rujukan saat ragu. Dokumen ini **acuan kerja** — dibuka berkali-kali sambil ngoding, oleh empat orang sekaligus.

**Untuk anggota tim:** nama route di Bagian II bersifat final. Kamu akan memakai nama route milik modul orang lain (tombol di P2 mengarah ke U1 dan U4), jadi jangan menunggu modul itu jadi — nama route-nya sudah bisa dipakai sekarang. Kalau ada yang terasa keliru, bahas di grup, jangan diam-diam memakai nama sendiri.

**Untuk AI/asisten coding:** perlakukan isi dokumen ini sebagai keputusan yang mengikat. Jangan menawarkan konvensi penamaan atau struktur route alternatif. Kalau sebuah kebutuhan tidak tercakup, tanyakan ke pengguna.

---

# BAGIAN I — KONVENSI PENAMAAN

## 1. Aturan induk

> Nama route, controller, dan view mengikuti **nama tabel**, dan dikelompokkan berdasarkan **role yang mengaksesnya**.

Bahasa yang dipakai untuk nama route, controller, view, dan variabel adalah **Inggris**, mengikuti nama tabel yang sudah jalan di migration. Bahasa Indonesia dipakai untuk teks yang dilihat pengguna (label, pesan error, judul halaman).

Role jadi pengelompok utama karena F4 sudah memaksa pemisahan di level route. Sekalian itu membuat pembagian kerja rapi: satu folder controller ≈ satu bagian modul.

| Role | Prefix route | Prefix URI | Folder controller |
|---|---|---|---|
| Publik & pengguna | *(tanpa prefix)* | *(tanpa prefix)* | `Controllers/` |
| Petugas | `officer.` | `/officer` | `Controllers/Officer/` |
| Admin | `admin.` | `/admin` | `Controllers/Admin/` |

Pengguna tidak punya prefix sendiri karena ia berbagi halaman dengan pengunjung (P1 dan P2 dipakai keduanya). Yang membedakan hanya middleware pada route yang butuh login.

Petugas dan admin tidak digabung menjadi satu prefix `staff.` karena D3 menetapkan matriks wewenang yang berbeda. Pemisahan di level URI membuat middleware-nya sederhana — satu `Route::prefix` per role, bukan pengecekan per route.

## 2. Controller

Pola: `{Entitas}Controller`, **singular**, PascalCase. Dua belas controller untuk 22 halaman.

| Controller | Halaman | Modul pemilik |
|---|---|---|
| `AuthController` | P3 | Auth & Akun |
| `RegisterController` | P4 | Auth & Akun |
| `FacilityController` | P1, P2 | Fasilitas |
| `ReservationController` | U1, U2, U3 | Reservasi |
| `ReportController` | U4, U5, U6 | Laporan & Rekap |
| `Officer\DashboardController` | O1 | Reservasi |
| `Officer\ReservationController` | O2, O3 | Reservasi |
| `Officer\ReportController` | O4, O5 | Laporan & Rekap |
| `Officer\FacilityController` | O6 | Fasilitas |
| `Admin\FacilityController` | A1, A2 | Fasilitas |
| `Admin\UserController` | A3, A4, A5 | Auth & Akun |
| `Admin\RecapController` | A6 | Laporan & Rekap |

Nama boleh sama asal beda namespace — `FacilityController`, `Officer\FacilityController`, dan `Admin\FacilityController` adalah tiga kelas berbeda. Ini justru disengaja: nama yang sama menandakan mereka mengurus entitas yang sama dari sudut wewenang yang berbeda.

**Tidak ada satu pun controller yang dipegang dua orang.** Itu syarat supaya commit tiap orang tidak bertabrakan, dan supaya kontribusi per anggota terlihat jelas saat penilaian.

## 3. Method controller

Pakai tujuh nama baku Laravel lebih dulu, baru membuat nama sendiri kalau tidak ada yang cocok.

| Method | HTTP | Untuk |
|---|---|---|
| `index` | GET | Daftar |
| `create` | GET | Menampilkan form tambah |
| `store` | POST | Menyimpan hasil form tambah |
| `show` | GET | Detail satu baris |
| `edit` | GET | Menampilkan form edit |
| `update` | PATCH | Menyimpan hasil form edit |
| `destroy` | DELETE | **Tidak dipakai sama sekali** |

Ketiadaan `destroy` di seluruh sistem adalah penerapan C6 dan D6 yang melarang penghapusan permanen, bukan kelalaian. Sebut demikian kalau ditanya.

Untuk aksi yang bukan CRUD, pakai nama kata kerjanya sendiri dengan method **PATCH**:

```
approve, reject, cancel, verify, suspend, activate, resetPassword, status
```

PATCH dipilih, bukan POST, karena yang terjadi adalah perubahan sebagian pada baris yang sudah ada — konsisten dengan `update`.

## 4. Nama route

Pola: `{prefix}{entitas}.{aksi}` — huruf kecil semua, entitas **jamak** mengikuti nama tabel.

```
facilities.index                 →  P1
facilities.show                  →  P2
reservations.create              →  U1
reservations.store               →  U1 submit
reservations.cancel              →  U3 tombol batalkan
officer.reservations.approve     →  O3 tombol setujui
admin.users.reset-password       →  A5 aksi reset password
```

Segmen yang lebih dari satu kata memakai **kebab-case** (`reset-password`), bukan camelCase, supaya nama route dan URI-nya sama persis dan tidak perlu dihafal dua versi. Nama method controller-nya tetap camelCase karena itu aturan PHP: `admin.users.reset-password` → `Admin\UserController@resetPassword`.

**Semua route wajib punya nama, dan di Blade selalu dipanggil lewat `route()`.**

```blade
{{-- benar --}}
<a href="{{ route('facilities.show', $facility) }}">

{{-- salah --}}
<a href="/facilities/{{ $facility->id }}">
```

Alasannya praktis: pemegang modul Fasilitas menulis tombol di P2 yang mengarah ke U1 milik pemegang modul Reservasi. Kalau URI-nya berubah, yang memakai nama route tidak ikut rusak.

## 5. View

Path view **mencerminkan nama route**, titik diganti garis miring.

| Route | View |
|---|---|
| `facilities.index` | `resources/views/facilities/index.blade.php` |
| `officer.reservations.show` | `resources/views/officer/reservations/show.blade.php` |
| `admin.users.create` | `resources/views/admin/users/create.blade.php` |

Aturan ini membuat pencarian file view tidak pernah perlu: baca nama route, kamu tahu path-nya.

**Pengecualian pertama, sudah ditetapkan dokumen pendamping:** A2 adalah satu form untuk mode tambah dan edit. `admin.facilities.create` dan `admin.facilities.edit` sama-sama merender `admin/facilities/form.blade.php`. Ini satu-satunya tempat dua route berbagi satu view.

**Pengecualian kedua, ditetapkan v1.1:** dua nama route tidak punya titik sama sekali, karena tidak menempel pada nama tabel mana pun.

| Route | View |
|---|---|
| `login` | `resources/views/login.blade.php` |
| `register` | `resources/views/register.blade.php` |

Ini bukan penyimpangan, melainkan penerapan harfiah aturan di atas: tidak ada titik, jadi tidak ada folder. Ditulis di sini supaya tidak ada yang "merapikannya" jadi `auth/login.blade.php` belakangan dan membuat dua halaman auth punya konvensi berbeda.

Folder tambahan:

```
resources/views/layouts/app.blade.php      ← layout induk + navbar
resources/views/components/                ← Blade component bersama
```

## 6. Form Request

Validasi tidak ditulis di controller, melainkan di class terpisah pada `app/Http/Requests/`. Pola: `{Store|Update}{Entitas}Request`.

```
LoginRequest                  ← P3
RegisterRequest               ← P4
StoreReservationRequest       ← U1
StoreReportRequest            ← U4
StoreFacilityRequest          ← A2 mode tambah
UpdateFacilityRequest         ← A2 mode edit
StoreUserRequest              ← A4
```

Aturan validasi jadi terkumpul di satu tempat dan bisa dibaca tanpa mengurai controller. Saat tanya jawab, pertanyaan "mana validasi sisi server-nya?" dijawab dengan membuka satu file.

## 7. Komponen bersama dan pemiliknya

Tiga hal berikut dipakai lintas modul. Kalau tidak ditunjuk pemiliknya, akan ditulis dua kali oleh dua orang lalu bentrok saat merge.

| Komponen | Dipakai di | Pemilik |
|---|---|---|
| Middleware role (`role:pengguna`, `role:petugas`, `role:admin`) | Semua route pengguna, officer, admin | Auth & Akun |
| Layout induk `layouts/app.blade.php` + navbar | Semua halaman | Fasilitas |
| Komponen peringatan D4 | O5, O6, A1, A5 | Reservasi |

Dokumen pendamping sudah menandai komponen D4 sebagai hal yang harus disepakati di awal. Dua yang lain ditambahkan di sini karena masalahnya sama persis.

**Navbar perlu perhatian ekstra.** Isinya berbeda per role, dan keempat orang akan ingin menambahkan link modulnya sendiri. Sepakati bahwa pemilik layout menyiapkan kerangkanya **lengkap di awal**, dengan semua link mengarah ke nama route di Bagian II — termasuk route yang belum dibuat. Dengan begitu tiga orang lain tidak perlu menyentuh file itu sama sekali.

---

# BAGIAN II — DAFTAR ROUTE

**22 halaman menghasilkan 45 route.** Satu halaman form menghasilkan dua route (GET menampilkan, POST menyimpan), dan halaman detail sering punya beberapa tombol aksi.

Kolom **Tujuan** adalah ke mana pengguna berakhir setelah aksi berhasil.

## 8. Publik — tanpa prefix

| Halaman | Method | URI | Nama route | Controller@method | Middleware | Tujuan |
|---|---|---|---|---|---|---|
| — | GET | `/` | *(redirect)* | — | — | → `/facilities` |
| P1 | GET | `/facilities` | `facilities.index` | `FacilityController@index` | — | P1 |
| P2 | GET | `/facilities/{facility}` | `facilities.show` | `FacilityController@show` | — | P2 |
| P3 | GET | `/login` | `login` | `AuthController@create` | `guest` | P3 |
| P3 | POST | `/login` | `login.store` | `AuthController@store` | `guest` | P1 \| O1 \| A3 sesuai role |
| — | POST | `/logout` | `logout` | `AuthController@destroy` | `auth` | P1 |
| P4 | GET | `/register` | `register` | `RegisterController@create` | `guest` | P4 |
| P4 | POST | `/register` | `register.store` | `RegisterController@store` | `guest` | P3 + pesan menunggu verifikasi |

**Catatan:**

- Baris pertama memakai `Route::redirect('/', '/facilities')`, tanpa controller. URL root berfungsi tanpa membuat P1 punya dua alamat berbeda
- **Logout wajib POST, bukan GET.** Kalau GET, satu tag `<img src="/logout">` di halaman mana pun bisa memaksa orang keluar. Di Blade bentuknya form kecil dengan `@csrf`, bukan `<a href>`
- Middleware `guest` mencegah orang yang sudah login membuka halaman login lagi
- `login.store` bercabang ke tiga tujuan sesuai H1. Logika percabangannya di `AuthController@store`

## 9. Pengguna — `auth` + `role:pengguna`

| Halaman | Method | URI | Nama route | Controller@method | Tujuan |
|---|---|---|---|---|---|
| U1 | GET | `/reservations/create` | `reservations.create` | `ReservationController@create` | U1 |
| U1 | POST | `/reservations` | `reservations.store` | `ReservationController@store` | U3 (reservasi baru) |
| U2 | GET | `/reservations` | `reservations.index` | `ReservationController@index` | U2 |
| U3 | GET | `/reservations/{reservation}` | `reservations.show` | `ReservationController@show` | U3 |
| U3 | PATCH | `/reservations/{reservation}/cancel` | `reservations.cancel` | `ReservationController@cancel` | U3 |
| U4 | GET | `/reports/create` | `reports.create` | `ReportController@create` | U4 |
| U4 | POST | `/reports` | `reports.store` | `ReportController@store` | U6 (laporan baru) |
| U5 | GET | `/reports` | `reports.index` | `ReportController@index` | U5 |
| U6 | GET | `/reports/{report}` | `reports.show` | `ReportController@show` | U6 |

**Catatan:**

- Seluruh blok memakai `role:pengguna`, bukan sekadar `auth`. Petugas dan admin tidak boleh mengajukan reservasi — C3 menetapkan role eksklusif
- `reservations.create` dan `reports.create` menerima query string opsional `?facility=3` saat datang dari P2. Ini penerapan "U1 harus berfungsi dalam dua keadaan" — satu kondisi di Blade, bukan dua halaman
- `reservations.show` dan `reports.show` **wajib dijaga Laravel Policy**, bukan `if` di controller (F4). Route-nya sendiri tidak tahu soal kepemilikan
- **Tidak ada `edit` dan `update`** untuk reservasi dan laporan. Tidak ada user story yang membolehkan pengguna mengubah pengajuan yang sudah masuk — kalau salah, batalkan lalu ajukan ulang

## 10. Petugas — `auth` + `role:petugas`, prefix `/officer`

| Halaman | Method | URI | Nama route | Controller@method | Tujuan |
|---|---|---|---|---|---|
| O1 | GET | `/officer` | `officer.dashboard` | `Officer\DashboardController@index` | O1 |
| O2 | GET | `/officer/reservations` | `officer.reservations.index` | `Officer\ReservationController@index` | O2 |
| O3 | GET | `/officer/reservations/{reservation}` | `officer.reservations.show` | `Officer\ReservationController@show` | O3 |
| O3 | PATCH | `/officer/reservations/{reservation}/approve` | `officer.reservations.approve` | `Officer\ReservationController@approve` | O2 \| O3 + pesan bentrok |
| O3 | PATCH | `/officer/reservations/{reservation}/reject` | `officer.reservations.reject` | `Officer\ReservationController@reject` | O2 |
| O3 | PATCH | `/officer/reservations/{reservation}/cancel` | `officer.reservations.cancel` | `Officer\ReservationController@cancel` | O2 |
| O4 | GET | `/officer/reports` | `officer.reports.index` | `Officer\ReportController@index` | O4 |
| O5 | GET | `/officer/reports/{report}` | `officer.reports.show` | `Officer\ReportController@show` | O5 |
| O5 | PATCH | `/officer/reports/{report}` | `officer.reports.update` | `Officer\ReportController@update` | O4 |
| O6 | GET | `/officer/facilities` | `officer.facilities.index` | `Officer\FacilityController@index` | O6 |
| O5, O6 | PATCH | `/officer/facilities/{facility}/status` | `officer.facilities.status` | `Officer\FacilityController@status` | kembali ke halaman asal |

**Catatan:**

- **Tiga aksi terpisah** untuk approve/reject/cancel, bukan satu `update` berparameter status. Aturan validasinya berbeda total: `reject` dan `cancel` mewajibkan `status_reason` minimal 10 karakter (A7), sementara `approve` menjalankan transaksi + row lock (F2). Satu method yang menangani ketiganya akan jadi rantai `if` yang sulit dibedah saat tanya jawab
- `officer.reports.update` sebaliknya **satu method**, karena keempat status laporan alurnya sama — yang berubah hanya kewajiban `resolution_note` (B3)
- `officer.facilities.status` adalah **satu route yang dipakai dua halaman.** US 12 punya dua arah masuk: menandai dalam perbaikan dari O5 (laporan yang sedang ditangani), mengembalikan ke aktif dari O6. Aksinya identik, jadi route-nya satu; controller mengembalikan pengguna ke halaman asal lewat `back()`
- Server **wajib menolak** approve dan reject untuk reservasi `pending` yang `start_time`-nya sudah lewat (A8). Tombol yang di-*disable* di tab Terlewat bukan penjaga

## 11. Admin — `auth` + `role:admin`, prefix `/admin`

| Halaman | Method | URI | Nama route | Controller@method | Tujuan |
|---|---|---|---|---|---|
| A1 | GET | `/admin/facilities` | `admin.facilities.index` | `Admin\FacilityController@index` | A1 |
| A2 | GET | `/admin/facilities/create` | `admin.facilities.create` | `Admin\FacilityController@create` | A2 |
| A2 | POST | `/admin/facilities` | `admin.facilities.store` | `Admin\FacilityController@store` | A1 |
| A2 | GET | `/admin/facilities/{facility}/edit` | `admin.facilities.edit` | `Admin\FacilityController@edit` | A2 (mode edit) |
| A2 | PATCH | `/admin/facilities/{facility}` | `admin.facilities.update` | `Admin\FacilityController@update` | A1 |
| A1 | PATCH | `/admin/facilities/{facility}/status` | `admin.facilities.status` | `Admin\FacilityController@status` | A1 |
| A3 | GET | `/admin/users` | `admin.users.index` | `Admin\UserController@index` | A3 |
| A4 | GET | `/admin/users/create` | `admin.users.create` | `Admin\UserController@create` | A4 |
| A4 | POST | `/admin/users` | `admin.users.store` | `Admin\UserController@store` | A3 |
| A5 | GET | `/admin/users/{user}` | `admin.users.show` | `Admin\UserController@show` | A5 |
| A5 | PATCH | `/admin/users/{user}/verify` | `admin.users.verify` | `Admin\UserController@verify` | A3 |
| A5 | PATCH | `/admin/users/{user}/reject` | `admin.users.reject` | `Admin\UserController@reject` | A3 |
| A5 | PATCH | `/admin/users/{user}/suspend` | `admin.users.suspend` | `Admin\UserController@suspend` | A5 |
| A5 | PATCH | `/admin/users/{user}/activate` | `admin.users.activate` | `Admin\UserController@activate` | A5 |
| A5 | PATCH | `/admin/users/{user}/reset-password` | `admin.users.reset-password` | `Admin\UserController@resetPassword` | A5 |
| A6 | GET | `/admin/recap` | `admin.recap.index` | `Admin\RecapController@index` | A6 |
| A6 | GET | `/admin/recap/export` | `admin.recap.export` | `Admin\RecapController@export` | unduh berkas CSV |

**Catatan:**

- **Empat aksi status akun memetakan langsung ke matriks transisi C2**, bukan dipecah secara asal:

| Route | Transisi |
|---|---|
| `verify` | `pending` → `verified` |
| `reject` | `pending` → `rejected` |
| `suspend` | `verified` → `suspended` |
| `activate` | `rejected` → `verified`, `suspended` → `verified` |

  Tidak ada satu pun yang mengembalikan akun ke `pending`, sesuai larangan C2. Setiap method **wajib memeriksa status asal di server** — tombol yang tidak ditampilkan di Blade bukan penjaga.

- `admin.facilities.status` menangani `active` ↔ `inactive` (wewenang admin), sementara `officer.facilities.status` menangani `active` ↔ `under_maintenance` (wewenang bersama). **Dua route terpisah untuk dua wewenang berbeda** — penerapan matriks D3. Kalau digabung jadi satu route, petugas memperoleh wewenang milik admin
- **Tidak ada `admin.facilities.destroy`.** D6 hanya menyebut tambah/edit/nonaktifkan
- `admin.recap.export` memakai GET karena tidak mengubah apa pun: ia membaca dengan filter yang sama lalu mengirim file. Query string-nya identik dengan `admin.recap.index`, dan itulah yang menjamin export memakai rentang tanggal yang sama persis (E1)

## 12. Cara memperoleh data peringatan D4

Dokumen pendamping menetapkan peringatan D4 berupa modal berisi daftar reservasi `approved` yang belum lewat, masing-masing dengan tautan ke O3 — bukan `confirm()` browser, karena isinya data.

Di O6 dan A1 halamannya berisi daftar fasilitas dengan tombol status di setiap baris. **Keputusan: seluruh modal dirender sekaligus saat halaman dibuka**, tanpa JavaScript dan tanpa route tambahan.

Datanya diambil satu kali per halaman, dikelompokkan per fasilitas:

```sql
SELECT facility_id, id, start_time, end_time, user_id
FROM reservations
WHERE status = 'approved' AND start_time > NOW()
```

Alasan: proyek ini tanpa build pipeline, fasilitasnya hanya puluhan, dan satu query yang dikelompokkan sudah cukup. Alternatif yang ditolak adalah route GET terpisah yang mengembalikan potongan HTML lewat `fetch()` — menambah route dan menambah JavaScript untuk keuntungan yang tidak terasa pada volume data ini.

Harga yang diterima: HTML halaman A1 dan O6 membengkak seukuran jumlah reservasi mendatang. Pada skala proyek ini, tidak berdampak.

## 13. Hal teknis yang berlaku untuk semua route

**Route model binding.** `{facility}`, `{reservation}`, `{report}`, `{user}` otomatis diubah Laravel menjadi objek model berdasarkan `id`. Kalau ID tidak ada di database, Laravel mengeluarkan 404 tanpa kode tambahan. Syaratnya nama parameter di route sama persis dengan nama variabel di method controller:

```php
Route::get('/facilities/{facility}', [FacilityController::class, 'show']);

public function show(Facility $facility) { ... }
```

**Method spoofing.** Browser hanya bisa mengirim GET dan POST. Untuk PATCH, form-nya POST dengan penanda tambahan:

```blade
<form method="POST" action="{{ route('reservations.cancel', $reservation) }}">
    @csrf
    @method('PATCH')
</form>
```

Lupa `@csrf` menghasilkan error 419 yang membingungkan — ini kesalahan yang paling sering muncul di awal.

**Urutan penulisan di `routes/web.php`.** Kelompokkan per role, dan **route statis harus berada di atas route berparameter**:

```php
Route::get('/reservations/create', ...);         // ini lebih dulu
Route::get('/reservations/{reservation}', ...);  // baru ini
```

Kalau terbalik, Laravel menganggap `create` sebagai ID dan mencari `Reservation::find('create')`.

**Aturan bersama:** file ini disentuh keempat orang. Setiap orang hanya menambah di blok modulnya sendiri, dan **tidak merapikan blok orang lain**. Konflik merge di file ini paling sering terjadi karena dua orang mengubah baris yang sama karena alasan kosmetik.

---

# BAGIAN III — KONTRAK FORM

Dua belas form, dilayani tujuh Form Request class. Sisanya validasinya terlalu ringan untuk butuh class tersendiri.

## 14. Prinsip

> **Server adalah satu-satunya sumber kebenaran. Validasi client hanya mempercepat umpan balik.**

Aturan Implementasi poin 2 mewajibkan validasi di dua sisi, tapi keduanya tidak setara. Validasi client dapat dilewati siapa pun dengan mematikan JavaScript atau mengirim POST langsung. Karena itu:

- Setiap aturan yang ada di client **wajib** punya padanan di server
- Tidak semua aturan server perlu ada di client — yang butuh query database (cek status fasilitas, cek email unik) memang tidak bisa

Bentuk validasi client: atribut HTML5 (`required`, `min`, `max`, `maxlength`, `accept`) ditambah sedikit JavaScript untuk yang tidak bisa diwakili atribut. Tanpa library validasi tambahan, sesuai keputusan tanpa build pipeline.

**Semua form wajib memuat `@csrf`.**

## 15. P3 — Login (`LoginRequest`)

| Field | Tipe input | Aturan server | Client |
|---|---|---|---|
| `email` | email | `required\|email\|max:150` | `required type="email"` |
| `password` | password | `required\|string` | `required` |

Tidak ada `min:8` di sini — itu aturan saat **membuat** password, bukan saat memasukkannya. Kalau dipasang, akun dengan password lama yang lebih pendek akan ditolak dengan pesan yang salah.

**Di luar validasi, controller wajib memeriksa `users.status`** setelah kredensial benar. C2 mensyaratkan pesan spesifik per status, bukan "email atau password salah":

| Status | Pesan |
|---|---|
| `pending` | Akun Anda masih menunggu verifikasi admin. |
| `rejected` | Registrasi Anda ditolak. Hubungi admin untuk klarifikasi. |
| `suspended` | Akun Anda dinonaktifkan. Hubungi admin. |

Status akun bukan masalah kredensial. Menyamarkannya hanya membuat orang mencoba password berulang kali.

**Pemeriksaan status dilakukan sebelum session dibuat**, bukan sesudah. Akun `pending`, `rejected`, dan `suspended` tidak boleh pernah login walau sepersekian detik.

**Kunci error bag: `email`** — ditetapkan v1.1. Tiga pesan status di atas, pesan kredensial salah, dan pesan penolakan dari middleware role semuanya dikirim lewat kunci yang sama. Halaman P3 hanya punya dua field dan tidak menyediakan tempat untuk pesan level-form, jadi satu kunci berarti satu lokasi tampilan yang melayani semua sumber. Ini juga pola bawaan Laravel.

**Tujuan setelah login berhasil.** Kolom Tujuan di bagian 8 menulis "P1 | O1 | A3 sesuai role", dan peta navigasi 2.1 dokumen pendamping menulis `P3 (?redirect=...)`. Keduanya benar dan berlaku bersamaan lewat mekanisme `intended` bawaan Laravel:

- Orang yang tiba di P3 karena menekan tombol di P2 dikembalikan ke halaman yang ia tuju
- Orang yang membuka P3 langsung dari navbar mendarat di landing H1 sesuai role-nya

Tidak ada query param `?redirect=` yang ditulis sendiri. Lihat catatan di peta navigasi 2.1 dokumen pendamping.

## 16. P4 — Registrasi mandiri (`RegisterRequest`)

| Field | Tipe | Aturan server | Client |
|---|---|---|---|
| `name` | text | `required\|string\|max:100` | `required maxlength="100"` |
| `email` | email | `required\|email\|max:150\|unique:users,email` | `required type="email"` |
| `password` | password | `required\|string\|min:8\|confirmed` | `required minlength="8"` |
| `password_confirmation` | password | *(dicek oleh `confirmed`)* | `required` + cocokkan via JS |
| `identity_number` | text | `required\|string\|max:30\|unique:users,identity_number` | `required maxlength="30"` |
| `user_type` | select | `required\|in:mahasiswa,dosen,staf` | `required` |

`confirmed` mencari field bernama `{nama}_confirmation` secara otomatis. Nama `password_confirmation` harus persis demikian.

`identity_number` bersifat `required` di sini, berbeda dengan A4 yang memakai `required_if`. Alasannya: form ini hanya dipakai role `pengguna`, dan NIM/NIP justru bahan utama yang diverifikasi admin (C4). Pendaftar tanpa NIM tidak dapat diverifikasi.

**`role` dan `status` tidak ada di form.** Keduanya diisi controller dengan `pengguna` dan `pending`. Kalau ikut form, orang bisa mendaftar sebagai admin dengan mengubah HTML.

## 17. U1 — Ajukan reservasi (`StoreReservationRequest`)

Form terpenting di sistem ini.

### 17.1 Bentuk field

Dokumen pendamping bagian 4 menyebut bentuk UI-nya belum diputuskan (dua dropdown slot atau pemilihan langsung di grid). **Keputusan itu tidak memengaruhi kontrak ini** — apa pun UI-nya, yang dikirim ke server tetap nilai yang sama; grid hanya mengisi hidden input lewat JavaScript. Pemegang modul Reservasi bebas memutuskan bentuk UI belakangan.

| Field | Tipe | Isi |
|---|---|---|
| `facility_id` | select / hidden | ID fasilitas |
| `date` | date | Tanggal penggunaan |
| `start_slot` | select / hidden | Jam mulai, `07:00` … `19:30` (26 nilai) |
| `end_slot` | select / hidden | Jam selesai, `07:30` … `20:00` (26 nilai) |
| `purpose` | text | Tujuan penggunaan |

**Kenapa satu field tanggal + dua field slot, bukan dua field datetime:** bentuk inilah yang membuat tiga dari tujuh aturan menjadi **mustahil dilanggar secara struktural**, bukan sekadar ditolak validasi. Satu field tanggal berarti tidak ada cara mengirim reservasi yang melintasi dua tanggal. Daftar slot yang terbatas berarti tidak ada cara mengirim jam 09.17.

`start_time` dan `end_time` disusun controller dari ketiga field itu sebelum disimpan.

### 17.2 Kedelapan aturan dan pesannya

| # | Aturan | Asal | Ditegakkan oleh | Pesan error |
|---|---|---|---|---|
| 1 | `start_time` kelipatan 30 menit, detik = 0 | F1 | `Rule::in` 26 nilai slot | Jam mulai harus kelipatan 30 menit. |
| 2 | `end_time` kelipatan 30 menit, detik = 0 | F1 | `Rule::in` 26 nilai slot | Jam selesai harus kelipatan 30 menit. |
| 3 | Keduanya dalam 07.00–20.00 | F1 | `Rule::in` (daftarnya hanya berisi jam operasional) | Reservasi hanya dapat diajukan pada jam operasional 07.00–20.00. |
| 4 | `DATE(start_time) = DATE(end_time)` | F1, A2 | **Struktural** — hanya ada satu field tanggal | Reservasi tidak boleh melewati pergantian hari. |
| 5 | `end_time > start_time` | F1, A2 | Rule kustom `EndSlotAfterStart` | Jam selesai harus lebih besar dari jam mulai. |
| 6 | Maksimal 26 slot | A2 | **Struktural** — konsekuensi aturan 3 dan 4 | *(tidak dapat terjadi)* |
| 7 | `CURDATE()+1 ≤ DATE(start_time) ≤ CURDATE()+30` | A3 | `after_or_equal` + `before_or_equal` | Reservasi paling cepat untuk besok. / Reservasi paling jauh 30 hari ke depan. |
| 8 | Fasilitas harus berstatus `active` | D5 | `Rule::exists` berkondisi | Fasilitas ini sedang tidak menerima reservasi. |

### 17.3 Kodenya

```php
public function rules(): array
{
    return [
        'facility_id' => [
            'required',
            Rule::exists('facilities', 'id')->where('status', 'active'),
        ],
        'date' => [
            'required',
            'date_format:Y-m-d',
            'after_or_equal:' . now()->addDay()->toDateString(),
            'before_or_equal:' . now()->addDays(30)->toDateString(),
        ],
        'start_slot' => ['required', Rule::in(Slot::startTimes())],
        'end_slot'   => ['required', Rule::in(Slot::endTimes()), new EndSlotAfterStart($this->start_slot)],
        'purpose'    => ['required', 'string', 'min:5', 'max:255'],
    ];
}

public function messages(): array
{
    return [
        'facility_id.exists'   => 'Fasilitas ini sedang tidak menerima reservasi.',
        'date.after_or_equal'  => 'Reservasi paling cepat untuk besok.',
        'date.before_or_equal' => 'Reservasi paling jauh 30 hari ke depan.',
        'start_slot.in'        => 'Jam mulai harus kelipatan 30 menit dalam jam operasional 07.00–20.00.',
        'end_slot.in'          => 'Jam selesai harus kelipatan 30 menit dalam jam operasional 07.00–20.00.',
        'purpose.min'          => 'Tujuan penggunaan minimal 5 karakter.',
    ];
}
```

`Slot` adalah class konstanta yang mendefinisikan 26 slot — penerapan F1 "slot tetap ada sebagai konsep, bukan sebagai tabel". Dipakai tiga tempat: mengisi dropdown di U1, menggambar grid di P2, dan memvalidasi di sini. **Satu sumber untuk tiga pemakai**, sehingga tidak mungkin isi dropdown berbeda dari isi validasi.

### 17.4 Empat hal yang perlu dijelaskan saat tanya jawab

**`Rule::exists(...)->where('status','active')` bukan sekadar memeriksa ID ada.** Ia sekaligus menegakkan D5 di sisi server, bukan hanya mematikan tombol di tampilan. Satu POST yang dikirim manual ke fasilitas `under_maintenance` tetap ditolak.

**Perbandingan tanggal memakai `now()`, dan `now()` mengembalikan WIB** berkat `APP_TIMEZONE` yang dijemput `config/app.php` (lihat bagian 19). Kalau masih UTC, `after_or_equal` salah sehari untuk setiap pengajuan yang dibuat setelah pukul 17.00.

**Aturan 4 dan 6 tidak punya baris kode, dan itu bukan kelalaian.** Kalau ditanya "mana validasi yang mencegah reservasi melintasi hari?", jawabannya: bentuk form tidak menyediakan cara untuk melakukannya. Aturan yang dijamin oleh struktur lebih kuat daripada aturan yang dijamin oleh pengecekan.

**U1 tidak memeriksa bentrok jadwal, dan itu disengaja.** US 9 menetapkan bentrok diperiksa saat **approve**, bukan saat submit — dua reservasi `pending` yang bertumpuk memang sah tersimpan. Menambahkan cek bentrok di sini melanggar US 9 sekaligus membuat F2 kehilangan alasan keberadaannya.

## 18. U3 — Batalkan reservasi (modal)

| Field | Aturan server | Client |
|---|---|---|
| `status_reason` | `nullable\|string\|max:500` | `maxlength="500"` |

Opsional bagi pengguna (A7).

**Dua hal yang wajib diperiksa controller, di luar validasi:**

1. Reservasi milik pengguna itu sendiri → Laravel Policy
2. Status `pending`, atau `approved` dengan `CURDATE() < DATE(start_time)` → A1

Poin 2 harus ada di server. Dokumen pendamping menyebut tombolnya diganti teks *"Sudah lewat batas pembatalan mandiri, hubungi petugas"* kalau sudah lewat — itu tampilannya, bukan penjaganya.

## 19. U4 — Lapor kerusakan (`StoreReportRequest`)

| Field | Tipe | Aturan server | Client |
|---|---|---|---|
| `facility_id` | select / hidden | `required\|exists:facilities,id` | `required` |
| `category` | select | `required\|in:` 6 slug B1 | `required` |
| `description` | textarea | `required\|string\|min:10\|max:1000` | `required minlength="10"` |
| `photos` | file (multiple) | `required\|array\|min:1\|max:3` | `required multiple` |
| `photos.*` | — | `image\|mimes:jpg,jpeg,png\|max:3072` | `accept=".jpg,.jpeg,.png"` |

**`exists` di sini tanpa kondisi status**, berbeda dengan U1. Penerapan B4: fasilitas yang sedang dalam perbaikan justru yang paling mungkin dilaporkan lagi.

**`photos` dan `photos.*` adalah dua aturan berbeda.** Yang pertama memvalidasi arraynya (jumlah 1–3), yang kedua memvalidasi tiap file di dalamnya. Aturan `min:1|max:3` inilah penegak batas yang tidak dapat dijaga database — lihat bagian 7.10 dokumen pendamping.

**`max:3072` bersatuan kilobyte**, bukan byte: 3072 KB = 3 MB, sesuai B2. Inilah alasan `php.ini` harus diatur — kalau `upload_max_filesize` masih 2M, PHP membuang file sebelum Laravel melihatnya, dan pesan errornya menyesatkan.

**`mimes:` memeriksa MIME type file sebenarnya, bukan ekstensi nama file.** File bernama `virus.exe.jpg` tetap ditolak. Ini bergantung pada extension `fileinfo`, yang onboarding sudah tandai sebagai paling sering tidak aktif.

## 20. O3 — Tolak dan batalkan reservasi

| Field | Aturan server | Client |
|---|---|---|
| `status_reason` | `required\|string\|min:10\|max:500` | `required minlength="10"` |

Berlaku untuk `officer.reservations.reject` dan `officer.reservations.cancel` (A7, B5). Approve tidak memakai form ini — ia menjalankan transaksi F2.

Yang wajib diperiksa controller: reservasi masih `pending` (untuk reject) atau `approved` (untuk cancel), **dan `start_time` belum lewat** untuk approve dan reject (A8).

## 21. O5 — Ubah status laporan

| Field | Aturan server | Client |
|---|---|---|
| `status` | `required\|in:baru,diproses,selesai,ditolak` | `required` |
| `resolution_note` | `required_if:status,selesai,ditolak\|nullable\|string\|min:10\|max:1000` | JS: wajib saat dropdown bernilai selesai/ditolak |

`required_if` inilah penerapan B3 — kewajiban yang berubah mengikuti status yang dipilih. Ini satu dari tiga aturan yang dokumen pendamping bagian 7.10 minta disebut eksplisit di dokumen Word.

Validasi client-nya butuh JavaScript kecil yang memasang dan melepas atribut `required` saat dropdown berubah. Kalau JavaScript dilewati, server tetap menangkap.

## 22. O6 dan A1 — Ubah status fasilitas

| Route | Field | Aturan server |
|---|---|---|
| `officer.facilities.status` | `status` | `required\|in:active,under_maintenance` |
| `admin.facilities.status` | `status` | `required\|in:active,inactive` |

**Daftar `in:`-nya sengaja berbeda** — inilah matriks wewenang D3 yang ditegakkan di server. Petugas yang mengirim `status=inactive` ditolak validasi, bukan sekadar tidak melihat tombolnya.

Peringatan D4 muncul sebelum form ini disubmit dan sifatnya informatif. Tidak ada pembatalan otomatis.

## 23. A2 — Form fasilitas (`StoreFacilityRequest` / `UpdateFacilityRequest`)

| Field | Tipe | Aturan server | Client |
|---|---|---|---|
| `name` | text | `required\|string\|max:100` | `required maxlength="100"` |
| `type` | select | `required\|in:` 5 tipe D1 | `required` |
| `location` | select | `required\|in:` 6 lokasi D2 | `required` |
| `capacity` | number | `nullable\|integer\|min:1\|max:65535` | `type="number" min="1"` |
| `description` | textarea | `nullable\|string\|max:1000` | `maxlength="1000"` |
| `status` | select | `required\|in:active,under_maintenance,inactive` | `required` |

**`in:` pada `type` dan `location` adalah satu-satunya penjaga**, karena kolomnya VARCHAR (F6) dan database menerima apa saja. Daftarnya diambil dari konstanta PHP yang sama dengan yang mengisi dropdown — pola identik dengan `Slot` pada U1.

`max:65535` mengikuti batas `SMALLINT UNSIGNED`. Tanpa aturan ini, memasukkan 99999 menghasilkan error database, bukan pesan validasi yang enak dibaca.

Dua class terpisah untuk store dan update meski aturannya saat ini sama, supaya kalau nanti berbeda tidak perlu membongkar.

## 24. A4 — Tambah akun (`StoreUserRequest`)

| Field | Aturan server | Client |
|---|---|---|
| `name` | `required\|string\|max:100` | `required maxlength="100"` |
| `email` | `required\|email\|max:150\|unique:users,email` | `required type="email"` |
| `password` | `required\|string\|min:8\|confirmed` | `required minlength="8"` |
| `role` | `required\|in:pengguna,petugas` | `required` |
| `identity_number` | `required_if:role,pengguna\|nullable\|string\|max:30\|unique:users,identity_number` | JS: muncul dan wajib saat role = pengguna |
| `user_type` | `required_if:role,pengguna\|nullable\|in:mahasiswa,dosen,staf` | JS: sama |

**`in:pengguna,petugas` — `admin` tidak ada di daftar.** US 13 dan 14 hanya menyebut petugas dan pengguna. Akun admin dibuat lewat seeder.

Dua `required_if` di sini adalah aturan ketiga dari daftar bagian 7.10 dokumen pendamping. Dokumen itu menegaskan: field bersyarat wajib divalidasi di server, **bukan sekadar disembunyikan JavaScript**.

`status` tidak ada di form — controller mengisinya `verified` (C2).

## 25. A5 — Reset password

| Field | Aturan server | Client |
|---|---|---|
| `password` | `required\|string\|min:8\|confirmed` | `required minlength="8"` |

Satu-satunya elemen di seluruh sistem yang tidak punya dasar user story. Konsekuensi C5, dan H2 meminta keberadaannya **disebut eksplisit di dokumen Word** supaya tidak terlihat sebagai fitur yang muncul tanpa alasan.

## 26. A6 — Filter rekap

| Field | Aturan server | Client |
|---|---|---|
| `start_date` | `required\|date_format:Y-m-d` | `required type="date"` |
| `end_date` | `required\|date_format:Y-m-d\|after_or_equal:start_date` | `required type="date"` |

Filter yang sama dipakai `admin.recap.export` lewat query string identik. Itu yang menjamin export memakai rentang yang sama persis (E1).

---

# BAGIAN IV — KEPUTUSAN BARU

## 27. Batas panjang teks yang belum ditetapkan

Empat angka berikut belum ada di dokumen induk maupun pendamping. **Tidak satu pun membutuhkan perubahan skema** — kolomnya sudah bertipe `TEXT` atau `VARCHAR` yang cukup longgar; ini murni angka validasi di sisi aplikasi.

| Field | Batas | Alasan |
|---|---|---|
| `reports.description` | min 10, max 1000 | Deskripsi "rusak" saja tidak cukup bagi petugas untuk menindaklanjuti |
| `reports.resolution_note` | min 10, max 1000 | Disamakan dengan `status_reason` yang A7 tetapkan minimal 10 |
| `reservations.status_reason` | max 500 | A7 sudah menetapkan batas bawah 10; batas atasnya belum ada |
| `facilities.description` | max 1000 | Kolomnya TEXT, tapi textarea tanpa batas mengundang paste sembarangan |

## 28. Tidak ada kolom yang kurang di skema

Seluruh form di Bagian III dapat dilayani oleh kelima tabel yang sudah dibuat. Tidak ada `ALTER TABLE` yang dibutuhkan, dan aturan "jangan edit migration yang sudah di-push" tidak tersentuh.

## 29. Koreksi terhadap dokumen induk dan onboarding

**`APP_TIMEZONE` di `.env` saja tidak cukup.** Dokumen induk bagian 1 dan onboarding bagian 4 sama-sama menyebut `APP_TIMEZONE=Asia/Jakarta` seolah itu langkah terakhir. Ternyata `config/app.php` juga harus menjemput nilainya:

```php
'timezone' => env('APP_TIMEZONE', 'UTC'),
```

Tanpa baris itu, `config('app.timezone')` tetap `UTC` meski `.env` sudah benar, dan seluruh perbandingan waktu — A1, A3, A8, E5 — bergeser 7 jam. Gejalanya tidak berupa error.

Sudah diperbaiki dan ikut git, sehingga anggota tim otomatis mendapatkannya. **Wajib disebut di bagian "informasi setting" pada dokumen Word.**

**`'engine' => 'InnoDB'` di `config/database.php`.** Ditetapkan pada connection `mysql` dan `mariadb`. F2 memakai `lockForUpdate()` yang tidak berfungsi di MyISAM — dan tidak mengeluarkan error apa pun saat gagal.

---

## 30. Langkah berikutnya

Diperbarui di v1.1 mengikuti keadaan sekarang. Urutan lengkapnya ada di bagian 3 `pembagian-modul-dan-urutan-kerja.md`; di sini hanya yang menyangkut route.

**Sudah selesai:** layout induk dan navbar lengkap, middleware role beserta alias `role` dan kerangka ketiga grup di `routes/web.php`, serta P3 Login dan logout.

**Yang berikutnya:**

1. Pemegang modul **Fasilitas** mendaftarkan `facilities.index`, walau controller-nya masih seadanya. Link brand navbar memanggilnya tanpa syarat, sehingga **setiap halaman yang extends layout gagal dirender** sampai route itu ada — bukan cuma link-nya yang mati. Sekalian mengganti baris `/` bawaan Laravel dengan `Route::redirect('/', '/facilities')` sesuai catatan bagian 8
2. Pemegang modul **Reservasi** mendaftarkan `officer.dashboard`, dengan alasan yang sama: itu landing petugas
3. Pemegang modul **Auth & Akun** mendaftarkan `admin.users.index`, landing admin
4. Setiap orang mengisi blok route modulnya **di dalam grup yang sudah ada** di `routes/web.php`, tanpa membuat grup baru dan tanpa menyentuh blok orang lain
5. Pemegang modul **Reservasi** menulis class konstanta `Slot` dan komponen peringatan D4
6. `DatabaseSeeder` dilengkapi dengan fasilitas, reservasi, dan laporan — bagian akunnya sudah ada
