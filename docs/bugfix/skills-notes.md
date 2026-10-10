# Catatan Skill untuk Perbaikan Bug Wiyata

## 1. Open Code Review (alibaba/open-code-review)
- Fokus pada deteksi defect nyata (presisi tinggi) dibanding noise: cek bug fungsional, keamanan, dan maintainability.
- Review baris kode spesifik pada git diff tanpa melebar ke area yang tidak tersentuh.
- Validasi batasan engineering: pastikan perubahan mematuhi aturan sintaks dan arsitektur tanpa regresi.
- Cegah celah keamanan baru pada input/output handling dan otorisasi.

## 2. ECC Engineering System (affaan-m/ECC)
- Ikuti siklus kerja terstruktur: plan -> test -> implement -> review -> verify.
- Terapkan TDD dan verification loop: buat tes yang memicu kegagalan sebelum perbaikan jika memungkinkan.
- Jaga konteks tetap bersih dan terfokus pada lingkup tugas saat ini.
- Gunakan verifikasi mandiri berbasis fakta eksekusi sebelum menyatakan pekerjaan selesai.

## 3. Browser Use (browser-use/browser-use)
- Manfaatkan otomasi browser untuk mereproduksi alur interaksi pengguna secara nyata (klik, input, navigasi).
- Verifikasi hasil perbaikan antarmuka dan alur form langsung pada browser lokal (respons form, modal, notifikasi).
- Catat bukti pengujian visual dan langkah reproduksi konkret.
- Pastikan interaksi end-to-end berjalan mulus tanpa error konsol atau elemen blocking.

## 4. Frontend Design (anthropics/frontend-design)
- Jaga konsistensi hierarki visual, tipografi, dan spasi dengan desain eksisting.
- Hindari perlakuan teks generik/khas template seperti aksen warna acak pada satu kata atau label all-caps berlebih.
- Sesuaikan visual dengan konteks aplikasi (aplikasi kampus Wiyata yang bersih dan fungsional).
- Pertahankan skala tipe yang rapi dan hindari penambahan elemen dekoratif yang tidak perlu.

## 5. Taste Skill (leonxlnx/taste-skill)
- Utamakan detail visual presisi: keselarasan margin, padding, perataan font, dan proporsi antarelemen.
- Cegah tampilan boilerplate atau layout canggung (perataan form, posisi ikon dropdown, tombol aksi).
- Pastikan komponen mikro (tombol, badge status, box info) selaras dengan bahasa desain keseluruhan.
- Perhatikan detail interaksi dan layout pada breakpoint mobile maupun desktop.

## 6. Anti-Slop (miqdadbadjuber/anti-slop)
- Hapus redundansi elemen visual, teks/copy repetitif, dan status ganda yang membingungkan pengguna.
- Gunakan bahasa/copy Indonesia yang natural, lugas, dan manusiawi tanpa frasa klise atau kaku.
- Jangan menambahkan elemen visual generik atau hiasan tanpa fungsi spesifik.
- Jalankan pemeriksaan gate: pastikan setiap elemen UI yang tersisa memiliki tujuan fungsional yang jelas.
