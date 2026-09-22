# Keputusan M1 — Filter dan Bentuk Halaman A3
## Sistem Reservasi & Pelaporan Fasilitas Kampus

**Mata kuliah:** Pengembangan Platform Khusus (PPK)
**Versi dokumen:** 1.0 — 23 September 2026
**Ditetapkan oleh:** Elang (pemegang M1)

**Dokumen induk:** `dasar-proyek.md` — aturan bisnis yang mengikat
**Dokumen pendamping:**
- `halaman-navigasi-dan-skema.md` bagian 1.4 dan 2.4 — spesifikasi A3 dan jalur admin
- `route-dan-kontrak-form.md` bagian 11 dan Bagian III — daftar route admin dan kontrak form
- `pembagian-modul-dan-urutan-kerja.md` bagian 3 dan 5 — urutan kerja tahap 2 dan isi seeder
- `keputusan-m1-tahap1.md` — D1 sampai D8, penomorannya dilanjutkan di sini

---

## Kenapa dokumen ini ada

Bagian 1.4 `halaman-navigasi-dan-skema.md` menetapkan A3 sebagai *"seluruh akun dengan filter role dan status, serta penanda jumlah akun `pending`"*, lalu berhenti di situ. Bentuk filternya tidak ditetapkan di dokumen mana pun.

Kekosongan itu tidak simetris dengan halaman berfilter yang lain. A6 punya kontraknya di bagian 26 `route-dan-kontrak-form.md`, lengkap dengan nama field dan aturan validasinya. A3 tidak punya padanannya. Tanpa penetapan, bentuk filter A3 akan ditentukan oleh apa yang kebetulan pertama kali ditulis di controller.

Delapan keputusan berikut mengisi kekosongan itu. Tiga di antaranya (D9, D13, D15) menutup jalur yang secara teknis jalan tapi keliru, dan alasannya ditulis supaya tidak ada yang "memperbaikinya" balik.

**Penomoran melanjutkan `keputusan-m1-tahap1.md`** supaya seluruh keputusan M1 jadi satu deret, bukan dua deret yang harus dibedakan lewat nama file. D1 sampai D8 ada di dokumen itu.

**Sifatnya mengikat, sama seperti dokumen tim lain.** Kalau ada yang terasa keliru, bahas di grup dan naikkan versinya.

---

## Ringkasan

| # | Keputusan |
|---|---|
| **D9** | Filter A3 lewat query string pada satu route `admin.users.index`, bukan route per tab |
| **D10** | Nama parameter mengikuti nama kolom: `?status=` dan `?role=` |
| **D11** | Status ditampilkan sebagai tab, role sebagai select — keduanya menulis ke query string yang sama |
| **D12** | Tampilan default A3 tanpa filter sama sekali |
| **D13** | Angka penanda `pending` diambil dari query terpisah, bukan dari daftar yang sedang ditampilkan |
| **D14** | Nilai filter yang tidak dikenal diabaikan, bukan ditolak. Tanpa Form Request |
| **D15** | Filter tidak dibawa melewati A5. `verify` dan `reject` kembali ke A3 tanpa query string |
| **D16** | Paginasi 15 baris per halaman, dengan `withQueryString()` |

---

## D9 — Bentuk transport filter

**Keputusan:** state filter dibawa lewat query string pada satu route `admin.users.index`. Tidak ada route tambahan per nilai filter, dan tidak ada filter berbasis POST.

**Alasan.** Tiga hal di dokumen sudah menutup alternatifnya sebelum selera ikut bicara:

| Yang menutup | Isinya |
|---|---|
| Bagian II `route-dan-kontrak-form.md` | A3 punya tepat satu route. Tab berbentuk route terpisah (`/admin/users/pending`) berarti menambah route di luar daftar 45 |
| F11 | `Paginator::useBootstrapFive()` sudah aktif, artinya daftar akun berpaginasi. Filter yang tidak ada di URL akan hilang begitu admin menekan halaman 2 |
| E1 dan bagian 26 | A6 sudah menetapkan filter lewat query string justru supaya `index` dan `export` berbagi filter yang sama persis. Filter POST di A3 akan jadi pola kedua untuk masalah yang sama |

Preseden bentuknya sudah ada di catatan bagian 9 `route-dan-kontrak-form.md`: `reservations.create` menerima `?facility=3` dan diselesaikan sebagai *"satu kondisi di Blade, bukan dua halaman"*. A3 memakai pola yang sama.

