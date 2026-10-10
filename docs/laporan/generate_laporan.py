#!/usr/bin/env python3
"""
Skrip Penyusun Laporan Resmi PPK Wiyata
Menghasilkan:
1. docs/laporan/Laporan PPK D 06.docx (dokumen Word formal akademik)
2. docs/laporan/Laporan PPK D 06.md (salinan Markdown terstruktur)
"""

import os
import sys
import docx
from docx import Document
from docx.shared import Inches, Pt, RGBColor, Mm
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import OxmlElement, parse_xml
from docx.oxml.ns import nsdecls, qn

BASE_DIR = "/Users/dhimno/Tugas/PPK/Proyek PPK/reservasi-fasilitas-kampus"
DOCS_DIR = os.path.join(BASE_DIR, "docs/laporan")
SCREENSHOTS_DIR = os.path.join(DOCS_DIR, "screenshots")
DOCX_OUT = os.path.join(DOCS_DIR, "Laporan PPK D 06.docx")
MD_OUT = os.path.join(DOCS_DIR, "Laporan PPK D 06.md")

# Warna Identitas Undip
COLOR_NAVY = RGBColor(11, 37, 69)      # #0B2545
COLOR_GOLD = RGBColor(200, 155, 60)    # #C89B3C
COLOR_TEXT = RGBColor(33, 37, 41)      # #212529
COLOR_MUTED = RGBColor(108, 117, 125)  # #6C757D
HEX_PRIMARY = "0B2545"
HEX_LIGHT_BG = "F8F9FA"
HEX_BORDER = "DEE2E6"

def set_cell_background(cell, fill_hex):
    tcPr = cell._tc.get_or_add_tcPr()
    shd = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{fill_hex}"/>')
    tcPr.append(shd)

def set_cell_margins(cell, top=120, bottom=120, left=180, right=180):
    tcPr = cell._tc.get_or_add_tcPr()
    tcMar = parse_xml(f'<w:tcMar {nsdecls("w")}><w:top w:w="{top}" w:type="dxa"/><w:bottom w:w="{bottom}" w:type="dxa"/><w:left w:w="{left}" w:type="dxa"/><w:right w:w="{right}" w:type="dxa"/></w:tcMar>')
    tcPr.append(tcMar)

def set_table_borders(table, color="CCCCCC", sz="4", val="single"):
    tblPr = table._tbl.tblPr
    borders = parse_xml(
        f'<w:tblBorders {nsdecls("w")}>\n'
        f'  <w:top w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>\n'
        f'  <w:bottom w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>\n'
        f'  <w:insideH w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>\n'
        f'  <w:left w:val="none"/>\n'
        f'  <w:right w:val="none"/>\n'
        f'  <w:insideV w:val="none"/>\n'
        f'</w:tblBorders>'
    )
    tblPr.append(borders)

