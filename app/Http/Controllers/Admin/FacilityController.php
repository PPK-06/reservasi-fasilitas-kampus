<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFacilityRequest;
use App\Http\Requests\UpdateFacilityRequest;
use App\Models\Facility;
use App\Models\Reservation;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    /**
     * A1 – Daftar Fasilitas (Admin)
     *
     * Menampilkan seluruh fasilitas dengan filter dan data peringatan D4.
     */
    public function index(Request $request)
    {
        $query = Facility::query();

        // ── Filter: pencarian nama ──
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        // ── Filter: tipe (F6 — konstanta model) ──
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // ── Filter: lokasi (F6 — konstanta model) ──
        if ($request->filled('location')) {
            $query->where('location', $request->input('location'));
        }

        // ── Filter: status (D3) ──
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $facilities = $query
            ->orderByRaw("FIELD(status, 'active', 'under_maintenance', 'inactive')")
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // ── D4: reservasi approved mendatang, dikelompokkan per fasilitas ──
        // Diambil sekaligus saat halaman dibuka (bagian 12 dokumen route).
        // Dipakai untuk modal peringatan sebelum admin menonaktifkan fasilitas.
        $upcomingReservations = Reservation::where('status', 'approved')
            ->where('start_time', '>', now())
            ->with('user:id,name')
            ->select('id', 'facility_id', 'start_time', 'end_time', 'user_id')
            ->orderBy('start_time')
            ->get()
            ->groupBy('facility_id');

        return view('admin.facilities.index', compact('facilities', 'upcomingReservations'));
    }

    /**
     * A1 – Ubah status fasilitas (admin: active ↔ inactive)
     *
     * D3 matriks wewenang: admin hanya menangani active ↔ inactive.
     * Validasi in:active,inactive memastikan admin tidak bisa set under_maintenance.
     */
    public function status(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive'],
        ], [
            'status.in' => 'Status hanya boleh diubah ke Aktif atau Nonaktif.',
        ]);

        $facility->status = $validated['status'];
        $facility->save();

        $label = Facility::STATUSES[$facility->status] ?? $facility->status;

        return redirect()
            ->route('admin.facilities.index')
            ->with('success', "Status fasilitas \"{$facility->name}\" berhasil diubah menjadi {$label}.");
    }

    /**
     * A2 – Form Tambah Fasilitas
     */
    public function create()
    {
        $facility = new Facility();
        return view('admin.facilities.form', compact('facility'));
    }

    /**
     * A2 – Simpan Fasilitas Baru
     */
    public function store(StoreFacilityRequest $request)
    {
        $facility = Facility::create($request->validated());

        return redirect()
            ->route('admin.facilities.index')
            ->with('success', "Fasilitas \"{$facility->name}\" berhasil ditambahkan.");
    }

    /**
     * A2 – Form Edit Fasilitas
     */
    public function edit(Facility $facility)
    {
        return view('admin.facilities.form', compact('facility'));
    }

    /**
     * A2 – Simpan Perubahan Fasilitas
     */
    public function update(UpdateFacilityRequest $request, Facility $facility)
    {
        $facility->update($request->validated());

        return redirect()
            ->route('admin.facilities.index')
            ->with('success', "Data fasilitas \"{$facility->name}\" berhasil diperbarui.");
    }
}
