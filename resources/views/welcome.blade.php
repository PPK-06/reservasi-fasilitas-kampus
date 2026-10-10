@extends('layouts.app')

@section('title', 'Reservasi Fasilitas Kampus · Wiyata')

@section('full-width-content')
{{-- ════════════════════════════════════════════════════════════════════════
     1. HERO SECTION DENGAN CITRA ARSITEKTUR WIDYA PURAYA UNDIP
     ════════════════════════════════════════════════════════════════════════ --}}
<section class="py-5 bg-white border-bottom position-relative overflow-hidden">
    <div class="container py-lg-4">
        <div class="row align-items-center g-4 g-lg-5">
            {{-- Kolom Teks & CTA --}}
            <div class="col-lg-6">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 mb-3 rounded-pill"
                     style="background-color: var(--undip-blue-surface); border: 1px solid #D6E4F0;">
                    <span class="badge rounded-pill" style="background-color: var(--undip-gold); color: #07172C; font-size: 0.72rem; font-weight: 700;">
                        WIYATA
                    </span>
                    <span class="small fw-semibold" style="color: var(--undip-navy); font-size: 0.8rem;">
                        Portal Fasilitas Kampus Undip Tembalang
                    </span>
                </div>

                <h1 class="display-5 fw-bold mb-3" style="color: var(--undip-navy-dark); line-height: 1.18; letter-spacing: -0.02em;">
                    Layanan Reservasi Sarana & Prasarana Kampus
                </h1>

                <p class="lead mb-4 text-muted" style="font-size: 1.05rem; line-height: 1.6;">
                    Akses peminjaman terpadu untuk ruang kelas perkuliahan, aula serbaguna, laboratorium riset, serta gedung olahraga di lingkungan Universitas Diponegoro Tembalang.
                </p>

                <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                    @auth
                        @if (auth()->user()->role === 'pengguna')
                            <a href="{{ route('reservations.create') }}" class="btn btn-undip-primary btn-lg px-4 shadow-sm">
                                <i class="bi bi-calendar-plus me-2"></i>Ajukan Reservasi Sekarang
                            </a>
                        @else
                            <a href="{{ route('facilities.index') }}" class="btn btn-undip-primary btn-lg px-4 shadow-sm">
                                <i class="bi bi-building me-2"></i>Buka Katalog Fasilitas
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-undip-primary btn-lg px-4 shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Masuk untuk Reservasi
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-outline-undip btn-lg px-4">
                            <i class="bi bi-person-plus me-2"></i>Daftar Akun Civitas
                        </a>
                    @endauth
                </div>

                {{-- Fakta Statistik Nyata --}}
                <div class="pt-3 border-top d-flex flex-wrap gap-4 text-muted small">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <span><strong>{{ $stats['total_active'] }}</strong> Fasilitas Aktif Terdata</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-geo-alt-fill text-primary fs-5"></i>
                        <span><strong>{{ $stats['locations_count'] }}</strong> Zona Gedung Tembalang</span>
                    </div>
                </div>
            </div>

            {{-- Kolom Gambar Nyata Widya Puraya --}}
            <div class="col-lg-6">
                <div class="card-undip overflow-hidden border shadow-lg" style="border-radius: 16px;">
                    <img src="{{ asset('images/landing/widya-puraya.webp') }}"
                         srcset="{{ asset('images/landing/widya-puraya-sm.webp') }} 480w,
                                 {{ asset('images/landing/widya-puraya.webp') }} 800w,
                                 {{ asset('images/landing/widya-puraya-lg.webp') }} 1200w"
                         sizes="(max-width: 768px) 100vw, 50vw"
                         alt="Gedung Widya Puraya Universitas Diponegoro Tembalang"
                         class="w-100 object-fit-cover"
                         style="height: 380px;"
                         loading="eager">

                    <div class="p-3 bg-white border-top">
                        <span class="d-block fw-bold text-dark font-heading">Gedung Widya Puraya Undip</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ════════════════════════════════════════════════════════════════════════
     2. FASILITAS UNGGULAN KAMPUS (BERDASARKAN DATA NYATA DATABASE)
     ════════════════════════════════════════════════════════════════════════ --}}
