# Arah Desain & Design System — Reservasi Fasilitas Undip Tembalang

## 1. Design Read (Deklarasi Awal)
> **Reading this as**: Portal publik dan aplikasi operasional reservasi fasilitas kampus untuk civitas akademika Universitas Diponegoro (Tembalang), bergaya visual *Civic Modernity* yang bersih, kredibel, dan berwibawa khas kampus negeri terkemuka, dial **ENERGY 1 / RHYTHM 2 / MOTION 1**.

## 2. Identitas Visual & Filosofi
- **Karakter**: Institusi perguruan tinggi negeri yang modern, transparan, rapi, dan terpercaya. Tidak kaku seperti aplikasi birokrasi lawas, namun tidak menggunakan gimmick teknologi berlebihan (tanpa font monospaced terminal palsu, tanpa gradien ungu-neon acak, tanpa glassmorphism berlebihan).
- **Nuansa Khas Undip**: Mengadopsi perpaduan *Deep Undip Navy* (lambang kelautan dan kebijaksanaan Diponegoro) berpadu dengan aksen *Diponegoro Warm Gold* (kehormatan dan prestasi).
- **Struktur Berbobot**: Layout berbasis grid teratur, rasio kontras tinggi, navigasi yang intuitif, dan pemisahan tegas antara area publik, area pengguna (mahasiswa/dosen/staf), serta area pengelola (petugas & admin).

## 3. Sistem Tipografi (Typography System)
Menggunakan pasangan font Google Fonts yang profesional dan memiliki bobot visual:
- **Font Display & Judul**: `Plus Jakarta Sans` (sans-serif modern buatan desainer Indonesia dengan proporsi geometris presisi, berkarakter kuat, dan ramah dibaca).
- **Font Body & Teks Data**: `Outfit` atau fallback `system-ui, -apple-system, sans-serif` (keterbacaan tinggi, ritme baris nyaman, dan angka tabel proporsional).

### Skala Tipografi:
- Display 1 (Hero Title): `2.5rem - 3.25rem` (40px - 52px), weight 800, line-height 1.15, letter-spacing -0.02em.
- Heading 1 (Page Title): `1.75rem - 2rem` (28px - 32px), weight 700, line-height 1.25.
- Heading 2 (Section Title): `1.35rem - 1.5rem` (22px - 24px), weight 700, line-height 1.3.
- Heading 3 (Card / Modal Title): `1.1rem - 1.25rem` (18px - 20px), weight 600, line-height 1.35.
- Body Regular: `0.9375rem` (15px), weight 400, line-height 1.6.
- Body Small / Metadata: `0.8125rem` (13px), weight 500, line-height 1.5.
- Micro / Badge: `0.75rem` (12px), weight 600, uppercase terkontrol (tracking +0.03em).

## 4. Design Tokens (CSS Variables)

```css
:root {
  /* Brand Core */
  --undip-navy-dark: #07172C;
  --undip-navy: #0B2545;
  --undip-navy-light: #134074;
  --undip-blue-surface: #EEF4FA;
  
  --undip-gold: #C89B3C;
  --undip-gold-hover: #AF842A;
  --undip-gold-light: #FDF8ED;

  /* Neutrals & Surfaces */
  --surface-canvas: #F8FAFC;
  --surface-card: #FFFFFF;
  --surface-muted: #F1F5F9;
  --border-subtle: #E2E8F0;
  --border-medium: #CBD5E1;
  --text-main: #0F172A;
  --text-muted: #475569;
  --text-subtle: #64748B;

  /* Semantic Statuses (WCAG AA Compliant) */
  --status-approved-text: #065F46;
  --status-approved-bg: #D1FAE5;
  --status-approved-border: #A7F3D0;

  --status-pending-text: #92400E;
  --status-pending-bg: #FEF3C7;
  --status-pending-border: #FDE68A;

  --status-maintenance-text: #B45309;
  --status-maintenance-bg: #FFFBEB;
  --status-maintenance-border: #FCD34D;

  --status-rejected-text: #991B1B;
  --status-rejected-bg: #FEE2E2;
  --status-rejected-border: #FECACA;

  /* Radius */
  --radius-sm: 6px;
  --radius-md: 10px;
  --radius-lg: 16px;
  --radius-xl: 20px;

  /* Shadows */
  --shadow-subtle: 0 1px 3px rgba(11, 37, 69, 0.05);
  --shadow-card: 0 4px 16px -2px rgba(11, 37, 69, 0.06), 0 2px 6px -1px rgba(11, 37, 69, 0.04);
  --shadow-card-hover: 0 12px 28px -4px rgba(11, 37, 69, 0.12), 0 4px 10px -2px rgba(11, 37, 69, 0.06);
  --shadow-modal: 0 24px 48px -12px rgba(11, 37, 69, 0.25);
}
```