**Yang perlu disadari:** "query string atau tab" bukan dua pilihan yang setara. Query string adalah cara state dikirim ke server, tab adalah cara state ditampilkan. Tab di proyek tanpa build pipeline tetap berbentuk `<a href="?status=pending">`, jadi ia berjalan **di atas** query string, bukan menggantikannya. Itu juga yang dimaksud bagian 1.3 saat menjelaskan tab O2: *"halamannya sama, hanya query dan tombolnya berbeda"*.

Konsekuensinya: D11 di bawah murni soal Blade dan tidak memengaruhi bentuk controller sama sekali.

---

## D10 — Nama parameter

**Keputusan:** `?status=` dan `?role=`, sama persis dengan nama kolom di tabel `users`.

**Alasan.** Bukan soal keseragaman kosmetik. E1 sudah membuktikan bahwa query string yang identik antar route adalah satu-satunya hal yang menjamin dua route membaca data yang sama — itu yang mengikat `admin.recap.export` ke `admin.recap.index`. Kalau suatu saat A3 butuh export atau butuh dibagikan sebagai tautan, parameter yang sudah senama kolom membuat itu tidak perlu penerjemahan.

Bahasa Inggris mengikuti aturan induk bagian 1 `route-dan-kontrak-form.md`: nama route, view, dan variabel berbahasa Inggris; Bahasa Indonesia hanya untuk teks yang dilihat pengguna. Label tab tetap "Menunggu", "Terverifikasi", "Ditolak", "Dinonaktifkan"; yang ada di URL tetap nilai enum.

---

## D11 — Bentuk tampilan filter

**Keputusan:** status ditampilkan sebagai deretan tab, role sebagai `<select>` dengan submit `method="GET"`. Keduanya menulis ke query string yang sama.

**Alasan.** Bagian 1.4 menetapkan A3 punya **dua** filter. Tab mengasumsikan satu dimensi yang saling eksklusif, jadi dua dimensi tab berarti dua baris tab bertumpuk — dan dua baris tab sejajar terbaca seperti dua tingkat, padahal bukan.

Pembagiannya mengikuti cara halaman ini dipakai. Admin membuka A3 untuk mencari akun yang menunggu verifikasi (US 15); itu dimensi status, dan itu yang pantas dapat tempat paling terlihat. Role hampir tidak pernah jadi cara admin mencari akun, jadi ia cukup jadi penyaring sekunder.

Bentuk ini juga seragam dengan tab O2 milik modul Reservasi, sehingga ada satu pola tab di seluruh sistem, bukan dua.

**Ini keputusan yang paling murah dibatalkan.** Karena D9, mengubah tab jadi select atau sebaliknya tidak menyentuh controller, route, maupun model.

---

## D12 — Tampilan default

**Keputusan:** A3 dibuka tanpa filter apa pun. Tab aktif adalah "Semua".

**Alasan.** H1 menetapkan landing adalah halaman keadaan, yaitu halaman yang menampilkan keadaan sistem. Membuka A3 langsung terfilter ke `pending` terasa menolong, tapi menyembunyikan keadaan: admin tidak tahu ada berapa akun seluruhnya, dan tab aktif yang tidak ia pilih sendiri mudah tidak disadari — ia akan menyimpulkan sistem hanya punya lima akun.

Penanda jumlah `pending` (D13) sudah mengarahkan perhatian ke pekerjaan yang menunggu tanpa perlu menyembunyikan sisanya. Penanda itu sekaligus jadi tautan ke `?status=pending`, jadi jalur pintasnya tetap satu klik.

---

## D13 — Sumber angka penanda `pending`

**Keputusan:** angkanya diambil dari query terpisah atas seluruh tabel, bukan dari koleksi yang sedang ditampilkan.

```php
$pendingCount = User::where('status', 'pending')->count();
```

**Alasan.** Menghitungnya dari daftar yang sudah difilter akan membuat penanda menunjukkan 0 setiap kali admin memfilter status lain — dan tepat pada saat itulah penanda dibutuhkan. Fungsinya memberi tahu ada pekerjaan yang menunggu **terlepas dari** apa yang sedang dilihat.

Kesalahan ini tidak menghasilkan error dan tidak terlihat saat halaman dibuka polos, karena pada tampilan default angkanya kebetulan benar. Itu sebabnya ditulis sebagai keputusan, bukan diserahkan ke pembaca kode.

Jumlah akun `pending` juga tidak terpengaruh paginasi, karena `count()` berjalan di database, bukan atas halaman yang sedang diambil.

---

## D14 — Perlakuan nilai filter yang tidak dikenal

**Keputusan:** nilai di luar daftar yang sah diabaikan dan halaman dirender tanpa filter itu. Tidak ada Form Request, tidak ada 422, tidak ada pesan error.

```php
$status = in_array($request->query('status'), ['pending','verified','rejected','suspended'], true)
    ? $request->query('status')
    : null;
```