<section class="py-5" id="fasilitas-unggulan">
    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between mb-4 gap-3">
            <div>
                <span class="text-uppercase fw-bold text-muted small" style="letter-spacing: 0.05em;">Katalog Pilihan</span>
                <h2 class="h2 fw-bold mb-1" style="color: var(--undip-navy);">Fasilitas Unggulan Kampus</h2>
                <p class="text-muted mb-0">Daftar ruangan dan perlengkapan siap pakai di berbagai zona kampus Tembalang.</p>
            </div>
            <a href="{{ route('facilities.index') }}" class="btn btn-outline-undip fw-semibold">
                Buka Seluruh Fasilitas ({{ $stats['total_facilities'] }}) <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            @forelse ($facilities->take(6) as $f)
                @php
                    $imageSrc = $f->image_url;
                    $altText = $f->imageAlt();
                @endphp

                <div class="col-md-6 col-lg-4">
                    <div class="card card-undip card-undip-interactive h-100 d-flex flex-column">
                        {{-- Bagian Media / Thumbnail Bersih Tanpa Teks Overlay --}}
                        <div class="overflow-hidden" style="height: 200px; border-top-left-radius: 13px; border-top-right-radius: 13px;">
                            @if ($imageSrc)
                                <img src="{{ $imageSrc }}"
                                     sizes="(max-width: 768px) 100vw, 33vw"
                                     alt="{{ $altText }}"
                                     class="w-100 h-100 object-fit-cover"
                                     loading="lazy">
                            @else
                                <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center"
                                     style="background: linear-gradient(135deg, #07172C 0%, #134074 100%); color: #ffffff;">
                                    @if ($f->type === 'Lapangan')
                                        <i class="bi bi-trophy fs-1 mb-2 text-warning"></i>
                                        <span class="small fw-semibold text-white-50">Area Olahraga Tembalang</span>
                                    @else
                                        <i class="bi bi-projector fs-1 mb-2 text-warning"></i>
                                        <span class="small fw-semibold text-white-50">Inventaris Perlengkapan</span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Body Kartu --}}
                        <div class="card-body p-4 d-flex flex-column flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-light text-dark border fw-semibold" style="font-size: 0.75rem;">
                                    {{ $f->type }}
                                </span>
                            </div>

                            <h5 class="fw-bold mb-2 font-heading" style="color: var(--undip-navy-dark);">
                                {{ $f->name }}
                            </h5>

                            <div class="d-flex align-items-center text-muted small mb-2">
                                <i class="bi bi-geo-alt me-1 text-danger"></i>
                                <span>{{ $f->location }}</span>
                            </div>

                            @if (!is_null($f->capacity) && $f->type !== 'Alat')
                                <div class="d-flex align-items-center text-muted small mb-3">
                                    <i class="bi bi-people me-1 text-primary"></i>
                                    <span>Kapasitas: <strong>{{ $f->capacity }}</strong> orang</span>
                                </div>
                            @else
                                <div class="d-flex align-items-center text-muted small mb-3">
                                    <i class="bi bi-box-seam me-1 text-secondary"></i>
                                    <span>Unit Inventaris Sarana</span>
                                </div>
                            @endif

                            <p class="text-muted small mb-4 flex-grow-1" style="line-height: 1.5;">
                                {{ Str::limit($f->description ?: 'Fasilitas aktif yang dapat dipergunakan untuk kegiatan akademik dan kemahasiswaan dengan konfirmasi petugas.', 110) }}
                            </p>

                            <div class="d-flex align-items-center justify-content-between pt-3 border-top mt-auto">
                                <span class="badge-status badge-status-approved">
                                    <i class="bi bi-check-circle-fill"></i> Siap Dipesan
                                </span>
                                <a href="{{ route('facilities.show', $f) }}" class="btn btn-sm btn-outline-undip">
                                    Lihat Detail & Slot
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card p-5 text-center text-muted">
                        <i class="bi bi-inbox fs-1 mb-2"></i>
                        <p class="mb-0">Belum ada fasilitas aktif yang dapat ditampilkan saat ini.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- ════════════════════════════════════════════════════════════════════════
     3. CARA KERJA RESERVASI (4 LANGKAH JELAS & BERURUTAN)
     ════════════════════════════════════════════════════════════════════════ --}}