## 5. Komponen Standar

### A. Tombol (Buttons)
- **Primary Button**: Warna `var(--undip-navy)`, teks putih, hover `var(--undip-navy-light)`, focus-visible outline `2px solid var(--undip-gold)` dengan offset `2px`.
- **Accent Button (CTA)**: Warna `var(--undip-gold)`, teks navy gelap `#07172C`, font weight 600.
- **Secondary Button**: Outlined border dengan warna navy transparan, hover background warna `var(--undip-blue-surface)`.
- **Destructive Button**: Crimson `#DC2626` dengan teks putih atau soft red background.
- Ukuran minimum touch-target: 44px tinggi pada tombol utama untuk kenyamanan sentuh seluler.

### B. Formulir & Input (Forms)
- Input field: Background putih, border `1px solid var(--border-medium)`, radius `8px`, padding `10px 14px`.
- Focus state: Border menjadi `var(--undip-navy)` dengan shadow ring halus `0 0 0 3px rgba(11, 37, 69, 0.12)`.
- Label: Font size `0.85rem`, font-weight 600, warna `var(--text-main)`, indikator wajib `*` berwarna merah.
- Feedback error: Teks jelas di bawah input (`.invalid-feedback`) dengan ikon peringatan, bukan sekadar warna merah samar.

### C. Kartu Fasilitas (Cards)
- Bingkai putih bersih beradius 12px, border halus `1px solid var(--border-subtle)`.
- Gambar rasio 16:9 atau 4:3 dengan object-fit cover, badge tipe di sudut foto.
- Informasi hierarkis: Judul fasilitas (bold), lokasi dengan ikon pin, kapasitas daya tampung, status operasional.
- Hover effect: Elevasi bertahap naik 2px dengan shadow menyebar halus.

### D. Tabel Data & Antrian
- Header tabel: Latar `var(--undip-blue-surface)`, teks navy semi-bold, uppercase ringan (`font-size: 0.78rem`).
- Baris tabel: Padding vertikal lapang (`14px`), border bawah tipis, hover highlight sangat halus.
- Kontrol pagination yang jelas dengan informasi total data yang ramah.

### E. Status Badges
- Menunggu / Pending: Latar amber lembut, teks amber gelap, ikon jam pasir.
- Disetujui / Selesai / Aktif: Latar hijau mint lembut, teks hijau tua, ikon centang.
- Ditolak / Batal / Nonaktif: Latar merah lembut, teks crimson, ikon silang.
- Dalam Perbaikan: Latar oranye lembut, teks oranye pekat, ikon obeng/peralatan.

### F. Modal & Dialog
- Backdrop gelap bersih (`rgba(7, 23, 44, 0.5)`).
- Header modal memiliki judul tegas dengan tombol close yang mudah diakses keyboard (Escape key aktif).
- Footer tombol aksi utama di kanan, tombol batal di kiri.

## 6. Checklist Anti-Slop & Kualitas Penulisan
- **Bebas em dash (`—`)**: Gunakan koma, titik dua, atau kurung pada seluruh teks UI yang ditulis.
- **Bebas Buzzword AI**: Tidak ada frasa klise seperti "Revolutionize your campus experience", "Seamless booking system", atau "Cutting-edge facility platform".
- **Bahasa Indonesia Alami**: Seluruh copywriting ditulis dalam Bahasa Indonesia yang lugas, baku, dan lazim di lingkungan akademik Undip (contoh: "Ajukan Permohonan", "Pilih Jadwal", "Katalog Sarana Kampus Tembalang", "Ketentuan Peminjaman").
- **Tanpa Data Palsu**: Statistik yang ditampilkan hanya berasal dari database nyata (jumlah fasilitas yang terdaftar, kuota kapasitas asli).
- **Semua Elemen Interaktif Berfungsi**: Tidak ada tombol tanpa aksi nyata, tidak ada menu yang mengarah ke `#` kosong.
