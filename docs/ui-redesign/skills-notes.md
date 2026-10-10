# Catatan Ringkas Sumber Skill UI Redesign

## 1. Anthropic: frontend-design
- Desain berakar pada konteks subjek nyata (institusi Undip Tembalang), bukan template korporat netral.
- Tipografi memiliki karakter kuat (hindari Inter/Roboto default tanpa pertimbangan), tentukan skala tipe jelas.
- Hindari pola AI generik: aksen 1 kata pada headline, uppercase tracked-out berlebih, kartu seragam membosankan.
- Struktur visual berfungsi menyampaikan hierarki informasi, bukan sekadar dekorasi visual tanpa fungsi.
- Gerakan dan animasi digunakan secara hemat, bertujuan membantu orientasi pengguna, bukan gimmick entrance berlebihan.
- Copywriting berorientasi pada sudut pandang peminjam fasilitas, lugas, bersahabat, aktif, dan tanpa filler marketing.

## 2. Leonxlnx: taste-skill
- Wajibkan satu baris "Design Read" sebelum kode untuk menetapkan target audiens, ragam visual, dan dial setting.
- Tetapkan dial sistematis: ENERGY (keseimbangan), MOTION (restrained/smooth), VISUAL DENSITY (lapang dan terstruktur).
- Hindari default LLM: gradien ungu-biru generik, dark mesh acak, 3 kartu identik, efek kaca blur di seluruh elemen.
- Protokol redesign: audit mendalam aset dan kode eksisting sebelum modifikasi; pertahankan logika dan brand identity.
- Larangan keras em dash (—) pada seluruh copywriting antarmuka pengguna; gunakan koma, titik, atau tanda kurung.
- Terapkan pre-flight check ketat dan pengujian responsif pada beragam resolusi layar nyata sebelum pengiriman.

## 3. Miqdad Badjuber: anti-slop
- Terapkan 38 aturan anti-slop dengan 3 kelompok utama: Hard Gate, Purpose-Gate, dan Quality Locks.
- Hard Gate mutlak: bebas em dash (R-02), responsif tanpa overflow (R-03), bebas data palsu (R-17), bebas ulasan fiktif (R-18).
- Setiap elemen interaktif wajib berfungsi nyata atau dihilangkan (R-26); sediakan status empty, loading, error (R-27).
- Kontras warna wajib lolos WCAG AA (R-25) dan seluruh kontrol dapat diakses penuh via keyboard (R-32).
- Aturan Purpose-Gate: setiap gradien, bayangan, border-radius, dan kartu wajib memiliki alasan tertulis 1 baris (R-31).
- Eksekusi Delivery Gate lengkap dengan bukti verifikasi nyata sebelum menyatakan pekerjaan selesai.

## 4. Affaan-M: ECC (Engineering & Agent Harness)
- Disiplin rekayasa perangkat lunak berbasis TDD dan verifikasi berbasis bukti nyata (eksekusi build dan test).
- Manajemen konteks terisolasi dan modular untuk mencegah halusinasi serta degradasi akurasi agen.
- Optimasi siklus iterasi: kerjakan bertahap (tokens, komponen dasar, landing page, modul internal), uji per tahapan.
- Isolasi sub-tugas independen untuk evaluasi objektif dan penelusuran regresi secara sistematis.
- Penegakan kebersihan git dan struktur commit yang bersih, deskriptif, dan dapat ditelusuri.

## 5. Alibaba: open-code-review
- Audit deterministik pada diff perubahan branch terhadap main untuk mendeteksi potensi cacat sebelum rilis.
- Keamanan: periksa proteksi XSS, CSRF token pada form Blade, escaping string aman, dan penanganan input pengguna.
- Kinerja: cegah N+1 query pada pemuatan relasi Eloquent fasilitas, gambar, atau riwayat reservasi.
- Kualitas Blade & CSS: cegah duplikasi komponen, hapus kelas CSS mati, dan minimalkan dependensi eksternal tak berfaedah.
- Aksesibilitas dan semantik HTML: pastikan form berlabel jelas, atribut alt gambar deskriptif, dan fokus navigasi teratur.

## 6. Browser-Use: browser-use & open-source skill
- Pemanfaatan otomasi browser riil untuk pengumpulan data visual terverifikasi dan inspeksi DOM autentik.
- Pengambilan gambar fasilitas nyata kampus Undip Tembalang langsung dari sumber sah berlisensi jelas (Wikimedia/resmi).
- Verifikasi visual lintas viewport responsif (375px mobile, 768px tablet, 1280px desktop) dengan tangkapan layar asli.
- Audit interaksi UI di browser nyata: klik navigasi, buka tutup form/modal, validasi form, dan pengecekan console error.