<section class="py-5 bg-white border-top border-bottom">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="text-uppercase fw-bold text-muted small" style="letter-spacing: 0.05em;">Alur Peminjaman</span>
            <h2 class="h2 fw-bold mb-2" style="color: var(--undip-navy);">Cara Kerja Reservasi Fasilitas</h2>
            <p class="text-muted mb-0">Empat langkah mudah pengajuan sarana bagi civitas akademika Universitas Diponegoro.</p>
        </div>

        <div class="row g-4">
            {{-- Langkah 1 --}}
            <div class="col-md-6 col-lg-3">
                <div class="card-undip p-4 h-100 border text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 mx-auto"
                         style="width: 54px; height: 54px; background-color: var(--undip-blue-surface); color: var(--undip-navy);">
                        <span class="fw-bold fs-5 font-heading">1</span>
                    </div>
                    <h5 class="fw-bold mb-2 font-heading">Pilih Fasilitas</h5>
                    <p class="text-muted small mb-0">
                        Cari ruangan atau sarana yang sesuai dengan kapasitas peserta dan lokasi gedung di kampus Tembalang.
                    </p>
                </div>
            </div>

            {{-- Langkah 2 --}}
            <div class="col-md-6 col-lg-3">
                <div class="card-undip p-4 h-100 border text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 mx-auto"
                         style="width: 54px; height: 54px; background-color: var(--undip-gold-subtle); color: #7A5200;">
                        <span class="fw-bold fs-5 font-heading">2</span>
                    </div>
                    <h5 class="fw-bold mb-2 font-heading">Pilih Waktu & Isi Data</h5>
                    <p class="text-muted small mb-0">
                        Tentukan tanggal kegiatan, pilih sesi waktu yang masih kosong, dan sertakan tujuan peminjaman secara jelas.
                    </p>
                </div>
            </div>

            {{-- Langkah 3 --}}
            <div class="col-md-6 col-lg-3">
                <div class="card-undip p-4 h-100 border text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 mx-auto"
                         style="width: 54px; height: 54px; background-color: #E6F7F0; color: #0D7A55;">
                        <span class="fw-bold fs-5 font-heading">3</span>
                    </div>
                    <h5 class="fw-bold mb-2 font-heading">Validasi Petugas</h5>
                    <p class="text-muted small mb-0">
                        Petugas sarana memverifikasi permohonan untuk memastikan ketiadaan bentrok jadwal sebelum persetujuan diterbitkan.
                    </p>
                </div>
            </div>

            {{-- Langkah 4 --}}
            <div class="col-md-6 col-lg-3">
                <div class="card-undip p-4 h-100 border text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 mx-auto"
                         style="width: 54px; height: 54px; background-color: #F1F5F9; color: #475569;">
                        <span class="fw-bold fs-5 font-heading">4</span>
                    </div>
                    <h5 class="fw-bold mb-2 font-heading">Gunakan & Laporkan</h5>
                    <p class="text-muted small mb-0">
                        Gunakan ruangan sesuai jam reservasi dan kirimkan laporan kerusakan apabila menjumpai kendala fasilitas.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ════════════════════════════════════════════════════════════════════════
     4. PREVIEW KETERSEDIAAN / KALENDER JADWAL NYATA
     ════════════════════════════════════════════════════════════════════════ --}}
@if ($previewFacility)
<section class="py-5">
    <div class="container py-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-5">
                <span class="text-uppercase fw-bold text-muted small" style="letter-spacing: 0.05em;">Transparansi Waktu</span>
                <h2 class="h2 fw-bold mb-3" style="color: var(--undip-navy);">
                    Pratinjau Ketersediaan Slot Waktu Nyata
                </h2>
                <p class="text-muted mb-3">
                    Sistem membagi jadwal dalam slot waktu terstandarisasi untuk mencegah tumpang tindih penggunaan fasilitas antarmahasiswa dan departemen.
                </p>
                <div class="p-3 bg-white rounded-3 border mb-3">
                    <div class="fw-bold text-dark font-heading">{{ $previewFacility->name }}</div>
                    <div class="text-muted small">
                        <i class="bi bi-geo-alt me-1"></i>{{ $previewFacility->location }} |
                        Tanggal: <strong>{{ \Carbon\Carbon::parse($previewDate)->format('d F Y') }}</strong>
                    </div>
                </div>
                <a href="{{ route('facilities.show', $previewFacility) }}" class="btn btn-undip-primary">
                    <i class="bi bi-calendar-event me-2"></i>Buka Jadwal Lengkap Fasilitas Ini
                </a>
            </div>

            <div class="col-lg-7">
                <div class="card-undip p-4 bg-white border">
                    <h6 class="fw-bold mb-3 font-heading text-dark">
                        Status Sesi Operasional ({{ \Carbon\Carbon::parse($previewDate)->format('d M Y') }})
                    </h6>

                    <div class="row g-2">
                        @foreach ($previewSlots as $key => $slot)
                            <div class="col-sm-6">
                                <div class="p-3 rounded-2 border d-flex align-items-center justify-content-between"
                                     style="background-color: {{ $slot['is_available'] ? '#F0FDF4' : '#F8FAFC' }};">
                                    <div>
                                        <div class="fw-bold text-dark small" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                                            {{ $slot['start'] }} – {{ $slot['end'] }}
                                        </div>
                                        <span class="text-muted" style="font-size: 0.76rem;">Sesi {{ $key }}</span>
                                    </div>
                                    @if ($slot['is_available'])
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">
                                            <i class="bi bi-check-circle me-1"></i>Tersedia
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 small">
                                            <i class="bi bi-x-circle me-1"></i>Terisi / Lewat
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3 pt-3 border-top text-muted small d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-info-circle me-1"></i>Pembaruan real-time mengikuti persetujuan petugas.</span>
                        <span class="fw-semibold text-dark">Sistem Terintegrasi</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- ════════════════════════════════════════════════════════════════════════
     5. ATURAN, KETENTUAN & PERTANYAAN UMUM (FAQ NYATA)
     ════════════════════════════════════════════════════════════════════════ --}}
