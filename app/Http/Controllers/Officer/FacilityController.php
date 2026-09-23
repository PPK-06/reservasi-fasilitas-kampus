<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FacilityController extends Controller
{
    /**
     * O6 – Daftar Fasilitas (Ketersediaan)
     */
    public function index(Request $request)
    {
        $query = Facility::query();

        // Filter: Search Name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter: Type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter: Location
        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        // Filter: Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $facilities = $query->orderBy('name')->paginate(10)->withQueryString();

        // Ambil data reservasi mendatang (D4)
        // Hanya yang status 'approved' dan belum dimulai
        $upcomingReservations = Reservation::where('status', 'approved')
            ->where('start_time', '>', now())
            ->get()
            ->groupBy('facility_id');

        return view('officer.facilities.index', compact('facilities', 'upcomingReservations'));
    }

    /**
     * O6 – Ubah Status Fasilitas
     */
    public function status(Request $request, Facility $facility)
    {
        // Petugas tidak berwenang mengelola fasilitas inactive
        if ($facility->status === 'inactive') {
            abort(403, 'Anda tidak dapat mengubah status fasilitas yang dinonaktifkan.');
        }

        $request->validate([
            // Aturan D3: Petugas hanya bisa toggle active <-> under_maintenance
            'status' => ['required', Rule::in(['active', 'under_maintenance'])],
        ]);

        $facility->update([
            'status' => $request->status,
        ]);

        $label = $facility->statusLabel();

        // Menggunakan back() agar route ini juga bisa diakses dari O5 (Detail Laporan)
        return back()->with('success', "Status fasilitas \"{$facility->name}\" berhasil diubah menjadi {$label}.");
    }
}
