<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Support\Slot;

class LandingController extends Controller
{
    /**
     * Halaman landing publik reservasi sarana kampus Undip Tembalang.
     */
    public function index()
    {
        // Ambil data fasilitas aktif yang dikelompokkan untuk kurasi unggulan
        $facilities = Facility::where('status', 'active')
            ->orderByRaw("FIELD(type, 'Aula', 'Laboratorium', 'Ruang Kelas', 'Lapangan', 'Alat')")
            ->orderBy('name')
            ->get();

        // Preview ketersediaan slot nyata untuk satu fasilitas representatif (Aula Utama / fasilitas aktif pertama)
        $previewFacility = Facility::where('name', 'Aula Utama')->first()
            ?? Facility::where('status', 'active')->first();

        $previewDate = now()->addDay()->toDateString();
        $previewSlots = $previewFacility ? Slot::availability($previewFacility, $previewDate) : [];

        $stats = [
            'total_facilities' => Facility::count(),
            'total_active' => Facility::where('status', 'active')->count(),
            'locations_count' => count(Facility::LOCATIONS),
        ];

        return view('welcome', compact('facilities', 'previewFacility', 'previewDate', 'previewSlots', 'stats'));
    }
}
