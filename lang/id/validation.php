<?php

/*
|--------------------------------------------------------------------------
| Pesan Validasi — Bahasa Indonesia
|--------------------------------------------------------------------------
|
| Penerapan bagian 1 `route-dan-kontrak-form.md`: nama route, controller, view,
| dan variabel berbahasa Inggris; Bahasa Indonesia untuk teks yang dilihat
| pengguna, termasuk pesan error.
|
| Berkas ini sengaja TIDAK memuat seluruh aturan bawaan Laravel, hanya yang
| dipakai proyek ini — yaitu aturan pada kedua Form Request yang sudah ada
| (`LoginRequest`, `RegisterRequest`) ditambah aturan yang sudah ditetapkan
| kontrak form bagian 17 sampai 26 untuk Form Request yang menyusul
| (`StoreReservationRequest`, `StoreReportRequest`, `StoreFacilityRequest`,
| `UpdateFacilityRequest`, `StoreUserRequest`).
|
| Aturan yang belum diterjemahkan akan jatuh ke `fallback_locale` = `en` dan
| muncul dalam Bahasa Inggris. Kalau itu terjadi, tambahkan barisnya di sini —
| jangan menambahkan `messages()` per Form Request, supaya satu pesan hanya
| punya satu tempat.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Aturan Validasi
    |--------------------------------------------------------------------------
    */

    'after_or_equal' => 'Kolom :attribute harus berisi tanggal setelah atau sama dengan :date.',
    'array' => 'Kolom :attribute harus berupa daftar.',
    'before_or_equal' => 'Kolom :attribute harus berisi tanggal sebelum atau sama dengan :date.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'date' => 'Kolom :attribute harus berisi tanggal yang sah.',
    'date_format' => 'Kolom :attribute harus sesuai format :format.',
    'email' => 'Kolom :attribute harus berisi alamat email yang sah.',
    'exists' => 'Pilihan :attribute tidak sah.',
    'image' => 'Kolom :attribute harus berupa gambar.',
    'in' => 'Pilihan :attribute tidak sah.',
    'integer' => 'Kolom :attribute harus berupa bilangan bulat.',
    'mimes' => 'Kolom :attribute harus berupa berkas bertipe: :values.',
    'required' => 'Kolom :attribute wajib diisi.',
    'required_if' => 'Kolom :attribute wajib diisi saat :other bernilai :value.',
    'string' => 'Kolom :attribute harus berupa teks.',
    'unique' => ':attribute sudah terpakai.',
    'uploaded' => ':attribute gagal diunggah. Pastikan ukuran berkas tidak lebih dari 3 MB.',

    /*
    | `max` dan `min` punya empat bentuk, dipilih Laravel berdasarkan jenis
    | nilainya. Keempatnya dipakai proyek ini: string (nama, deskripsi), numeric
    | (kapasitas fasilitas), file (ukuran foto laporan dalam kilobita, B2), dan
    | array (jumlah foto 1–3, B2).
    */

    'max' => [
        'array' => 'Kolom :attribute tidak boleh berisi lebih dari :max item.',
        'file' => 'Ukuran :attribute tidak boleh lebih dari :max kilobita.',
        'numeric' => 'Kolom :attribute tidak boleh lebih dari :max.',
        'string' => 'Kolom :attribute tidak boleh lebih dari :max karakter.',
    ],

    'min' => [
        'array' => 'Kolom :attribute harus berisi setidaknya :min item.',
        'file' => 'Ukuran :attribute harus setidaknya :min kilobita.',
        'numeric' => 'Kolom :attribute harus bernilai setidaknya :min.',
        'string' => 'Kolom :attribute harus berisi setidaknya :min karakter.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pesan Khusus per Field
    |--------------------------------------------------------------------------
    |
    | Tempat untuk pesan yang hanya berlaku pada satu field, dengan pola
    | 'nama_field.nama_aturan'. Dibiarkan kosong: sejauh ini pesan umum di atas
    | sudah terbaca wajar untuk seluruh form.
    |
    | Kontrak bagian 17 menetapkan `StoreReservationRequest` punya `messages()`
    | sendiri untuk `date.after_or_equal` dan `date.before_or_equal`. Pesan di
    | Form Request menang atas berkas ini, jadi keduanya tidak bertabrakan.
    |
    */

    'custom' => [],

    /*
    |--------------------------------------------------------------------------
    | Nama Field
    |--------------------------------------------------------------------------
    |
    | Menggantikan :attribute pada pesan di atas. Isinya seluruh kolom kelima
    | tabel (skema bagian 6 `halaman-navigasi-dan-skema.md`), ditambah field
    | yang hanya ada di form dan tidak punya kolom sendiri.
    |
    | Kuncinya nama field, bukan nama tabel, sehingga satu nama berlaku lintas
    | tabel. `name`, `status`, dan `description` memang dipakai lebih dari satu
    | tabel, dan terjemahannya sengaja dibuat netral supaya tetap benar di
    | semuanya.
    |
    */

    'attributes' => [

        // users (6.1)
        'id' => 'ID',
        'name' => 'Nama',
        'email' => 'Email',
        'password' => 'Password',
        'role' => 'Role',
        'status' => 'Status',
        'identity_number' => 'NIM/NIP',
        'user_type' => 'Tipe Pengguna',
        'remember_token' => 'Token Ingat Saya',
        'created_at' => 'Tanggal Dibuat',
        'updated_at' => 'Tanggal Diperbarui',

        // facilities (6.2)
        'type' => 'Tipe Fasilitas',
        'location' => 'Lokasi',
        'capacity' => 'Kapasitas',
        'description' => 'Deskripsi',

        // reservations (6.3)
        'user_id' => 'Pengguna',
        'facility_id' => 'Fasilitas',
        'start_time' => 'Waktu Mulai',
        'end_time' => 'Waktu Selesai',
        'purpose' => 'Tujuan Penggunaan',
        'status_reason' => 'Alasan',

        // reports (6.4)
        'category' => 'Kategori',
        'resolution_note' => 'Catatan Resolusi',

        // report_photos (6.5)
        'report_id' => 'Laporan',
        'file_name' => 'Nama Berkas',

        // Field yang hanya ada di form, tanpa kolom sendiri
        'password_confirmation' => 'Konfirmasi Password',
        'date' => 'Tanggal Penggunaan',
        'start_slot' => 'Jam Mulai',
        'end_slot' => 'Jam Selesai',
        'photos' => 'Foto',
        'photos.*' => 'Foto',
        'start_date' => 'Tanggal Mulai',
        'end_date' => 'Tanggal Selesai',
    ],

    /*
    |--------------------------------------------------------------------------
    | Nilai Field
    |--------------------------------------------------------------------------
    |
    | Menggantikan :value pada pesan `required_if`. A4 memakai
    | `required_if:role,pengguna`, sehingga pesannya berbunyi "... saat Role
    | bernilai Pengguna", bukan "... bernilai pengguna".
    |
    */

    'values' => [
        'role' => [
            'pengguna' => 'Pengguna',
            'petugas' => 'Petugas',
            'admin' => 'Admin',
        ],
        'status' => [
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
        ],
    ],

];
