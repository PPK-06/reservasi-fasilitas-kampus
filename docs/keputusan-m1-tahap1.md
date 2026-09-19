# Keputusan M1 — Middleware Role & Alur Login
## Sistem Reservasi & Pelaporan Fasilitas Kampus

**Mata kuliah:** Pengembangan Platform Khusus (PPK)
**Versi dokumen:** 1.0 — 18 September 2026
**Ditetapkan oleh:** Elang (pemegang M1)

**Dokumen induk:** `dasar-proyek.md` — aturan bisnis yang mengikat
**Dokumen pendamping:**
- `pembagian-modul-dan-urutan-kerja.md` bagian 2 — yang menugaskan penetapan ini
- `route-dan-kontrak-form.md` bagian 15 — kontrak form Login
- `halaman-navigasi-dan-skema.md` bagian 2.1 — peta navigasi jalur publik

---

## Kenapa dokumen ini ada

Bagian 2 `pembagian-modul-dan-urutan-kerja.md` menetapkan bahwa middleware role juga memeriksa status akun, lalu menyerahkan perilakunya: *"Elang menetapkan perilakunya dan menuliskannya di grup."*

Dokumen ini adalah penetapan itu. Delapan keputusan, masing-masing dengan alasannya, supaya yang membaca kodenya nanti tidak perlu menebak kenapa bentuknya begitu.

**Sifatnya mengikat, sama seperti dokumen tim lain.** Kalau ada yang terasa keliru, bahas di grup dan naikkan versinya — jangan diam-diam menulis perilaku berbeda di modul sendiri.

---

## Ringkasan

| # | Keputusan |
|---|---|
| **D1** | Middleware meloloskan hanya `status === 'verified'` |
| **D2** | Status tidak lolos → logout penuh + redirect ke P3 + pesan spesifik C2 |
| **D3** | Role tidak cocok → `abort(403)` |
| **D4** | Urutan pemeriksaan: status dulu, baru role |
| **D5** | Peta pesan C2 dan peta landing H1 tinggal di model `User`, satu sumber |
| **D6** | Tujuan setelah login: `intended()` dengan fallback ke landing H1 |
| **D7** | Kerangka grup route per role dibuat di awal, kosong |
| **D8** | Pesan kegagalan login dan penolakan status dikirim lewat error bag kunci `email` |

---

## D1 — Status yang boleh lanjut

**Keputusan:** middleware meloloskan request hanya kalau `status === 'verified'`. Allow-list, bukan deny-list.

**Alasan.** Hari ini hasilnya identik dengan menolak ketiga status lain, karena C2 hanya punya empat status. Bedanya ada di arah kegagalannya: allow-list gagal ke arah menolak, deny-list gagal ke arah mengizinkan. Untuk kode yang tugasnya menjaga akses, arah kegagalan itu yang menentukan. Kalau suatu saat ada status baru, allow-list menolaknya sampai seseorang memutuskan sebaliknya.

---

## D2 — Kalau status tidak lolos

**Keputusan:** logout, session di-invalidate, token di-regenerate, lalu redirect ke P3 membawa pesan spesifik sesuai status — tabel pesannya ada di bagian 15 `route-dan-kontrak-form.md`.

**Alasan.** Tiga pilihan dipertimbangkan:

| Pilihan | Kenapa tidak |
|---|---|
| Tetap login, `abort(403)` | Orangnya terjebak: navbar masih menampilkan menu role-nya, tapi setiap link 403, tanpa penjelasan |
| Logout tanpa pesan | Terlihat seperti session kedaluwarsa biasa, dan ia akan mencoba login berulang kali |

Pilihan yang diambil satu-satunya yang konsisten dengan alasan C2 ditulis: status akun bukan masalah kredensial, dan menyamarkannya membuat orang mencoba password berulang. Aneh kalau halaman login menjelaskan sementara middleware diam.

Jalur ini bukan kasus teoretis — A5 punya aksi suspend yang bisa mengenai akun yang sedang login.

### Batasan yang diketahui

