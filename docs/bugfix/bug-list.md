# Daftar Bug Wiyata

| ID | Kategori | Deskripsi | Status | File Terkait | Bukti Reproduksi / Catatan |
|---|---|---|---|---|---|
| B-001 | Global | Bagian notifikasi di bawah navbar tidak akan pernah terutup secara otomatis dan membutuhkan close manual | Solved | `resources/views/layouts/app.blade.php` | Menambahkan script auto-close untuk alert di `.flash-wrapper` dengan timeout 5 detik via `bootstrap.Alert.getOrCreateInstance(alertEl).close()`. |
| B-002 | Petugas | Font dalam dashboard bagian antrian reservasi tidak selaras dengan font laporan belum selesai, gunakan bagian laporan belum selesai sebagai refrensi pembenaran | Solved | `resources/views/officer/dashboard.blade.php` | Menghapus kelas `font-monospace` pada waktu antrian reservasi dan menyelaraskannya menjadi `<small class="text-muted">` sesuai referensi panel laporan. |
| B-003 | Petugas | Font dalam halaman antrian terutama pada bagian waktu pemakaian tidak selaras dengan font waktu lain nya (gunakan bagian laporan belum selesai sebagai refrensi pembenaran) | Solved | `resources/views/officer/reservations/index.blade.php` | Menghapus kelas `font-monospace` pada kolom Waktu Pemakaian di tabel antrian reservasi sehingga menggunakan font standar selaras dengan kolom tanggal dan tabel laporan. |
| B-004 | Petugas | Dalam bagian Detail Reservasi itu ada redundansi status tiket reservasi | Solved | `resources/views/officer/reservations/show.blade.php` | Menghapus baris status redundan di dalam definition list (`<dl>`) kartu detail reservasi karena status tiket sudah ditampilkan dengan rapi pada header kartu via `.badge-status`. |
| B-005 | Petugas | UI dalam halaman Ketersediaan tidak selaras dengan UI bagian halaman lainnya | Solved | `resources/views/officer/facilities/index.blade.php` | Menyelaraskan komponen tombol filter (`btn-undip-primary`), border card-header (`border-bottom`), warna thead (`var(--undip-navy)`), tipografi nama fasilitas (`font-heading`), dan status badge menggunakan kelas standar `.badge-status`. |
| B-006 | Petugas | Font dalam halaman detil antrial (secara spesifik bagian waktu) kurang konsisten | Pending | TBD | TBD |
| B-007 | Petugas | Ada teks (US) dalam halaman warning tandai perbaikan fasilitas | Pending | TBD | TBD |
| B-008 | User | Dalam bagian detil fasilitas adanya redundansi kata aktif, bagian box informasi juga redundan | Pending | TBD | TBD |
| B-009 | User | Dalam halaman setelah berhasil mengajukan reservasi terdapat redundansi dua "menunggu" dan tulisan waktu disitu belum terstandarisasi | Pending | TBD | TBD |
| B-010 | User | Dalam halaman detil fasilitas itu ada tombol refresh yang kurang masuk akal pemetaanya dan lebih masuk akal kami taruh tombol nya di sebelah kiri ajukan reservasi (yang di bawah) | Pending | TBD | TBD |
| B-011 | User | Dalam halaman reservasi ada tombol batal dan itu redundan soalnya ngga kemana2 dan ngga guna, hapus aja | Pending | TBD | TBD |
| B-012 | User | Dalam halaman riwayat pada detail permohonan reservasi itu ada "Tidak sempat diproses" dan saat masuk itu ada tombol untuk batal padahal redundan | Pending | TBD | TBD |
| B-013 | User | Dalam halaman riwayat pada detail permohonan reservasi itu ada redundansi status tiket permohonan, mohon hapus salah satu | Pending | TBD | TBD |
| B-014 | User | Dalam halaman lapor kerusakan ada tombol batal dan itu redundan | Pending | TBD | TBD |
| B-015 | Admin | Dalam halaman Master Akun itu bagian pemilihan role di filter nabrak dengan tanda dropdown | Pending | TBD | TBD |
| B-016 | Admin | Saat masuk ke akun Admin halaman yang dibuka pertama itu master akun (ini minor tapi aku ngga suka cug), karena letaknya ada di tengah normalnya kalo habis login akan masuk ke halaman paling pojok kiri | Pending | TBD | TBD |
| B-017 | Admin | Dalam halaman Rekap Data saat handler untuk tanggal mulai tidak boleh lebih dari tanggal akhir itu membuat banyak hal rusak: (1) 3 error message: di bawah navbar, di atas box rentang tanggal; (2) error di bawah box tanggal selesai membuat box naik proporsional | Pending | TBD | TBD |