**Alasan.** Pola `{Store|Update}{Entitas}Request` di bagian 6 `route-dan-kontrak-form.md` diperuntukkan bagi form yang **menulis**. Filter ini tidak menulis apa pun, dan menolak query string yang diutak-atik akan melempar admin ke halaman error tanpa tempat menampilkan pesannya — banner `$errors->any()` di layout mengasumsikan ada halaman form untuk kembali, dan A3 bukan halaman form.

Arah kegagalannya juga sejalan dengan D1. Nilai yang tidak dikenal jatuh ke "tidak memfilter", bukan ke "memfilter dengan nilai sembarang" yang menghasilkan daftar kosong tanpa penjelasan.

Whitelist ditulis eksplisit di controller, bukan diambil dari kolom enum, supaya penambahan status baru di database tidak diam-diam menjadi filter yang bisa dipanggil.

---

## D15 — Filter saat kembali dari A5

**Keputusan:** `admin.users.verify` dan `admin.users.reject` kembali ke `admin.users.index` tanpa query string. Filter yang sedang aktif sebelum admin masuk ke A5 tidak dipulihkan.

**Alasan.** Ini keputusan yang paling dekat ke salah, jadi alasannya ditulis lengkap.

Membawa filter melewati A5 berarti empat tempat harus kompak: A3 menempelkan query string pada setiap tautan baris, A5 menerimanya, A5 menyimpannya sebagai hidden input pada form `verify` dan `reject`, lalu controller meneruskannya ke `redirect()->route()`. Kalau satu di antaranya lupa, filternya hilang tanpa error dan tanpa gejala selain "kok balik ke semua lagi".

Biaya itu tidak sebanding untuk tenggat yang ada, sementara alur verifikasi yang wajar juga bisa dilakukan dari tombol di baris A3 tanpa masuk ke A5 sama sekali.

**`back()` bukan alternatif.** `verify` dan `reject` dipanggil dari A5, jadi `back()` akan mendarat di A5 — bertentangan dengan kolom Tujuan bagian 11 yang menulis A3. Ini berbeda dengan `officer.facilities.status` yang kolom Tujuannya memang ditulis "kembali ke halaman asal".

**Kalau nanti terasa mengganggu**, penambahannya bersifat aditif dan tidak membongkar apa pun yang ditulis sekarang.

---

## D16 — Paginasi

**Keputusan:** 15 baris per halaman, dan setiap pemanggilan `paginate()` di A3 diakhiri `->withQueryString()`.

**Alasan.** `withQueryString()` bukan pilihan gaya — tanpanya, tautan halaman 2 dirender tanpa `?status=` dan filternya hilang di klik pertama. Ini gejala yang mudah disalahartikan sebagai filternya yang rusak, padahal filternya benar dan tautannya yang tidak lengkap.

Angka 15 dipilih supaya tujuh akun demo dari seeder muat di satu halaman, sehingga paginasi tidak menyulitkan pengujian manual, sekaligus tetap memaksa paginasi terlihat begitu seeder dilengkapi.

---

## Di mana keputusan ini hidup di kode

| Keputusan | File |
|---|---|
| D9, D10, D13, D14, D16 | `app/Http/Controllers/Admin/UserController.php` — method `index` |
| D11, D12 | `resources/views/admin/users/index.blade.php` |
| D15 | `Admin\UserController@verify` dan `@reject` |

Tidak ada satu pun keputusan di atas yang menyentuh model, migration, atau `#[Fillable]`. Filter hanya membaca.

---

## Konsekuensi ke dokumen lain

**`route-dan-kontrak-form.md` — satu baris catatan, bukan bagian baru.**

Filter A3 sengaja **tidak** dimasukkan ke Bagian III. Bagian itu berisi kontrak form yang menulis, dilayani Form Request, dan punya pesan error; D14 menetapkan A3 tidak punya ketiganya. Memasukkannya ke sana juga memaksa renumbering bagian 27 sampai 30 di dokumen yang dipakai empat orang, demi entri yang isinya cuma menunjuk balik ke sini.

Yang diusulkan adalah satu baris di catatan bagian 11, naik ke v1.2:

> - `admin.users.index` menerima query string opsional `?status=` dan `?role=`. Ini filter baca, bukan kontrak form — tidak ada Form Request dan tidak ada penolakan validasi. Bentuk dan alasannya ada di `keputusan-m1-tahap2.md` D9–D16

**`pembagian-modul-dan-urutan-kerja.md` — tidak perlu diubah.**

Bagian 3 tidak menetapkan urutan internal pekerjaan Elang di tahap 2, jadi pemindahan `DatabaseSeeder` di bawah ini adalah penerapan dokumen itu, bukan penyimpangan darinya.