Pemeriksaan status hanya berjalan di route yang memasang middleware `role`. P1 dan P2 adalah halaman publik yang dipakai bersama pengunjung, jadi tidak memasangnya.

**Akibatnya:** akun yang di-suspend masih bisa membuka P1 dan P2 dengan navbar versi pengguna, sampai ia menyentuh halaman ber-role. Ia tidak bisa melakukan aksi apa pun di sana — semua route aksi ada di balik middleware.

Menutupnya berarti memindahkan pemeriksaan status ke middleware terpisah yang berjalan di semua route `auth`. Bagian 2 `pembagian-modul-dan-urutan-kerja.md` sudah menetapkan pemeriksaan status berada **di dalam** middleware role, jadi batasan ini diterima, bukan diperbaiki.

---

## D3 — Kalau role tidak cocok

**Keputusan:** `abort(403)`.

**Alasan.** Bukan alasan keamanan — redirect pun sama amannya, karena request-nya tetap tidak sampai ke controller. Alasannya umpan balik saat pengembangan: empat orang akan saling mencoba modul selama beberapa minggu ke depan, dan 403 memberi tahu "middleware-nya jalan, role-mu yang salah". Redirect ke halaman sendiri terasa lebih halus tapi menyembunyikan middleware yang salah pasang, dan itu justru kesalahan yang paling mahal kalau baru ketahuan di minggu terakhir.

`abort(404)` juga dipertimbangkan dan ditolak: menyembunyikan keberadaan halaman berlebihan untuk sistem internal kampus yang seluruh daftar halamannya ada di dokumen tim.

---

## D4 — Urutan pemeriksaan

**Keputusan:** status akun diperiksa lebih dulu, baru role.

**Alasan.** Bedanya hanya terasa di satu kasus: akun yang di-suspend membuka halaman yang role-nya juga tidak cocok. Dengan urutan ini ia dapat "kamu sudah dikeluarkan", bukan 403 yang menunjuk ke masalah yang salah. Session yang sudah mati adalah sebab; role yang tidak cocok cuma gejala yang kebetulan ikut terlihat.

**Catatan teknis:** guard session Laravel mengambil ulang user dari database di setiap request, jadi status yang dibaca middleware selalu nilai terbaru. Tidak perlu query manual.

---

## D5 — Di mana peta pesan dan peta landing disimpan

**Keputusan:** keduanya jadi konstanta dan method di model `User`, sebagai satu-satunya sumber.

**Alasan.** Keduanya dipakai di lebih dari satu tempat:

| Peta | Dipakai di |
|---|---|
| Status → pesan penolakan (C2) | `AuthController@store` dan middleware role |
| Role → route landing (H1) | `AuthController@store` dan `redirectUsersTo` di `bootstrap/app.php` |

Dua salinan akan berbeda dalam dua minggu, dan bedanya baru ketahuan saat seseorang membandingkan pesan di dua jalur berbeda.

Menaruhnya di model dipilih karena keduanya turunan langsung dari kolom di tabel `users` — `role` menentukan landing, `status` menentukan pesan. Class terpisah di `app/Support/` juga sah dan punya preseden (class `Slot` milik M3); itu pilihan yang diambil **kalau** `User` mulai sesak, bukan pola ketiga.

---

## D6 — Tujuan setelah login berhasil

**Keputusan:** `redirect()->intended()` dengan fallback ke landing H1 sesuai role.

**Alasan.** Dua dokumen menulisnya dengan dua cara. Kolom Tujuan di bagian 8 `route-dan-kontrak-form.md` menulis "P1 | O1 | A3 sesuai role"; peta navigasi 2.1 `halaman-navigasi-dan-skema.md` menulis `P3 (?redirect=...)`. Keduanya benar dan terpenuhi sekaligus oleh mekanisme `intended` bawaan Laravel — tanpa kode tambahan, karena middleware `auth` sudah menyimpan URL tujuan ke session.

Query param buatan sendiri ditolak karena tujuannya harus divalidasi manual; tanpa itu, `?redirect=` bisa diarahkan ke situs lain.