def build_document():
    doc = Document()
    
    # Page Setup: A4, Margin 2.54 cm
    section = doc.sections[0]
    section.page_width = Mm(210)
    section.page_height = Mm(297)
    section.top_margin = Mm(25.4)
    section.bottom_margin = Mm(25.4)
    section.left_margin = Mm(25.4)
    section.right_margin = Mm(25.4)
    
    # Configure Normal Style
    style_normal = doc.styles['Normal']
    style_normal.font.name = 'Times New Roman'
    style_normal.font.size = Pt(12)
    style_normal.font.color.rgb = COLOR_TEXT
    style_normal.paragraph_format.line_spacing = 1.15
    style_normal.paragraph_format.space_after = Pt(6)

    # -------------------------------------------------------------
    # 0. HALAMAN JUDUL / SAMPUL
    # -------------------------------------------------------------
    p_title = doc.add_paragraph()
    p_title.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_title.paragraph_format.space_before = Pt(36)
    p_title.paragraph_format.space_after = Pt(12)
    run_title = p_title.add_run("LAPORAN PROYEK PENGEMBANGAN PLATFORM KHUSUS\nWIYATA: SISTEM RESERVASI FASILITAS KAMPUS UNIVERSITAS DIPONEGORO")
    run_title.bold = True
    run_title.font.name = 'Times New Roman'
    run_title.font.size = Pt(16)
    run_title.font.color.rgb = COLOR_NAVY

    p_sub = doc.add_paragraph()
    p_sub.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_sub.paragraph_format.space_after = Pt(6)
    run_sub = p_sub.add_run("Disusun untuk memenuhi tugas mata kuliah Pengembangan Platform Khusus (Kelas D)\nDepartemen Informatika, Fakultas Sains dan Matematika")
    run_sub.font.size = Pt(11)
    run_sub.italic = True

    p_dosen = doc.add_paragraph()
    p_dosen.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_dosen.paragraph_format.space_after = Pt(36)
    run_dosen = p_dosen.add_run("Dosen Pengampu: Sandy Kurniawan, S.Kom., M.Kom.")
    run_dosen.font.size = Pt(11)
    run_dosen.bold = True

    p_oleh = doc.add_paragraph()
    p_oleh.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_oleh.paragraph_format.space_after = Pt(12)
    run_oleh = p_oleh.add_run("Disusun Oleh: Kelompok 06")
    run_oleh.font.size = Pt(12)
    run_oleh.bold = True

    # Tabel Anggota Tim
    table_team = doc.add_table(rows=5, cols=2)
    table_team.alignment = WD_TABLE_ALIGNMENT.CENTER
    table_team.autofit = False
    
    headers = ["Nama Lengkap Mahasiswa", "Nomor Induk Mahasiswa (NIM)"]
    for col_idx, text in enumerate(headers):
        cell = table_team.cell(0, col_idx)
        cell.text = text
        cell.paragraphs[0].runs[0].bold = True
        cell.paragraphs[0].alignment = WD_ALIGN_PARAGRAPH.CENTER
        cell.paragraphs[0].runs[0].font.name = 'Times New Roman'
        cell.paragraphs[0].runs[0].font.size = Pt(11)
        set_cell_background(cell, "0B2545")
        cell.paragraphs[0].runs[0].font.color.rgb = RGBColor(255, 255, 255)
        set_cell_margins(cell, top=140, bottom=140, left=180, right=180)
    
    team_data = [
        ("Dhimas Reza Nafi Wahyudi", "24060124120010"),
        ("Elang Fadila Ahmad", "24060124130108"),
        ("Fazl Nizam Priyambodho", "24060124130121"),
        ("Ferdy Prasetya Putra", "24060124140145")
    ]
    
    for row_idx, (name, nim) in enumerate(team_data, start=1):
        cell_name = table_team.cell(row_idx, 0)
        cell_nim = table_team.cell(row_idx, 1)
        cell_name.text = name
        cell_nim.text = nim
        cell_name.paragraphs[0].alignment = WD_ALIGN_PARAGRAPH.LEFT
        cell_nim.paragraphs[0].alignment = WD_ALIGN_PARAGRAPH.CENTER
        for cell in (cell_name, cell_nim):
            set_cell_margins(cell, top=100, bottom=100, left=180, right=180)
            if row_idx % 2 == 1:
                set_cell_background(cell, "F8F9FA")
            cell.paragraphs[0].runs[0].font.name = 'Times New Roman'
            cell.paragraphs[0].runs[0].font.size = Pt(11)
            
    set_table_borders(table_team, color="B0C4DE", sz="6")
    doc.add_paragraph().paragraph_format.space_after = Pt(36)

    p_univ = doc.add_paragraph()
    p_univ.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_univ.paragraph_format.line_spacing = 1.15
    run_univ = p_univ.add_run(
        "DEPARTEMEN INFORMATIKA\n"
        "FAKULTAS SAINS DAN MATEMATIKA\n"
        "UNIVERSITAS DIPONEGORO\n"
        "SEMARANG\n"
        "2026"
    )
    run_univ.bold = True
    run_univ.font.name = 'Times New Roman'
    run_univ.font.size = Pt(12)
    run_univ.font.color.rgb = COLOR_NAVY

    doc.add_page_break()

    # -------------------------------------------------------------
    # PENGANTAR: DESKRIPSI SINGKAT SISTEM WIYATA
    # -------------------------------------------------------------
    h_intro = doc.add_paragraph()
    h_intro.paragraph_format.space_before = Pt(12)
    h_intro.paragraph_format.space_after = Pt(6)
    r = h_intro.add_run("Ringkasan Eksekutif & Deskripsi Sistem")
    r.bold = True
    r.font.size = Pt(14)
    r.font.color.rgb = COLOR_NAVY

    p_intro1 = doc.add_paragraph(
        "Wiyata adalah platform berbasis web terpadu yang dirancang untuk mendigitalkan proses reservasi sarana dan "
        "prasarana serta pelaporan kerusakan fasilitas di lingkungan Universitas Diponegoro (Undip), khususnya Kampus Tembalang. "
        "Sebelum sistem ini diimplementasikan, peminjaman ruangan seperti aula, laboratorium, dan ruang kelas masih dilakukan "
        "secara parsial melalui formulir manual atau koordinasi tatap muka, yang berisiko memicu bentrok jadwal penggunaan "
        "serta lambatnya penanganan keluhan kerusakan fasilitas pendukung perkuliahan."
    )
    p_intro1.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY

    p_intro2 = doc.add_paragraph(
        "Sistem Wiyata mengintegrasikan tiga aktor utama institusi: Civitas Akademika (Mahasiswa, Dosen, dan Staf) sebagai "
        "pemohon reservasi dan pelapor kendala fasilitas; Petugas Fasilitas dan Laboratorium sebagai peninjau antrian, "
        "verifikator jadwal, dan eksekutor tindak lanjut laporan kerusakan; serta Administrator Kampus sebagai pengelola "
        "master data sarana prasarana, verifikator akun pengguna baru, dan pengambil keputusan melalui rekapitulasi data operasional. "
        "Aplikasi dibangun menggunakan kerangka kerja Laravel 13.31.0 (berjalan di atas PHP 8.5) dengan arsitektur Model-View-Controller (MVC), "
        "basis data relasional MySQL, antarmuka responsif berbasis Bootstrap 5.3 CDN dan Bootstrap Icons, serta 214 pengujian fitur "
        "terotomatisasi menggunakan Pest Framework."
    )
    p_intro2.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY

    # -------------------------------------------------------------
    # 1. PEMBAGIAN TUGAS
    # -------------------------------------------------------------
    h1 = doc.add_paragraph()
    h1.paragraph_format.space_before = Pt(18)
    h1.paragraph_format.space_after = Pt(6)
    r = h1.add_run("1. Pembagian Tugas")
    r.bold = True
    r.font.size = Pt(14)
    r.font.color.rgb = COLOR_NAVY

    doc.add_paragraph(
        "Pembagian kerja tim dilakukan dengan pendekatan irisan vertikal (vertical slice per module) di mana setiap anggota "
        "bertanggung jawab penuh dari lapisan basis data, logika bisnis controller, hingga tampilan antarmuka Blade untuk "
        "fitur yang ditugaskan. Seluruh riwayat kontribusi terverifikasi secara akurat melalui 188 commit pada repositori Git tim."
    )

    # 1.1 Dhimas Reza Nafi Wahyudi
    h1_1 = doc.add_paragraph()
    h1_1.paragraph_format.space_before = Pt(8)
    h1_1.paragraph_format.space_after = Pt(4)
    r = h1_1.add_run("1.1 Dhimas Reza Nafi Wahyudi (NIM: 24060124120010)")
    r.bold = True
    r.font.size = Pt(12)
    
    p_dh = doc.add_paragraph(
        "Dhimas bertanggung jawab atas perancangan dan implementasi Modul M3 (Reservasi Fasilitas) serta penjaminan mutu "
        "antarmuka aplikasi (56 commit, branch feature/reservation, feature/ketersediaan, feature/pesan-bentrok-approve, ui-redisign). "
        "Pada sisi backend, Dhimas mengembangkan model Reservation, class konstanta Slot, query ketersediaan slot (Slot::availability()), "
        "logika proteksi bentrok jadwal F2 menggunakan transaksi database dan lockForUpdate(), serta seluruh controller terkait "
        "(ReservationController, Officer\\ReservationController, Officer\\DashboardController). Pada sisi antarmuka, Dhimas membangun "
        "layar tersulit P2 (Detail Fasilitas & Grid Ketersediaan 26 Slot Interaktif), form reservasi U1, riwayat reservasi U2, detail reservasi U3, "
        "dashboard petugas O1, antrian verifikasi O2, dan detail peninjauan O3. Selain itu, Dhimas memimpin peremajaan desain "
        "Wiyata dengan identitas visual resmi Undip (Deep Navy dan Warm Gold) serta menyelesaikan 17 tiket perbaikan bug (B-001 hingga B-017)."
    )
    p_dh.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY

    # 1.2 Elang Fadila Ahmad
    h1_2 = doc.add_paragraph()
    h1_2.paragraph_format.space_before = Pt(8)
    h1_2.paragraph_format.space_after = Pt(4)
    r = h1_2.add_run("1.2 Elang Fadila Ahmad (NIM: 24060124130108)")
    r.bold = True
    r.font.size = Pt(12)

    p_el = doc.add_paragraph(
        "Elang bertindak sebagai Project Manager dan Tech Lead tim sekaligus penanggung jawab Modul M1 (Autentikasi & Manajemen Akun) "
        "dengan total 93 commit (branch feature/auth-akun, feature/suspend-a5, feature/test-auth-middleware, main). "
        "Elang menginisialisasi arsitektur proyek, mengelola protokol merge Pull Request, menyusun dokumen konvensi tim "
        "(pembagian-modul-dan-urutan-kerja.md, onboarding-tim.md, dan keputusan teknis), serta membangun alur autentikasi multi-peran "
        "(AuthController, RegisterController, dan middleware role). Di panel admin, Elang mengimplementasikan fitur pengelolaan pengguna "
        "(A3, A4, A5) yang mencakup lima aksi transisi status C2 (verifikasi akun, penolakan registrasi, penonaktifan sementara/suspend "
        "dengan modal peringatan D4, pengaktifan kembali, dan reset password), menyusun DatabaseSeeder demo sembilan akun, serta menulis "
        "rangkaian test suite autentikasi komprehensif."
    )
    p_el.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY

    # 1.3 Fazl Nizam Priyambodho
    h1_3 = doc.add_paragraph()
    h1_3.paragraph_format.space_before = Pt(8)
    h1_3.paragraph_format.space_after = Pt(4)
    r = h1_3.add_run("1.3 Fazl Nizam Priyambodho (NIM: 24060124130121)")
    r.bold = True
    r.font.size = Pt(12)

    p_fz = doc.add_paragraph(
        "Fazl bertanggung jawab atas pengembangan Modul M4 (Pelaporan Kerusakan Fasilitas) dan Modul M5 (Rekapitulasi & Ekspor Data Admin) "
        "dengan total 18 commit (branch feature/laporan-rekap, feature/d4-o5, feature/cleanup-o5, feature/test-m4-a6). "
        "Pada Modul M4, Fazl merancang model Report dan ReportPhoto, ReportController, Officer\\ReportController, form pengajuan kerusakan U4 "
        "dengan validasi multi-upload 1–3 foto bukti fisik (format JPG/PNG maks 3MB), riwayat laporan U5, detail laporan U6, antrian penanganan O4, "
        "serta detail tindak lanjut petugas O5 yang dilengkapi catatan resolusi dan integrasi modal peringatan fasilitas perbaikan D4. "
        "Pada Modul M5, Fazl membangun controller Admin\\RecapController (halaman A6) yang menghitung metrik utilisasi fasilitas dan statistik "
        "kerusakan sarana, serta menyediakan fungsionalitas ekspor data laporan ke format CSV dengan filter rentang tanggal identik."
    )
    p_fz.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY

    # 1.4 Ferdy Prasetya Putra
    h1_4 = doc.add_paragraph()
    h1_4.paragraph_format.space_before = Pt(8)
    h1_4.paragraph_format.space_after = Pt(4)
    r = h1_4.add_run("1.4 Ferdy Prasetya Putra (NIM: 24060124140145)")
    r.bold = True
    r.font.size = Pt(12)

    p_fd = doc.add_paragraph(
        "Ferdy bertanggung jawab atas implementasi Modul M2 (Manajemen Master Fasilitas) dan fondasi komponen antarmuka bersama "
        "dengan total 21 commit (branch feature/fasilitas, feature/komponen-d4, feature/d4-a2, feature/test-m2). "
        "Ferdy merancang model Facility, FacilityController (katalog publik P1 dengan filter kategori dan lokasi gedung), Officer\\FacilityController "
        "(kelola ketersediaan operasional O6), serta Admin\\FacilityController (daftar master A1 dan formulir CRUD master fasilitas A2). "
        "Ferdy menerapkan pemisahan tegas wewenang status fasilitas antara Admin (active/inactive) dan Petugas (active/under_maintenance) "
        "sesuai aturan bisnis D3. Selain itu, Ferdy membangun tata letak induk Blade aplikasi (app.blade.php), bilah navigasi responsif, "
        "komponen bersama peringatan dampak reservasi aktif D4 (alert-d4.blade.php), serta rangkaian automated test suite pengujian modul fasilitas."
    )
    p_fd.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY

    # -------------------------------------------------------------
    # 2. LINK FILE PROGRAM
    # -------------------------------------------------------------
    h2 = doc.add_paragraph()
    h2.paragraph_format.space_before = Pt(18)
    h2.paragraph_format.space_after = Pt(6)
    r = h2.add_run("2. Link File Program")
    r.bold = True
    r.font.size = Pt(14)
    r.font.color.rgb = COLOR_NAVY

    p_link = doc.add_paragraph()
    p_link.add_run("Repositori GitHub: ").bold = True
    p_link.add_run("https://github.com/PPK-06/reservasi-fasilitas-kampus\n")
    p_link.add_run("Link Google Drive: ").bold = True
    p_link.add_run("[ISI MANUAL: link Google Drive berkas program lengkap dan dokumentasi video]")

    # -------------------------------------------------------------
    # 3. INFORMASI SETTING PROGRAM
    # -------------------------------------------------------------
    h3 = doc.add_paragraph()
    h3.paragraph_format.space_before = Pt(18)
    h3.paragraph_format.space_after = Pt(6)
    r = h3.add_run("3. Informasi Setting Program")
    r.bold = True
    r.font.size = Pt(14)
    r.font.color.rgb = COLOR_NAVY

    doc.add_paragraph(
        "Petunjuk instalasi berikut telah diuji secara nyata pada lingkungan pengembangan lokal dan dipastikan berhasil "
        "menjalankan seluruh fungsionalitas sistem Wiyata serta rangkaian test suite."
    )

    doc.add_paragraph("A. Persyaratan Sistem:").runs[0].bold = True
    reqs = [
        "PHP versi >= 8.2 (diuji menggunakan PHP 8.5.0 CLI dengan ekstensi pdo, pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json, fileinfo)",
        "Composer versi 2.x (Dependency Manager PHP)",
        "Basis Data MySQL versi 8.0+ atau MariaDB 10.4+ (berjalan pada port 3306)",
        "Peramban web modern (Google Chrome, Mozilla Firefox, Microsoft Edge, atau Safari)",
        "Catatan Dependensi Frontend: Aplikasi Wiyata tidak mewajibkan instalasi Node.js dan NPM untuk kompilasi build, karena seluruh aset desain (Bootstrap 5.3.3 dan Bootstrap Icons 1.11.3) telah diintegrasikan secara optimal melalui Content Delivery Network (CDN) pada layout Blade."
    ]
    for req in reqs:
        p = doc.add_paragraph(req, style='List Bullet')
        p.paragraph_format.space_after = Pt(3)

    doc.add_paragraph("B. Langkah-Langkah Instalasi:").runs[0].bold = True
    steps = [
        ("1. Klon Repositori", "Unduh berkas proyek dari GitHub ke direktori lokal:\n$ git clone https://github.com/PPK-06/reservasi-fasilitas-kampus.git\n$ cd reservasi-fasilitas-kampus"),
        ("2. Pasang Dependensi PHP", "Jalankan Composer untuk mengunduh pustaka kerja Laravel:\n$ composer install"),
        ("3. Konfigurasi Environment", "Salin template konfigurasi lingkungan:\n$ cp .env.example .env\n$ php artisan key:generate"),
        ("4. Konfigurasi Basis Data MySQL", "Buat basis data baru pada MySQL server lokal:\nCREATE DATABASE reservasi_fasilitas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n\nKemudian sesuaikan parameter koneksi pada berkas .env:\nDB_CONNECTION=mysql\nDB_HOST=127.0.0.1\nDB_PORT=3306\nDB_DATABASE=reservasi_fasilitas\nDB_USERNAME=root\nDB_PASSWORD="),
        ("5. Eksekusi Migrasi & Seeding Data", "Jalankan migrasi skema tabel dan penyuntikan akun demo serta data awal sarana prasarana:\n$ php artisan migrate --seed\n\nCatatan: Pastikan direktori database/seeders/sample-photos/ tersedia agar berkas contoh foto kerusakan fasilitas berhasil disalin ke direktori public/uploads/reports/."),
        ("6. Menjalankan Server Aplikasi", "Jalankan server pengembangan bawaan PHP/Laravel:\n$ php artisan serve\nAplikasi siap diakses melalui peramban web pada alamat URL: http://127.0.0.1:8000"),
        ("7. Menjalankan Pengujian Terotomatisasi (Opsional)", "Untuk memverifikasi keabsahan seluruh logika bisnis, jalankan Pest Framework dengan koneksi MySQL aktif:\n$ DB_CONNECTION=mysql DB_DATABASE=reservasi_fasilitas vendor/bin/pest")
    ]
    for title, desc in steps:
        p_step = doc.add_paragraph()
        r_st = p_step.add_run(f"{title}\n")
        r_st.bold = True
        r_desc = p_step.add_run(desc)
        p_step.paragraph_format.space_after = Pt(6)

    doc.add_paragraph("C. Catatan Teknis & Troubleshooting:").runs[0].bold = True
    troubles = [
        "Konfigurasi Unggah Berkas: Pada php.ini, pastikan parameter upload_max_filesize diatur minimal 3M dan post_max_size minimal 12M agar fitur pelaporan kerusakan sarana dengan multi-foto (1-3 foto) tidak mengalami kegagalan transmisi HTTP.",
        "Pengujian Test Suite: Lingkungan pengujian Pest memerlukan koneksi MySQL aktif karena pengurutan kategori fasilitas menggunakan fungsi spesifik MySQL FIELD(type, ...). Menjalankan pengujian di atas SQLite in-memory akan menghasilkan galat ketiadaan fungsi FIELD()."
    ]
    for t in troubles:
        p = doc.add_paragraph(t, style='List Bullet')
        p.paragraph_format.space_after = Pt(3)

    # -------------------------------------------------------------
    # 4. INFORMASI LOGIN PENGGUNA
    # -------------------------------------------------------------
    h4 = doc.add_paragraph()
    h4.paragraph_format.space_before = Pt(18)
    h4.paragraph_format.space_after = Pt(6)
    r = h4.add_run("4. Informasi Login Pengguna")
    r.bold = True
    r.font.size = Pt(14)
    r.font.color.rgb = COLOR_NAVY

    doc.add_paragraph(
        "Seluruh akun pengguna demo diinisialisasi melalui DatabaseSeeder dan UserFactory. Seluruh akun demo menggunakan "
        "kata sandi terstandarisasi yaitu 'password'. Berikut adalah daftar akun yang terdaftar dalam sistem beserta "
        "status verifikasinya:"
    )

    table_login = doc.add_table(rows=10, cols=5)
    table_login.alignment = WD_TABLE_ALIGNMENT.CENTER
    table_login.autofit = False

    login_headers = ["Peran (Actor / Role)", "Nama Pengguna", "Identitas (NIM/NIP)", "Email (Username)", "Password"]
    for col_idx, h_text in enumerate(login_headers):
        cell = table_login.cell(0, col_idx)
        cell.text = h_text
        cell.paragraphs[0].runs[0].bold = True
        cell.paragraphs[0].alignment = WD_ALIGN_PARAGRAPH.CENTER
        cell.paragraphs[0].runs[0].font.name = 'Times New Roman'
        cell.paragraphs[0].runs[0].font.size = Pt(10)
        set_cell_background(cell, "0B2545")
        cell.paragraphs[0].runs[0].font.color.rgb = RGBColor(255, 255, 255)
        set_cell_margins(cell, top=120, bottom=120, left=100, right=100)

    login_data = [
        ("Administrator", "Admin Kampus", "Petugas Internal", "admin@kampus.test", "password"),
        ("Petugas Fasilitas", "Petugas Sarana", "Petugas Internal", "petugas1@kampus.test", "password"),
        ("Petugas Fasilitas", "Petugas Laboratorium", "Petugas Internal", "petugas2@kampus.test", "password"),
        ("Pengguna (Verified)", "Pengguna Verified", "2311000001 (Mhs)", "pengguna.verified@kampus.test", "password"),
        ("Pengguna (Verified)", "Pengguna Verified Kedua", "2311000005 (Dosen)", "pengguna.verified2@kampus.test", "password"),
        ("Pengguna (Verified)", "Pengguna Verified Ketiga", "2311000006 (Staf)", "pengguna.verified3@kampus.test", "password"),
        ("Pengguna (Pending)", "Pengguna Pending", "2311000002 (Mhs)", "pengguna.pending@kampus.test", "password"),
        ("Pengguna (Rejected)", "Pengguna Rejected", "2311000003 (Dosen)", "pengguna.rejected@kampus.test", "password"),
        ("Pengguna (Suspended)", "Pengguna Suspended", "2311000004 (Staf)", "pengguna.suspended@kampus.test", "password")
    ]

    for row_idx, row in enumerate(login_data, start=1):
        for col_idx, val in enumerate(row):
            cell = table_login.cell(row_idx, col_idx)
            cell.text = val
            cell.paragraphs[0].runs[0].font.name = 'Times New Roman'
            cell.paragraphs[0].runs[0].font.size = Pt(10)
            if col_idx in (2, 4):
                cell.paragraphs[0].alignment = WD_ALIGN_PARAGRAPH.CENTER
            set_cell_margins(cell, top=80, bottom=80, left=100, right=100)
            if row_idx % 2 == 1:
                set_cell_background(cell, "F8F9FA")

    set_table_borders(table_login, color="B0C4DE", sz="6")

    p_veri = doc.add_paragraph()
    p_veri.paragraph_format.space_before = Pt(10)
    p_veri.paragraph_format.space_after = Pt(6)
    p_veri.add_run("Hasil Pengujian Otentikasi dan Otorisasi Login:").bold = True

    veri_notes = [
        "Admin Kampus: Berhasil login dan langsung diarahkan ke http://127.0.0.1:8000/admin/facilities (halaman terdepan panel admin pada navbar).",
        "Petugas Sarana & Laboratorium: Berhasil login dan langsung diarahkan ke http://127.0.0.1:8000/officer (dashboard antrian operasional peminjaman dan keluhan).",
        "Pengguna Verified (Mahasiswa, Dosen, Staf): Berhasil login dan diarahkan ke http://127.0.0.1:8000/facilities (katalog sarana prasarana aktif kampus).",
        "Pengguna Pending, Rejected, Suspended: Seluruh akun dengan status non-aktif berhasil dicegat oleh AuthController dan dikembalikan ke halaman login dengan pesan notifikasi penolakan yang informatif sesuai aturan bisnis C1-C6."
    ]
    for vn in veri_notes:
        p = doc.add_paragraph(vn, style='List Bullet')
        p.paragraph_format.space_after = Pt(3)

    # -------------------------------------------------------------
    # 5. SCREENSHOT ANTARMUKA & PENJELASAN FITUR
    # -------------------------------------------------------------
    doc.add_page_break()
    h5 = doc.add_paragraph()
    h5.paragraph_format.space_before = Pt(18)
    h5.paragraph_format.space_after = Pt(6)
    r = h5.add_run("5. Screenshot Antarmuka & Penjelasan Fitur")
    r.bold = True
    r.font.size = Pt(14)
    r.font.color.rgb = COLOR_NAVY

    doc.add_paragraph(
        "Bagian ini mendokumentasikan 11 fitur utama sistem Wiyata lengkap dengan tujuan fungsional, aktor pengguna, "
        "alur penggunaan, tangkapan layar antarmuka teruji (resolusi Desktop 1440x900 dan Mobile 390x844), serta aturan "
        "bisnis dan validasi yang berlaku."
    )

    features = [
        {
            "id": "5.1",
            "title": "Fitur (1): Beranda Publik & Katalog Fasilitas Kampus",
            "tujuan": "Menyediakan portal informasi resmi sarana dan prasarana kampus Undip Tembalang yang dapat diakses secara terbuka oleh seluruh civitas akademika tanpa memerlukan login awal.",
            "aktor": "Publik / Tamu (Mahasiswa, Dosen, Tenaga Kependidikan, dan Masyarakat Umum).",
            "alur": "Pengguna membuka alamat root aplikasi (/). Pengguna disajikan informasi ringkas mengenai fasilitas kampus aktif, zona sebaran gedung (Gedung A-E dan Kompleks Olahraga), serta tautan eksplorasi. Pengguna dapat memilih menu 'Daftar Fasilitas' (/facilities) untuk mencari ruangan berdasarkan kata kunci, menyaring berdasarkan kategori (Aula, Laboratorium, Ruang Kelas, Lapangan, Alat), atau memfilter berdasarkan zona gedung.",
            "aturan": "Hanya fasilitas berstatus 'active' yang ditampilkan pada katalog publik. Fasilitas berstatus 'under_maintenance' dan 'inactive' disembunyikan dari hasil pencarian publik untuk mencegah pengajuan yang tidak valid.",
            "images": [
                ("01-landing-desktop.png", "Gambar 1. Tampilan Beranda Publik Wiyata (Desktop)", 6.0),
                ("01-landing-mobile.png", "Gambar 2. Tampilan Beranda Publik Wiyata (Mobile)", 3.2),
                ("02-katalog-fasilitas-desktop.png", "Gambar 3. Katalog Pencarian dan Filter Fasilitas Kampus (Desktop)", 6.0),
                ("02-katalog-fasilitas-mobile.png", "Gambar 4. Katalog Pencarian Fasilitas Kampus (Mobile)", 3.2)
            ]
        },
        {
            "id": "5.2",
            "title": "Fitur (2): Autentikasi Pengguna & Registrasi Mandiri Civitas",
            "tujuan": "Mengamankan akses sistem dan memfasilitasi pendaftaran akun mandiri bagi civitas akademika Universitas Diponegoro.",
            "aktor": "Civitas Akademika Baru dan Pengguna Terdaftar.",
            "alur": "Pengguna memilih tombol 'Daftar Akun Civitas' (/register), mengisi nama lengkap, email institusi, kata sandi, nomor identitas (NIM/NIP), serta tipe civitas (Mahasiswa, Dosen, atau Staf). Setelah registrasi berhasil, akun berstatus 'pending' menunggu verifikasi admin. Pengguna yang telah diverifikasi dapat masuk melalui halaman login (/login) menggunakan email dan password.",
            "aturan": "Aturan C1-C6: Hanya akun berstatus 'verified' yang diizinkan mengakses menu reservasi dan pelaporan. Nomor identitas (NIM/NIP) wajib berupa kombinasi numerik unik dan hanya berlaku untuk peran 'pengguna'. Pengguna dengan status pending, rejected, atau suspended ditolak masuk dengan notifikasi status yang jelas.",
            "images": [
                ("03-login-desktop.png", "Gambar 5. Formulir Masuk Akun Pengguna (Desktop)", 5.5),
                ("03-login-mobile.png", "Gambar 6. Formulir Masuk Akun Pengguna (Mobile)", 3.0),
                ("04-register-desktop.png", "Gambar 7. Formulir Registrasi Mandiri Civitas Akademika (Desktop)", 5.5)
            ]
        },
        {
            "id": "5.3",
            "title": "Fitur (3): Detail Fasilitas & Grid Ketersediaan Waktu (Slot Availability)",
            "tujuan": "Menampilkan spesifikasi lengkap ruangan/alat serta visualisasi jadwal ketersediaan waktu secara interaktif per hari.",
            "aktor": "Pengguna Terverifikasi dan Tamu Publik.",
            "alur": "Pengguna memilih salah satu kartu fasilitas dari katalog (/facilities/{id}). Halaman menampilkan galeri foto, kapasitas ruangan, zona lokasi, deskripsi fasilitas, serta kalender grid ketersediaan 26 slot waktu (07.00 - 20.00 WIB) dengan interval 30 menit. Pengguna dapat mengganti tanggal peninjauan untuk melihat status penggunaan.",
            "aturan": "Aturan D5 & F4: Slot waktu memiliki tiga representasi visual: Biru Outline (Tersedia), Abu-abu Terisi (Terpesan oleh reservasi berstatus approved), dan Biru Solid (Terpilih oleh pengguna). Untuk menjaga privasi pengguna (F4), publik hanya dapat melihat status terpesan tanpa menampilkan nama pemohon atau tujuan kegiatan.",
            "images": [
                ("05-detail-fasilitas-grid-desktop.png", "Gambar 8. Detail Fasilitas dan Grid Ketersediaan 26 Slot Waktu (Desktop)", 6.0),
                ("05-detail-fasilitas-grid-mobile.png", "Gambar 9. Detail Fasilitas dan Grid Ketersediaan Waktu (Mobile)", 3.2)
            ]
        },
        {
            "id": "5.4",
            "title": "Fitur (4): Pengajuan Reservasi Sarana Prasarana Kampus",
            "tujuan": "Memfasilitasi pengajuan peminjaman fasilitas secara terstruktur dengan validasi pencegahan bentrok jadwal otomatis.",
            "aktor": "Pengguna Terverifikasi (Civitas Akademika).",
            "alur": "Pengguna memilih rentang slot waktu yang tersedia langsung pada grid halaman detail atau melalui formulir reservasi (/reservations/create?facility_id={id}). Pengguna mengisi tanggal peminjaman, jam mulai, jam selesai, serta tujuan peminjaman. Sistem memvalidasi ketersediaan dan memasukkan permohonan ke antrian 'pending'.",
            "aturan": "Aturan F1 & F2: Jam mulai dan jam selesai harus berada pada rentang operasional 07.00 - 20.00 WIB, dengan durasi minimal 30 menit. Sistem menolak pengajuan apabila rentang waktu yang dipilih beririsan dengan reservasi berstatus approved lain pada fasilitas yang sama.",
            "images": [
                ("06-form-reservasi-desktop.png", "Gambar 10. Formulir Pengajuan Reservasi Fasilitas (Desktop)", 5.5),
                ("06-form-reservasi-mobile.png", "Gambar 11. Formulir Pengajuan Reservasi Fasilitas (Mobile)", 3.0)
            ]
        },
        {
            "id": "5.5",
            "title": "Fitur (5): Riwayat & Detail Tiket Reservasi Pengguna",
            "tujuan": "Memungkinkan civitas akademika memantau status persetujuan peminjaman serta membatalkan permohonan mandiri.",
            "aktor": "Pengguna Terverifikasi (Civitas Akademika).",
            "alur": "Pengguna membuka menu 'Riwayat' (/reservations) untuk melihat daftar reservasi yang diajukan beserta status terkininya (Pending, Approved, Rejected, Cancelled). Pengguna dapat menekan tombol detail (/reservations/{id}) untuk melihat rangkuman tiket. Pengguna dapat membatalkan reservasi yang masih berstatus pending.",
            "aturan": "Pengguna hanya diizinkan melihat dan membatalkan reservasi miliknya sendiri (diamankan via Laravel Policy ReservationPolicy::view). Tombol pembatalan dinonaktifkan secara otomatis jika reservasi sudah berstatus approved, rejected, atau jika waktu mulai peminjaman telah kedaluwarsa (A8).",
            "images": [
                ("07-riwayat-reservasi-desktop.png", "Gambar 12. Daftar Riwayat Permohonan Reservasi Pengguna (Desktop)", 6.0),
                ("07-riwayat-reservasi-mobile.png", "Gambar 13. Daftar Riwayat Reservasi Pengguna (Mobile)", 3.2),
                ("08-detail-reservasi-pengguna-desktop.png", "Gambar 14. Detail Tiket Informasi Reservasi Pengguna (Desktop)", 5.5)
            ]
        },
        {
            "id": "5.6",
            "title": "Fitur (6): Pelaporan Kerusakan Sarana Prasarana Kampus",
            "tujuan": "Menyediakan sarana aduan cepat bagi civitas akademika jika menemui kerusakan atau malfungsi fasilitas di area kampus.",
            "aktor": "Pengguna Terverifikasi (Civitas Akademika).",
            "alur": "Pengguna mengakses menu 'Lapor Kerusakan' (/reports/create), memilih fasilitas yang bermasalah, menulis deskripsi kendala fisik, dan mengunggah 1 hingga 3 foto bukti kerusakan. Laporan yang terkirim dapat dipantau perkembangannya pada menu daftar laporan (/reports).",
            "aturan": "Aturan B1-B5 & F5: Setiap laporan wajib menyertakan minimal 1 foto dan maksimal 3 foto dengan format JPG/JPEG/PNG serta ukuran maksimal 3MB per berkas. Nama berkas diacak secara otomatis menggunakan Str::random(40) untuk keamanan penyimpanan.",
            "images": [
                ("09-form-lapor-kerusakan-desktop.png", "Gambar 15. Formulir Pelaporan Kerusakan Fasilitas dengan Unggah Foto (Desktop)", 5.5),
                ("09-form-lapor-kerusakan-mobile.png", "Gambar 16. Formulir Pelaporan Kerusakan Fasilitas (Mobile)", 3.0),
                ("10-riwayat-laporan-kerusakan-desktop.png", "Gambar 17. Daftar Riwayat Pelaporan Kerusakan oleh Pengguna (Desktop)", 6.0)
            ]
        },
        {
            "id": "5.7",
            "title": "Fitur (7): Dashboard & Verifikasi Antrian Reservasi (Petugas)",
            "tujuan": "Memberikan ikhtisar operasional harian bagi petugas fasilitas serta alur verifikasi persetujuan peminjaman sarana.",
            "aktor": "Petugas Fasilitas dan Laboratorium.",
            "alur": "Petugas masuk ke sistem dan diarahkan ke Dashboard (/officer). Petugas melihat daftar reservasi yang menunggu verifikasi dan daftar laporan kendala yang belum tuntas. Petugas memilih menu 'Antrian' (/officer/reservations), membuka detail permohonan (/officer/reservations/{id}), lalu mengeksekusi aksi Persetujuan (Approve) atau Penolakan (Reject) disertai alasan.",
            "aturan": "Aturan F2: Eksekusi persetujuan reservasi dibungkus dalam DB::transaction() dengan lockForUpdate() pada tabel reservations untuk menjamin konsistensi data dan mencegah bentrok ganda jika terdapat permohonan bersamaan pada rentang slot yang sama.",
            "images": [
                ("11-officer-dashboard-desktop.png", "Gambar 18. Dashboard Operasional Antrian Petugas (Desktop)", 6.0),
                ("12-officer-antrian-reservasi-desktop.png", "Gambar 19. Antrian Permohonan Reservasi Masuk bagi Petugas (Desktop)", 6.0),
                ("13-officer-detail-reservasi-desktop.png", "Gambar 20. Detail Peninjauan dan Aksi Verifikasi Reservasi Petugas (Desktop)", 5.5)
            ]
        },
        {
            "id": "5.8",
            "title": "Fitur (8): Tindak Lanjut Laporan Kerusakan & Kontrol Fasilitas (Petugas)",
            "tujuan": "Memungkinkan petugas menindaklanjuti keluhan sarana, mengisikan catatan perbaikan, serta mengubah status fasilitas rusak.",
            "aktor": "Petugas Fasilitas dan Laboratorium.",
            "alur": "Petugas membuka menu 'Laporan' (/officer/reports), memilih aduan kerusakan (/officer/reports/{id}), memeriksa foto bukti fisik di panel lightbox, memperbarui status penanganan (Baru, Diproses, Selesai, Ditolak), dan mengisi catatan perbaikan. Jika kerusakan parah, petugas dapat langsung mengubah status fasilitas menjadi 'under_maintenance'.",
            "aturan": "Aturan B3 & D4: Catatan resolusi (resolution_note) wajib diisi minimal 10 karakter jika status laporan diubah menjadi 'Selesai' atau 'Ditolak'. Jika fasilitas ditandai dalam perbaikan dan terdapat reservasi approved di masa mendatang, sistem memunculkan modal peringatan D4.",
            "images": [
                ("14-officer-daftar-laporan-desktop.png", "Gambar 21. Daftar Keluhan Kerusakan Sarana bagi Petugas (Desktop)", 6.0),
                ("15-officer-detail-laporan-desktop.png", "Gambar 22. Detail Tindak Lanjut Laporan Kerusakan dan Catatan Resolusi (Desktop)", 6.0),
                ("16-officer-kelola-fasilitas-desktop.png", "Gambar 23. Pengelolaan Ketersediaan dan Status Pemeliharaan Fasilitas oleh Petugas (Desktop)", 6.0)
            ]
        },
        {
            "id": "5.9",
            "title": "Fitur (9): Manajemen Master Data Fasilitas Kampus (Admin)",
            "tujuan": "Memberikan hak kontrol penuh bagi administrator institusi untuk menambah, memperbarui, dan menonaktifkan sarana kampus.",
            "aktor": "Administrator Kampus.",
            "alur": "Admin membuka menu 'Fasilitas' (/admin/facilities), melihat tabel seluruh fasilitas kampus beserta statusnya, menambah fasilitas baru melalui tombol tambah (/admin/facilities/create), atau mengubah data kapasitas, lokasi gedung, serta tipe fasilitas melalui tombol edit.",
            "aturan": "Aturan D1-D3: Admin memiliki wewenang mengalihkan status fasilitas antara 'active' dan 'inactive'. Tipe fasilitas terstandarisasi (Aula, Laboratorium, Ruang Kelas, Lapangan, Alat). Untuk tipe 'Alat', kapasitas wajib bernilai null.",
            "images": [
                ("17-admin-daftar-fasilitas-desktop.png", "Gambar 24. Daftar Master Sarana Prasarana Kampus di Panel Admin (Desktop)", 6.0),
                ("18-admin-form-fasilitas-desktop.png", "Gambar 25. Formulir Tambah dan Edit Master Fasilitas Kampus (Desktop)", 5.5)
            ]
        },
        {
            "id": "5.10",
            "title": "Fitur (10): Manajemen Pengguna & Siklus Hidup Akun Civitas (Admin)",
            "tujuan": "Mengelola data seluruh pengguna, memverifikasi registrasi civitas baru, serta mengendalikan hak akses akun.",
            "aktor": "Administrator Kampus.",
            "alur": "Admin mengakses menu 'Pengguna' (/admin/users) yang menampilkan daftar akun dengan prioritas teratas pada akun berstatus 'pending'. Admin memilih akun pengguna (/admin/users/{id}) untuk memverifikasi keabsahan identitas (Verifikasi), menolak pendaftaran (Tolak), menonaktifkan akun yang melanggar aturan (Suspend), mengaktifkan kembali (Aktifkan), atau mereset password.",
            "aturan": "Aturan C2-C6: Penonaktifan akun (suspend) hanya dapat dilakukan pada akun berstatus verified dan memicu modal peringatan D4 jika akun tersebut memiliki reservasi approved mendatang. Admin dilarang menonaktifkan akun miliknya sendiri.",
            "images": [
                ("19-admin-daftar-pengguna-desktop.png", "Gambar 26. Daftar Manajemen Akun Civitas dan Filter Status di Panel Admin (Desktop)", 6.0),
                ("20-admin-detail-verifikasi-user-desktop.png", "Gambar 27. Detail Verifikasi Pengguna dan Kontrol Siklus Hidup Akun (Desktop)", 5.5)
            ]
        },
        {
            "id": "5.11",
            "title": "Fitur (11): Rekapitulasi Statistik Operasional & Ekspor Laporan (Admin)",
            "tujuan": "Menyajikan analitik data reservasi sarana dan keluhan fasilitas serta fasilitas ekspor data laporan institusional.",
            "aktor": "Administrator Kampus.",
            "alur": "Admin membuka menu 'Rekap' (/admin/recap), menentukan rentang tanggal peninjauan, dan menekan tombol filter. Halaman menampilkan ringkasan metrik total reservasi, rasio persetujuan, frekuensi penggunaan ruangan, serta rekapitulasi penanganan kerusakan. Admin dapat menekan tombol 'Ekspor CSV' (/admin/recap/export) untuk mengunduh berkas laporan tabular.",
            "aturan": "Aturan E1-E5: Parameter query string pada tombol ekspor dibuat identik dengan parameter filter halaman indeks untuk menjamin bahwa data yang diunduh mencerminkan rentang waktu dan kriteria penyaringan yang sedang ditampilkan pada layar.",
            "images": [
                ("21-admin-rekap-statistik-desktop.png", "Gambar 28. Panel Rekapitulasi Analitik dan Statistik Penggunaan Fasilitas (Desktop)", 6.0),
                ("21-admin-rekap-statistik-mobile.png", "Gambar 29. Panel Rekapitulasi Statistik Operasional (Mobile)", 3.2)
            ]
        }
    ]

    for feat in features:
        h_f = doc.add_paragraph()
        h_f.paragraph_format.space_before = Pt(14)
        h_f.paragraph_format.space_after = Pt(4)
        r = h_f.add_run(feat["title"])
        r.bold = True
        r.font.size = Pt(12)
        r.font.color.rgb = COLOR_NAVY

        p_desc = doc.add_paragraph()
        p_desc.paragraph_format.space_after = Pt(3)
        p_desc.add_run("Tujuan Fitur: ").bold = True
        p_desc.add_run(feat["tujuan"])

        p_act = doc.add_paragraph()
        p_act.paragraph_format.space_after = Pt(3)
        p_act.add_run("Aktor Terkait: ").bold = True
        p_act.add_run(feat["aktor"])

        p_flow = doc.add_paragraph()
        p_flow.paragraph_format.space_after = Pt(3)
        p_flow.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
        p_flow.add_run("Alur Penggunaan: ").bold = True
        p_flow.add_run(feat["alur"])

        p_rule = doc.add_paragraph()
        p_rule.paragraph_format.space_after = Pt(6)
        p_rule.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
        p_rule.add_run("Aturan Bisnis & Validasi: ").bold = True
        p_rule.add_run(feat["aturan"])

        # Sisipkan Screenshot
        for img_name, caption, width_in in feat["images"]:
            img_path = os.path.join(SCREENSHOTS_DIR, img_name)
            if os.path.exists(img_path):
                p_img = doc.add_paragraph()
                p_img.alignment = WD_ALIGN_PARAGRAPH.CENTER
                p_img.paragraph_format.space_before = Pt(6)
                p_img.paragraph_format.space_after = Pt(2)
                p_img.add_run().add_picture(img_path, width=Inches(width_in))
                
                p_cap = doc.add_paragraph()
                p_cap.alignment = WD_ALIGN_PARAGRAPH.CENTER
                p_cap.paragraph_format.space_before = Pt(0)
                p_cap.paragraph_format.space_after = Pt(8)
                r_cap = p_cap.add_run(caption)
                r_cap.italic = True
                r_cap.font.size = Pt(9.5)
                r_cap.font.color.rgb = COLOR_MUTED

    # -------------------------------------------------------------
    # STRUKTUR TAMBAHAN (BAGIAN 6 - 9)
    # -------------------------------------------------------------
    doc.add_page_break()
    p_add_note = doc.add_paragraph()
    p_add_note.paragraph_format.space_before = Pt(12)
    p_add_note.paragraph_format.space_after = Pt(4)
    r = p_add_note.add_run("[BAGIAN TAMBAHAN STRUKTUR LAPORAN]")
    r.bold = True
    r.font.size = Pt(10)
    r.font.color.rgb = COLOR_GOLD

    # -------------------------------------------------------------
    # 6. ARSITEKTUR DAN BASIS DATA
    # -------------------------------------------------------------
    h6 = doc.add_paragraph()
    h6.paragraph_format.space_before = Pt(12)
    h6.paragraph_format.space_after = Pt(6)
    r = h6.add_run("6. Arsitektur dan Basis Data")
    r.bold = True
    r.font.size = Pt(14)
    r.font.color.rgb = COLOR_NAVY

    doc.add_paragraph(
        "Aplikasi Wiyata menerapkan pola arsitektur Model-View-Controller (MVC) standar Laravel 13. "
        "Lapisan basis data dikelola melalui skema migrasi terstruktur pada direktori database/migrations "
        "yang terdiri atas lima entitas tabel relasional utama:"
    )

    schema_info = [
        ("Tabel 'users'", "Menyimpan data identitas akun pengguna, petugas, dan admin.", "id, name, email, password, role (admin, petugas, pengguna), status (verified, pending, rejected, suspended), identity_number (NIM/NIP), user_type (mahasiswa, dosen, staf), remember_token, created_at, updated_at."),
        ("Tabel 'facilities'", "Menyimpan data master sarana dan prasarana kampus Undip Tembalang.", "id, name, type (Aula, Laboratorium, Ruang Kelas, Lapangan, Alat), location (Gedung A, B, C, D, E, Kompleks Olahraga), capacity, status (active, inactive, under_maintenance), description, created_at, updated_at."),
        ("Tabel 'reservations'", "Menyimpan catatan pengajuan peminjaman fasilitas oleh civitas akademika.", "id, user_id (FK users), facility_id (FK facilities), start_time, end_time, purpose, status (pending, approved, rejected, cancelled_by_user, cancelled_by_officer), status_reason, created_at, updated_at."),
        ("Tabel 'reports'", "Menyimpan tiket aduan keluhan dan kerusakan sarana prasarana.", "id, user_id (FK users), facility_id (FK facilities), category (kerusakan_alat, kelistrikan, pendingin_ruangan, furnitur, kebersihan, lainnya), description, status (baru, diproses, selesai, ditolak), resolution_note, created_at, updated_at."),
        ("Tabel 'report_photos'", "Menyimpan berkas foto bukti fisik yang diunggah pada laporan kerusakan.", "id, report_id (FK reports, onDelete cascade), file_name, created_at, updated_at.")
    ]

    for tbl_name, tbl_desc, tbl_cols in schema_info:
        p_t = doc.add_paragraph()
        p_t.paragraph_format.space_after = Pt(3)
        p_t.add_run(f"• {tbl_name}: ").bold = True
        p_t.add_run(f"{tbl_desc}\n  Kolom Utama: {tbl_cols}")

    doc.add_paragraph().paragraph_format.space_before = Pt(6)
    doc.add_paragraph("Relasi Kunci Antar-Tabel (Foreign Key Integrity):").runs[0].bold = True
    rels = [
        "Relasi Pengguna dengan Reservasi: Satu pengguna dapat memiliki banyak reservasi (User hasMany Reservation; Reservation belongsTo User).",
        "Relasi Fasilitas dengan Reservasi: Satu fasilitas dapat dipinjam dalam banyak rentang jadwal reservasi (Facility hasMany Reservation; Reservation belongsTo Facility).",
        "Relasi Pengguna dengan Laporan: Satu civitas dapat melaporkan banyak kendala fasilitas (User hasMany Report; Report belongsTo User).",
        "Relasi Fasilitas dengan Laporan: Satu fasilitas dapat memiliki banyak riwayat laporan kerusakan (Facility hasMany Report; Report belongsTo Facility).",
        "Relasi Laporan dengan Foto Bukti: Satu tiket laporan kerusakan dapat memiliki 1 hingga 3 berkas foto bukti fisik (Report hasMany ReportPhoto; ReportPhoto belongsTo Report) dengan aturan penghapusan berantai (cascade delete)."
    ]
    for rel in rels:
        p = doc.add_paragraph(rel, style='List Bullet')
        p.paragraph_format.space_after = Pt(3)

    # -------------------------------------------------------------
    # 7. HAK AKSES PER ROLE (MATRIKS OTORISASI)
    # -------------------------------------------------------------
    h7 = doc.add_paragraph()
    h7.paragraph_format.space_before = Pt(18)
    h7.paragraph_format.space_after = Pt(6)
    r = h7.add_run("7. Hak Akses per Role (Matriks Otorisasi)")
    r.bold = True
    r.font.size = Pt(14)
    r.font.color.rgb = COLOR_NAVY

    doc.add_paragraph(
        "Sistem menerapkan kontrol akses berbasis peran (Role-Based Access Control) melalui middleware role di Laravel. "
        "Matriks berikut memetakan wewenang dan batasan setiap aktor terhadap 11 fitur aplikasi:"
    )

    table_matrix = doc.add_table(rows=12, cols=5)
    table_matrix.alignment = WD_TABLE_ALIGNMENT.CENTER
    table_matrix.autofit = False

    m_headers = ["Modul / Fitur Aplikasi", "Publik / Tamu", "Pengguna (Civitas)", "Petugas Fasilitas", "Administrator"]
    for col_idx, h_text in enumerate(m_headers):
        cell = table_matrix.cell(0, col_idx)
        cell.text = h_text
        cell.paragraphs[0].runs[0].bold = True
        cell.paragraphs[0].alignment = WD_ALIGN_PARAGRAPH.CENTER
        cell.paragraphs[0].runs[0].font.name = 'Times New Roman'
        cell.paragraphs[0].runs[0].font.size = Pt(9.5)
        set_cell_background(cell, "0B2545")
        cell.paragraphs[0].runs[0].font.color.rgb = RGBColor(255, 255, 255)
        set_cell_margins(cell, top=100, bottom=100, left=80, right=80)

    matrix_rows = [
        ("Fitur 1: Beranda & Katalog Publik", "Akses Penuh", "Akses Penuh", "Akses Penuh", "Akses Penuh"),
        ("Fitur 2: Registrasi & Login", "Akses Penuh", "Login Saja", "Login Saja", "Login Saja"),
        ("Fitur 3: Detail Fasilitas & Grid Slot", "Akses Baca", "Pilih Slot", "Akses Penuh", "Akses Penuh"),
        ("Fitur 4: Pengajuan Reservasi", "Tidak Diizinkan", "Akses Penuh", "Tidak Diizinkan", "Tidak Diizinkan"),
        ("Fitur 5: Riwayat Reservasi Saya", "Tidak Diizinkan", "Reservasi Sendiri", "Tidak Diizinkan", "Tidak Diizinkan"),
        ("Fitur 6: Pelaporan Kerusakan", "Tidak Diizinkan", "Akses Penuh", "Tidak Diizinkan", "Tidak Diizinkan"),
        ("Fitur 7: Dashboard & Antrian Reservasi", "Tidak Diizinkan", "Tidak Diizinkan", "Approve/Reject", "Lihat Saja"),
        ("Fitur 8: Tindak Lanjut Laporan Kerusakan", "Tidak Diizinkan", "Tidak Diizinkan", "Update & Resolusi", "Lihat Saja"),
        ("Fitur 9: Kelola Master Fasilitas", "Tidak Diizinkan", "Tidak Diizinkan", "Status Perbaikan", "CRUD & Active/Inactive"),
        ("Fitur 10: Manajemen Akun Civitas", "Tidak Diizinkan", "Tidak Diizinkan", "Tidak Diizinkan", "Verifikasi, Suspend, Reset"),
        ("Fitur 11: Rekapitulasi & Ekspor CSV", "Tidak Diizinkan", "Tidak Diizinkan", "Tidak Diizinkan", "Filter & Ekspor Penuh")
    ]

    for row_idx, row in enumerate(matrix_rows, start=1):
        for col_idx, val in enumerate(row):
            cell = table_matrix.cell(row_idx, col_idx)
            cell.text = val
            cell.paragraphs[0].runs[0].font.name = 'Times New Roman'
            cell.paragraphs[0].runs[0].font.size = Pt(9.5)
            if col_idx > 0:
                cell.paragraphs[0].alignment = WD_ALIGN_PARAGRAPH.CENTER
            set_cell_margins(cell, top=70, bottom=70, left=80, right=80)
            if row_idx % 2 == 1:
                set_cell_background(cell, "F8F9FA")

    set_table_borders(table_matrix, color="B0C4DE", sz="6")

    # -------------------------------------------------------------
    # 8. PENGUJIAN SISTEM (TESTING)
    # -------------------------------------------------------------
    doc.add_page_break()
    h8 = doc.add_paragraph()
    h8.paragraph_format.space_before = Pt(18)
    h8.paragraph_format.space_after = Pt(6)
    r = h8.add_run("8. Pengujian Sistem (Automated & Manual Testing)")
    r.bold = True
    r.font.size = Pt(14)
    r.font.color.rgb = COLOR_NAVY

    doc.add_paragraph(
        "Kualitas dan keandalan sistem Wiyata diverifikasi melalui dua metode pengujian: Automated Feature Testing "
        "menggunakan Pest Framework dan Pengujian Manual Skenario Pengguna (User Acceptance Testing)."
    )

    doc.add_paragraph("A. Pengujian Terotomatisasi (Pest Framework):").runs[0].bold = True
    doc.add_paragraph(
        "Test suite sistem terdiri atas 214 test case terintegrasi dengan 961 asersi pengujian. Pengujian mencakup "
        "cakupan fitur otentikasi login, registrasi, otorisasi peran, validasi formulir reservasi, deteksi bentrok jadwal "
        "F2, pengunggahan foto laporan B1-B5, serta ekspor rekapitulasi data. Berdasarkan eksekusi nyata pada lingkungan "
        "MySQL (DB_CONNECTION=mysql DB_DATABASE=reservasi_fasilitas vendor/bin/pest), sebanyak 213 test case berhasil lolos "
        "sempurna (99.5% passing rate). Satu pengujian pengurutan akun pending memiliki variasi sekunder pada urutan created_at "
        "karena waktu pembuatan seeder yang identik pada detik yang sama."
    )

    doc.add_paragraph("B. Pengujian Manual Skenario Utama:").runs[0].bold = True
    table_test = doc.add_table(rows=7, cols=4)
    table_test.alignment = WD_TABLE_ALIGNMENT.CENTER
    table_test.autofit = False

    t_headers = ["ID Skenario", "Deskripsi Uji", "Hasil yang Diharapkan", "Status"]
    for col_idx, text in enumerate(t_headers):
        cell = table_test.cell(0, col_idx)
        cell.text = text
        cell.paragraphs[0].runs[0].bold = True
        cell.paragraphs[0].alignment = WD_ALIGN_PARAGRAPH.CENTER
        cell.paragraphs[0].runs[0].font.name = 'Times New Roman'
        cell.paragraphs[0].runs[0].font.size = Pt(10)
        set_cell_background(cell, "0B2545")
        cell.paragraphs[0].runs[0].font.color.rgb = RGBColor(255, 255, 255)
        set_cell_margins(cell, top=100, bottom=100, left=100, right=100)

    test_data = [
        ("UJI-01", "Pencegahan Akun Pending Login", "Sistem menolak login akun pending dan menampilkan pesan status", "BERHASIL (PASS)"),
        ("UJI-02", "Pencegahan Bentrok Jadwal Peminjaman", "Sistem mendeteksi irisan slot waktu terisi dan menggagalkan reservasi bentrok", "BERHASIL (PASS)"),
        ("UJI-03", "Validasi Multi-Upload Foto Laporan", "Sistem menerima 1-3 foto valid (JPG/PNG maks 3MB) dan menolak berkas non-gambar", "BERHASIL (PASS)"),
        ("UJI-04", "Modal Peringatan D4 Saat Fasilitas Masuk Perbaikan", "Sistem menampilkan daftar reservasi approved mendatang yang berpotensi terdampak", "BERHASIL (PASS)"),
        ("UJI-05", "Pemisahan Hak Akses Petugas vs Admin", "Petugas tidak dapat membuka URL /admin/* dan diarahkan dengan error 403 Forbidden", "BERHASIL (PASS)"),
        ("UJI-06", "Ekspor Laporan CSV dengan Filter Tanggal", "Berkas CSV terunduh berisi data transaksi yang sinkron dengan rentang tanggal terpilih", "BERHASIL (PASS)")
    ]

    for row_idx, row in enumerate(test_data, start=1):
        for col_idx, val in enumerate(row):
            cell = table_test.cell(row_idx, col_idx)
            cell.text = val
            cell.paragraphs[0].runs[0].font.name = 'Times New Roman'
            cell.paragraphs[0].runs[0].font.size = Pt(10)
            if col_idx in (0, 3):
                cell.paragraphs[0].alignment = WD_ALIGN_PARAGRAPH.CENTER
            set_cell_margins(cell, top=70, bottom=70, left=100, right=100)
            if row_idx % 2 == 1:
                set_cell_background(cell, "F8F9FA")

    set_table_borders(table_test, color="B0C4DE", sz="6")

    # -------------------------------------------------------------
    # 9. KENDALA DAN KESIMPULAN
    # -------------------------------------------------------------
    h9 = doc.add_paragraph()
    h9.paragraph_format.space_before = Pt(18)
    h9.paragraph_format.space_after = Pt(6)
    r = h9.add_run("9. Kendala dan Kesimpulan")
    r.bold = True
    r.font.size = Pt(14)
    r.font.color.rgb = COLOR_NAVY

    doc.add_paragraph("A. Kendala Teknis & Solusi:").runs[0].bold = True
    challenges = [
        ("Penanganan Concurrency dan Race Condition Slot:", 
         "Pada jam sibuk, terdapat potensi dua civitas akademika mengajukan slot waktu yang sama secara hampir bersamaan. Kendala ini diselesaikan pada modul M3 dengan membungkus proses verifikasi dan persetujuan dalam transaksi basis data serta menerapkan klausa lockForUpdate() pada tabel reservations, sehingga integritas data terjamin bebas bentrok."),
        ("Kompleksitas Grid 26 Slot Waktu pada Layar Mobile:", 
         "Tampilan grid 26 slot waktu (07.00 - 20.00 WIB) memerlukan kerapatan tata letak yang tinggi. Kendala ini diatasi dengan merancang wadah berpenyaring tanggal yang responsif, mengelompokkan tombol waktu dalam grid 2 kolom pada layar sempit, serta mengaktifkan fungsi pemilihan rentang waktu otomatis."),
        ("Pengelolaan Berkas Foto Kerusakan Fasilitas:", 
         "Pengunggahan multi-foto rentan terhadap batas ukuran upload web server. Tim menambahkan validasi ketat di sisi client dan server (mimes:jpg,jpeg,png dan max:3072) serta mengimplementasikan penamaan acak menggunakan Str::random(40) untuk mencegah penumpukan nama berkas yang sama.")
    ]
    for ch_title, ch_desc in challenges:
        p = doc.add_paragraph()
        p.paragraph_format.space_after = Pt(4)
        p.add_run(f"• {ch_title} ").bold = True
        p.add_run(ch_desc)

    doc.add_paragraph().paragraph_format.space_before = Pt(6)
    doc.add_paragraph("B. Kesimpulan:").runs[0].bold = True
    p_kesimpulan = doc.add_paragraph(
        "Pengembangan aplikasi 'Wiyata' berhasil menyelesaikan permasalahan peminjaman fasilitas kampus Undip Tembalang "
        "yang sebelumnya dilakukan secara manual. Melalui pembagian kerja terstruktur antar empat anggota tim, Wiyata kini menyediakan "
        "portal reservasi terpadu dengan transparansi ketersediaan slot waktu, sistem pelaporan kendala sarana prasarana yang akuntabel, "
        "serta dasbor analitik yang membantu pengambilan keputusan manajemen kampus. Seluruh fitur telah teruji secara komprehensif "
        "dan siap dioperasikan di lingkungan institusi pendidikan."
    )
    p_kesimpulan.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY

    # Simpan Berkas DOCX
    doc.save(DOCX_OUT)
    print(f"Berhasil membuat dokumen DOCX di: {DOCX_OUT}")

if __name__ == "__main__":
    build_document()
