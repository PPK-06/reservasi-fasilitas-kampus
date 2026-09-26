<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Support\Slot;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    /* P1 – Daftar Fasilitas (publik) */
    public function index(Request $request)
    {
        $query = Facility::query();

        // ── Filter: tipe (F6 — nilai dari konstanta model) ──
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // ── Filter: lokasi (F6 — idem) ──
        if ($request->filled('location')) {
            $query->where('location', $request->input('location'));
        }

        // ── Filter: kapasitas minimal (A4) ──
        // Hanya diterapkan pada baris yang capacity IS NOT NULL.
        // Fasilitas bertipe 'Alat' memiliki capacity NULL, jadi otomatis
        // tidak terpengaruh — tetap muncul di hasil pencarian.
        if ($request->filled('capacity')) {
            $minCapacity = (int) $request->input('capacity');
            $query->where(function ($q) use ($minCapacity) {
                $q->where('capacity', '>=', $minCapacity)
                    ->orWhereNull('capacity');
            });
        }

        // ── Filter: pencarian nama (bonus convenience) ──
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->input('search').'%');
        }

        // Urutan: aktif di atas, lalu by name (D5 — non-aktif tetap muncul)
        $facilities = $query
            ->orderByRaw("FIELD(status, 'active', 'under_maintenance', 'inactive')")
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('facilities.index', compact('facilities'));
    }

    /* P2 – Detail Fasilitas (publik) */
    public function show(Request $request, Facility $facility)
    {
        $dateInput = $request->query('date');
        try {
            $selectedDate = $dateInput ? Carbon::parse($dateInput)->toDateString() : now()->addDay()->toDateString();
        } catch (\Throwable $e) {
            $selectedDate = now()->addDay()->toDateString();
        }

        $slots = Slot::availability($facility, $selectedDate);

        return view('facilities.show', compact('facility', 'selectedDate', 'slots'));
    }
}
