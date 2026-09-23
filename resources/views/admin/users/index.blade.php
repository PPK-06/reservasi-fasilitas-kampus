@extends('layouts.app')

@section('title', 'Daftar Akun — Sistem Fasilitas Kampus')

@section('page-title', 'Daftar Akun')

@section('page-actions')
    {{-- Peta 2.4: A3 "Tambah Akun" → A4. A4 masih abort(501) sampai potongan 7 --}}
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-person-plus me-1"></i>Tambah Akun
    </a>
@endsection

@section('content')
@php
    /**
     * Label Bahasa Indonesia untuk teks yang dilihat admin. Nilai enum tetap
     * yang dikirim di query string (D10 dan aturan bahasa bagian 1 dokumen route).
     */
    $statusLabels = [
        'pending' => 'Menunggu',
        'verified' => 'Terverifikasi',
        'rejected' => 'Ditolak',
        'suspended' => 'Dinonaktifkan',
    ];

    $statusBadges = [
        'pending' => 'text-bg-warning',
        'verified' => 'text-bg-success',
        'rejected' => 'text-bg-danger',
        'suspended' => 'text-bg-secondary',
    ];

    $roleLabels = [
        'pengguna' => 'Pengguna',
        'petugas' => 'Petugas',
        'admin' => 'Admin',
    ];

    // D11 — status sebagai tab. Tab "Semua" tidak membawa parameter status (D12).
    $statusTabs = [
        ['value' => null, 'label' => 'Semua'],
        ['value' => 'pending', 'label' => 'Menunggu'],
        ['value' => 'verified', 'label' => 'Terverifikasi'],
        ['value' => 'rejected', 'label' => 'Ditolak'],
        ['value' => 'suspended', 'label' => 'Dinonaktifkan'],
    ];
@endphp

<div class="card border-0 shadow-sm">

    {{-- ── Filter: tab status + penanda pending + select role ── --}}
    <div class="card-header bg-white py-3">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">

            {{-- D11 — tab status. Setiap tautan membawa role yang sedang aktif
                 supaya filter role tidak hilang saat tab diganti. --}}
            <ul class="nav nav-pills gap-1 flex-wrap">
                @foreach ($statusTabs as $tab)
                    <li class="nav-item">
                        <a class="nav-link py-1 px-3 {{ $activeStatus === $tab['value'] ? 'active' : 'text-dark' }}"
                           style="font-size:0.86rem;"
                           href="{{ route('admin.users.index', array_filter([
                               'status' => $tab['value'],
                               'role' => $activeRole,
                           ])) }}">
                            {{ $tab['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="d-flex flex-wrap align-items-center gap-3">

                {{-- Penanda jumlah pending (bagian 1.4). Angkanya dari query
                     terpisah atas seluruh tabel (D13), jadi tidak ikut berubah
                     saat daftar difilter. Netral saat 0, tapi tetap tampil. --}}
                <a href="{{ route('admin.users.index', ['status' => 'pending']) }}"
                   class="text-decoration-none d-inline-flex align-items-center gap-2">
                    <span class="text-muted" style="font-size:0.86rem;">Menunggu verifikasi</span>
                    <span class="badge rounded-pill {{ $pendingCount > 0 ? 'text-bg-warning' : 'text-bg-light text-dark border' }}">
                        {{ $pendingCount }}
                    </span>
                </a>

                {{-- D11 — role sebagai select. Status yang sedang aktif dibawa
                     sebagai hidden input, kalau tidak filter status hilang tiap
                     kali role diganti. --}}
                <form method="GET" action="{{ route('admin.users.index') }}" class="d-flex align-items-center gap-2">
                    @if ($activeStatus !== null)
                        <input type="hidden" name="status" value="{{ $activeStatus }}">
                    @endif

                    <label for="role" class="visually-hidden">Role</label>
                    <select name="role" id="role" class="form-select form-select-sm" style="width:auto;">
                        <option value="">Semua Role</option>
                        @foreach ($roleLabels as $value => $label)
                            <option value="{{ $value }}" @selected($activeRole === $value)>{{ $label }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-funnel me-1"></i>Terapkan
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Tabel ── --}}
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background-color: #0d3880; color: #fff;">
                    <tr>
                        <th class="ps-3">Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>NIM/NIP</th>
                        <th class="pe-3">Terdaftar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        @php $rowUrl = route('admin.users.show', $user); @endphp
                        {{-- Peta 2.4: A3 klik baris → A5. Seluruh sel jadi tautan
                             supaya barisnya bisa diklik tanpa JavaScript. A5 masih
                             abort(501) sampai potongan 5. --}}
                        <tr>
                            <td class="p-0">
                                <a href="{{ $rowUrl }}" class="d-block text-decoration-none text-reset fw-semibold ps-3 pe-2 py-2">
                                    {{ $user->name }}
                                </a>
                            </td>
                            <td class="p-0">
                                <a href="{{ $rowUrl }}" class="d-block text-decoration-none text-reset px-2 py-2">
                                    {{ $user->email }}
                                </a>
                            </td>
                            <td class="p-0">
                                <a href="{{ $rowUrl }}" class="d-block text-decoration-none text-reset px-2 py-2">
                                    {{ $roleLabels[$user->role] ?? $user->role }}
                                </a>
                            </td>
                            <td class="p-0">
                                <a href="{{ $rowUrl }}" class="d-block text-decoration-none text-reset px-2 py-2">
                                    <span class="badge {{ $statusBadges[$user->status] ?? 'text-bg-light text-dark border' }}">
                                        {{ $statusLabels[$user->status] ?? $user->status }}
                                    </span>
                                </a>
                            </td>
                            <td class="p-0">
                                {{-- Skema 6.1: nullable karena role — petugas dan
                                     admin memang tidak punya NIM/NIP (C4) --}}
                                <a href="{{ $rowUrl }}" class="d-block text-decoration-none text-reset px-2 py-2">
                                    {{ $user->identity_number ?? '—' }}
                                </a>
                            </td>
                            <td class="p-0">
                                <a href="{{ $rowUrl }}" class="d-block text-decoration-none text-reset px-2 pe-3 py-2 text-muted">
                                    {{ $user->created_at?->format('d M Y') ?? '—' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-people fs-3 d-block mb-2"></i>

                                @if ($activeStatus !== null || $activeRole !== null)
                                    Tidak ada akun dengan
                                    @if ($activeStatus !== null)
                                        status <strong>{{ $statusLabels[$activeStatus] }}</strong>
                                    @endif
                                    @if ($activeStatus !== null && $activeRole !== null)
                                        dan
                                    @endif
                                    @if ($activeRole !== null)
                                        role <strong>{{ $roleLabels[$activeRole] }}</strong>
                                    @endif.
                                    <a href="{{ route('admin.users.index') }}" class="d-block mt-2">
                                        Hapus filter
                                    </a>
                                @else
                                    Belum ada akun terdaftar.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Paginasi (D16). Bootstrap 5 sudah disetel di AppServiceProvider (F11) ── --}}
    <div class="card-footer bg-white border-top d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
        <span class="text-muted" style="font-size:0.85rem;">
            {{ $users->total() }} akun
            @if ($activeStatus !== null || $activeRole !== null)
                cocok dengan filter
            @endif
        </span>

        @if ($users->hasPages())
            {{ $users->links() }}
        @endif
    </div>
</div>
@endsection