<section class="py-5 bg-white border-top border-bottom">
    <div class="container py-4">
        <div class="row g-4 g-lg-5">
            {{-- Ketentuan Peminjaman --}}
            <div class="col-lg-5">
                <span class="text-uppercase fw-bold text-muted small" style="letter-spacing: 0.05em;">Pedoman Kampus</span>
                <h3 class="h3 fw-bold mb-3" style="color: var(--undip-navy);">Ketentuan Umum Peminjaman</h3>
                <p class="text-muted small mb-4">
                    Seluruh permohonan tunduk pada regulasi pemanfaatan sarana dan prasarana Universitas Diponegoro.
                </p>

                <div class="d-flex flex-column gap-3">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center p-2"
                             style="background: var(--undip-blue-surface); color: var(--undip-navy); width: 36px; height: 36px; flex-shrink: 0;">
                            <i class="bi bi-person-check-fill"></i>
                        </div>
                        <div>
                            <strong class="d-block text-dark small">Akun Terverifikasi</strong>
                            <span class="text-muted small">Pemohon wajib mahasiswa, dosen, atau tenaga kependidikan Undip dengan status akun terverifikasi admin.</span>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center p-2"
                             style="background: var(--undip-blue-surface); color: var(--undip-navy); width: 36px; height: 36px; flex-shrink: 0;">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div>
                            <strong class="d-block text-dark small">Tenggat Waktu Pengajuan</strong>
                            <span class="text-muted small">Pengajuan dilakukan sekurang-kurangnya 1 hari kalender sebelum jadwal pemakaian dimulai.</span>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center p-2"
                             style="background: var(--undip-blue-surface); color: var(--undip-navy); width: 36px; height: 36px; flex-shrink: 0;">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div>
                            <strong class="d-block text-dark small">Tanggung Jawab Pemeliharaan</strong>
                            <span class="text-muted small">Pemohon wajib menjaga kebersihan ruangan, mengembalikan tatanan alat, dan melaporkan kendala sarana yang ditemukan.</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FAQ Accordion --}}
            <div class="col-lg-7">
                <span class="text-uppercase fw-bold text-muted small" style="letter-spacing: 0.05em;">Bantuan Cepat</span>
                <h3 class="h3 fw-bold mb-3" style="color: var(--undip-navy);">Pertanyaan yang Sering Diajukan</h3>

                <div class="accordion accordion-flush card-undip border overflow-hidden" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faqHeadOne">
                            <button class="accordion-button fw-semibold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#faqOne" aria-expanded="true" aria-controls="faqOne">
                                Siapa saja yang berhak mengajukan permohonan reservasi fasilitas?
                            </button>
                        </h2>
                        <div id="faqOne" class="accordion-collapse collapse show" aria-labelledby="faqHeadOne" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted small">
                                Seluruh mahasiswa aktif, dosen, dan staf Universitas Diponegoro yang telah mendaftar dengan identitas resmi (NIM atau NIP) dan telah disetujui oleh admin kampus.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faqHeadTwo">
                            <button class="accordion-button collapsed fw-semibold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#faqTwo" aria-expanded="false" aria-controls="faqTwo">
                                Bagaimana jika terjadi bentrok jadwal penggunaan ruangan?
                            </button>
                        </h2>
                        <div id="faqTwo" class="accordion-collapse collapse" aria-labelledby="faqHeadTwo" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted small">
                                Sistem secara otomatis mendeteksi bentrok jadwal pada slot yang sama. Permohonan yang diajukan lebih awal dan memenuhi kriteria urgensi kegiatan akademik akan diprioritaskan oleh petugas validasi.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faqHeadThree">
                            <button class="accordion-button collapsed fw-semibold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#faqThree" aria-expanded="false" aria-controls="faqThree">
                                Apakah pemohon dapat membatalkan jadwal yang sudah diajukan?
                            </button>
                        </h2>
                        <div id="faqThree" class="accordion-collapse collapse" aria-labelledby="faqHeadThree" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted small">
                                Pembatalan mandiri dapat dilakukan oleh pengguna selama permohonan masih dalam status "Menunggu" melalui menu Riwayat Reservasi.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faqHeadFour">
                            <button class="accordion-button collapsed fw-semibold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#faqFour" aria-expanded="false" aria-controls="faqFour">
                                Apa yang harus dilakukan jika peralatan di dalam ruangan mengalami kerusakan?
                            </button>
                        </h2>
                        <div id="faqFour" class="accordion-collapse collapse" aria-labelledby="faqHeadFour" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted small">
                                Pengguna dapat memanfaatkan menu "Lapor Kerusakan" dengan menyertakan foto kendala di lokasi. Laporan akan ditindaklanjuti langsung oleh petugas pemeliharaan sarana kampus.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ════════════════════════════════════════════════════════════════════════
     6. LOKASI KAMPUS TEMBALANG & KONTAK PELAYANAN
     ════════════════════════════════════════════════════════════════════════ --}}