**Efek samping yang diterima:** kalau seseorang punya tujuan tersimpan lalu login dengan akun role lain, ia mendarat di 403. Jarang terjadi, dan pesannya sudah benar.

Peta navigasi 2.1 sudah diperbarui di v1.1 supaya notasinya tidak lagi terbaca sebagai query param.

---

## D7 — Kerangka grup route

**Keputusan:** kerangka ketiga grup route per role dibuat sekaligus dengan middleware, isinya kosong, dengan komentar penanda pemilik tiap blok.

**Alasan.** Bagian 1 `route-dan-kontrak-form.md` menetapkan pemisahan URI dibuat supaya middleware-nya cukup satu `Route::prefix` per role. Tanpa kerangka, tiga orang akan membuat grup sendiri-sendiri dan `/officer` berakhir dengan tiga pembungkus duplikat — O1 sampai O3 milik M3, O4 dan O5 milik M4, O6 milik M2.

Membuat kerangka sebelum bloknya ada bukan "merapikan blok orang lain" yang dilarang bagian 4 `pembagian-modul-dan-urutan-kerja.md`. Justru inilah yang membuat aturan blok itu bisa ditegakkan.

**Konsekuensi untuk semua orang:** isi blok masing-masing **di dalam** grup yang sudah ada. Jangan membuat grup baru.

---

## D8 — Kunci error bag

**Keputusan:** pesan kredensial salah, pesan penolakan status di `AuthController@store`, dan pesan penolakan status dari middleware role semuanya dikirim lewat error bag kunci `email`.

**Alasan.** P3 hanya punya dua field dan kontrak bagian 15 tidak menyediakan tempat untuk pesan level-form. Satu kunci berarti satu lokasi tampilan yang melayani ketiga sumber. Dua kunci berbeda berarti dua blok tampilan di Blade, dan keduanya harus dijaga supaya tidak pernah muncul bersamaan.

Ini juga pola bawaan Laravel, jadi tidak perlu dijelaskan panjang saat tanya jawab.

**Catatan tampilan:** layout induk sudah menampilkan seluruh error lewat banner `$errors->any()` di atas halaman. Karena itu view P3 tidak mencetak ulang teks pesannya, hanya menandai field dengan `is-invalid`. Satu pesan, satu lokasi, field tetap ditandai.

---

## Di mana keputusan ini hidup di kode

| Keputusan | File |
|---|---|
| D1, D2, D3, D4 | `app/Http/Middleware/EnsureUserHasRole.php` |
| D5 | `app/Models/User.php` — konstanta dan method peta |
| D6 | `app/Http/Controllers/AuthController.php` — method `store` |
| D7 | `routes/web.php` — kerangka tiga grup |
| D8 | `AuthController@store`, `EnsureUserHasRole`, dan view P3 |

Pendaftaran alias `role` dan `redirectUsersTo` ada di `bootstrap/app.php`, yang sejak v1.3 tercatat sebagai file milik Elang di bagian 4 `pembagian-modul-dan-urutan-kerja.md`.

---

## Yang dibuktikan sebelum keputusan ini ditutup

Enam pengujian dijalankan manual di lingkungan lokal, memakai akun demo dari `DatabaseSeeder` dan route stub sementara yang tidak di-commit:

| Uji | Membuktikan |
|---|---|
| Tiga role login, masing-masing mendarat di landing berbeda | H1 dan D6 |
| Tiga status gagal, masing-masing pesan sendiri | C2, D2, dan tabel bagian 15 |
| Password salah pada akun verified → pesan generik | Keberadaan akun tidak bocor |
| Pengguna membuka URL petugas → 403 | D3 |
| Petugas yang sudah login membuka P3 → mendarat di landing petugas | `redirectUsersTo` |
| Akun di-suspend saat sedang login, lalu membuka halaman ber-role | D2, termasuk batasannya |

Uji terakhir awalnya dijalankan di P1 dan tidak menghasilkan apa-apa. Itu yang memunculkan batasan yang dicatat di bawah D2.