---

## Urutan kerja M1 di tahap 2, setelah penyesuaian

Delapan potongan, satu potongan satu commit:

| # | Isi |
|---|---|
| 1 | Blok route M1 lengkap (11 route) + `Admin\UserController@index` seadanya |
| 2 | `DatabaseSeeder` lengkap |
| 3 | A3 — filter, default, penanda, paginasi (D9–D16) |
| 4 | P4 Registrasi |
| 5 | A5 `show` + `verify` + `reject` |
| 6 | A5 `activate` + `reset-password` |
| 7 | A4 + `StoreUserRequest` + tombol "Tambah Akun" di A3 |
| 8 | A5 `suspend` — menunggu komponen peringatan D4 |

**Kesebelas route didaftarkan sekaligus di potongan pertama**, dengan method yang isinya masih `abort(501)`. `routes/web.php` adalah satu-satunya file M1 yang juga disentuh tiga orang lain (bagian 4), jadi menyentuhnya sekali berarti satu kesempatan konflik, bukan lima. Ini juga yang disarankan bagian 3 poin 6: daftarkan route lebih awal walau controllernya masih kosong.

**Satu-satunya urutan yang mengikat di antara potongan 1–5 adalah 2 sebelum 3**, karena A3 tanpa data tidak bisa dilihat benar. Sisanya berdiri sendiri.

**`DatabaseSeeder` naik ke posisi kedua**, dari yang sebelumnya direncanakan terakhir. Bagian 3 `pembagian-modul-dan-urutan-kerja.md` menulisnya eksplisit: *"jangan ditunda sampai akhir"*, karena Dhimas dan Fazl tidak bisa menguji modulnya di atas database kosong. Menundanya sampai akhir berarti bug yang seharusnya ketahuan minggu ini baru muncul di PR terakhir.

Seeder juga prasyarat pengujian A3 dan A5 milik M1 sendiri: tanpa akun yang mencakup keempat status C2, tab di D11 tidak bisa dibuktikan dan tombol aksi A5 tidak bisa dilihat berubah.

Kelima model sudah selesai di tahap 0, dan **isi seeder tidak menunggu siapa pun.** Nilai tipe dan lokasi fasilitas sudah lengkap di D1 dan D2, statusnya di D3, dan konvensi path foto laporan di F5. Ditulis sebagai string biasa di seeder, bukan memanggil konstanta milik modul lain yang belum tentu sudah ada.

Satu hal yang perlu disiasati: audit F10 untuk `Reservation`, `Report`, dan `ReportPhoto` masih terutang sejak tahap 1, dan `#[Fillable]` yang keliru akan melempar `MassAssignmentException` di seeder — pada model yang bukan milik M1. Karena itu seeder mengisi ketiga model itu lewat penetapan properti eksplisit (`new Report()` lalu `$r->kolom = ...`), bukan mass assignment. Utang auditnya tetap ditagih lewat grup, tapi seeder tidak menunggunya.

---

## Yang belum diputuskan dan sengaja dibiarkan terbuka

| Hal | Kenapa dibiarkan |
|---|---|
| Kolom apa saja yang ditampilkan di tabel A3 | Tidak ada dokumen yang mengikat, dan ini tidak memengaruhi orang lain. Diputuskan saat menulis Blade-nya |
| Urutan baris (`ORDER BY`) | Sama. Kandidat awal: `pending` di atas, lalu `created_at` menurun |
| Pencarian teks bebas (nama, email, NIM) | Tidak diminta bagian 1.4. Kalau ditambahkan, ia parameter ketiga dengan perlakuan D14 yang sama, dan dokumen ini dinaikkan versinya |
| Aksi `suspend` di A5 | **Terblokir, bukan ditunda.** Bagian 7 `route-dan-kontrak-form.md` menugaskan komponen peringatan D4 ke modul Reservasi dan mendaftarkan A5 sebagai pemakainya, dan C6 mewajibkan daftar reservasi terdampak ditampilkan saat suspend ditekan. Menunggu komponen Dhimas |

**Hanya `suspend` yang terblokir.** `activate` cuma transisi status biasa sesuai matriks C2, dan `reset-password` cuma modal satu field sesuai bagian 25 — keduanya tidak memanggil peringatan apa pun dan diselesaikan M1 sendiri. Versi awal dokumen ini sempat menulis ketiganya terblokir; itu keliru dan diperbaiki di sini.

Dhimas tetap perlu diberi tahu sekarang, karena bagian 3 menempatkan komponen peringatan D4 di tahap 3 dan ia perlu tahu ada pemakai keempat sebelum menentukan bentuknya.