<section class="py-5">
    <div class="container py-4">
        <div class="card-undip p-4 p-lg-5 border bg-white">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <span class="text-uppercase fw-bold text-muted small" style="letter-spacing: 0.05em;">Lokasi Terpadu</span>
                    <h3 class="h3 fw-bold mb-3" style="color: var(--undip-navy);">
                        Kampus Universitas Diponegoro Tembalang
                    </h3>
                    <p class="text-muted mb-4">
                        Pusat pengelolaan sarana dan prasarana terpusat di kawasan kampus utama Tembalang, melayani kebutuhan seluruh fakultas dan unit kegiatan mahasiswa.
                    </p>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-geo-alt text-danger fs-5"></i>
                                <div class="small">
                                    <strong class="d-block text-dark">Alamat Kampus:</strong>
                                    <span class="text-muted">Jl. Prof. Sudarto, S.H., Tembalang, Semarang, Jawa Tengah 50275</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-clock text-primary fs-5"></i>
                                <div class="small">
                                    <strong class="d-block text-dark">Jam Kerja Layanan:</strong>
                                    <span class="text-muted">Senin – Jumat (07.30 – 16.00 WIB)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5 text-lg-end">
                    <div class="p-4 rounded-3 text-start mb-3" style="background-color: var(--undip-blue-surface); border: 1px solid #DCE7F2;">
                        <h6 class="fw-bold text-dark font-heading mb-2">Butuh Bantuan Reservasi?</h6>
                        <p class="text-muted small mb-3">
                            Konsultasikan kebutuhan aula besar atau penggunaan multi-hari bersama bagian sarana dan prasarana.
                        </p>
                        <a href="{{ route('facilities.index') }}" class="btn btn-undip-primary w-100">
                            <i class="bi bi-search me-2"></i>Eksplorasi Katalog Lengkap
                        </a>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <div class="card border rounded-2 overflow-hidden shadow-sm">
                                <div style="height: 90px;">
                                    <img src="{{ asset('images/landing/patung-diponegoro.webp') }}"
                                         srcset="{{ asset('images/landing/patung-diponegoro-sm.webp') }} 480w, {{ asset('images/landing/patung-diponegoro.webp') }} 800w"
                                         sizes="(max-width: 768px) 50vw, 20vw"
                                         alt="Monumen Patung Pangeran Diponegoro Tembalang"
                                         class="w-100 h-100 object-fit-cover"
                                         loading="lazy">
                                </div>
                                <div class="text-center py-1 bg-white border-top">
                                    <span class="text-dark small fw-semibold" style="font-size: 0.72rem;">Taman Diponegoro</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card border rounded-2 overflow-hidden shadow-sm">
                                <div style="height: 90px;">
                                    <img src="{{ asset('images/landing/masjid-kampus.webp') }}"
                                         srcset="{{ asset('images/landing/masjid-kampus-sm.webp') }} 480w, {{ asset('images/landing/masjid-kampus.webp') }} 800w"
                                         sizes="(max-width: 768px) 50vw, 20vw"
                                         alt="Masjid Kampus Universitas Diponegoro Tembalang"
                                         class="w-100 h-100 object-fit-cover"
                                         loading="lazy">
                                </div>
                                <div class="text-center py-1 bg-white border-top">
                                    <span class="text-dark small fw-semibold" style="font-size: 0.72rem;">Masjid Kampus</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
