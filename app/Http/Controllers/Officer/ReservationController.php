<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * O2 & O3 — Antrian dan detail reservasi (petugas).
 */
class ReservationController extends Controller
{
    /**
     * O2 — Daftar reservasi dengan tab Menunggu / Terlewat / Semua.
     */
    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'menunggu');

        $query = Reservation::with('facility', 'user')->orderBy('start_time');

        if ($tab === 'menunggu') {
            $query->where('status', 'pending')->where('start_time', '>', now());
        } elseif ($tab === 'terlewat') {
            $query->where('status', 'pending')->where('start_time', '<=', now());
        }

        $reservations = $query->paginate(20)->withQueryString();

        return view('officer.reservations.index', compact('reservations', 'tab'));
    }

    /**
     * O3 — Detail satu reservasi.
     */
    public function show(Reservation $reservation): View
    {
        $reservation->load('facility', 'user');

        return view('officer.reservations.show', compact('reservation'));
    }

    /**
     * O3 — Setujui reservasi (F2: transaksi + lockForUpdate).
     * Server menolak approve untuk pending yang start_time sudah lewat (A8).
     */
    public function approve(Reservation $reservation): RedirectResponse
    {
        if ($reservation->status !== 'pending') {
            return back()->with('error', 'Hanya reservasi berstatus menunggu yang dapat disetujui.');
        }

        if ($reservation->start_time->lte(now())) {
            return back()->with('error', 'Reservasi ini sudah terlewat dan tidak dapat disetujui.');
        }

        $conflictFacilityName = null;

        try {
            DB::transaction(function () use ($reservation, &$conflictFacilityName) {
                $facility = \App\Models\Facility::whereKey($reservation->facility_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($facility->status !== 'active') {
                    $conflictFacilityName = $facility->name;
                    throw new \RuntimeException('facility_inactive');
                }

                $conflict = Reservation::where('facility_id', $facility->id)
                    ->where('status', 'approved')
                    ->where('start_time', '<', $reservation->end_time)
                    ->where('end_time', '>', $reservation->start_time)
                    ->exists();

                if ($conflict) {
                    throw new \RuntimeException('conflict');
                }

                $reservation->status = 'approved';
                $reservation->status_reason = null;
                $reservation->save();
            });
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'conflict') {
                return back()->with('error', 'Gagal menyetujui: jadwal bertumpuk dengan reservasi lain yang baru saja disetujui.');
            }

            return back()->with('error', 'Gagal menyetujui: fasilitas sedang tidak aktif.');
        }

        return redirect()->route('officer.reservations.index')
            ->with('success', 'Reservasi disetujui.');
    }

    /**
     * O3 — Tolak reservasi (A7: status_reason wajib min 10 karakter).
     * Server menolak reject untuk pending yang start_time sudah lewat (A8).
     */
    public function reject(Request $request, Reservation $reservation): RedirectResponse
    {
        if ($reservation->status !== 'pending') {
            return back()->with('error', 'Hanya reservasi berstatus menunggu yang dapat ditolak.');
        }

        if ($reservation->start_time->lte(now())) {
            return back()->with('error', 'Reservasi ini sudah terlewat dan tidak dapat ditolak.');
        }

        $request->validate([
            'status_reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        $reservation->status = 'rejected';
        $reservation->status_reason = $request->input('status_reason');
        $reservation->save();

        return redirect()->route('officer.reservations.index')
            ->with('success', 'Reservasi ditolak.');
    }

    /**
     * O3 — Batalkan reservasi approved (A7, B5: status_reason wajib min 10 karakter).
     */
    public function cancel(Request $request, Reservation $reservation): RedirectResponse
    {
        if ($reservation->status !== 'approved') {
            return back()->with('error', 'Hanya reservasi berstatus disetujui yang dapat dibatalkan petugas.');
        }

        $request->validate([
            'status_reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        $reservation->status = 'cancelled_by_officer';
        $reservation->status_reason = $request->input('status_reason');
        $reservation->save();

        return redirect()->route('officer.reservations.index')
            ->with('success', 'Reservasi dibatalkan.');
    }
}
