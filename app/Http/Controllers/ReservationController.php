<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Models\Facility;
use App\Models\Reservation;
use App\Support\Slot;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * U1, U2, U3 — Form ajukan, riwayat, dan detail reservasi (pengguna).
 */
class ReservationController extends Controller
{
    /**
     * U1 — Tampilkan form ajukan reservasi.
     * Menerima ?facility=3 saat datang dari P2 (facility_id ter-prefill).
     */
    public function create(Request $request): View
    {
        $facilities = Facility::where('status', 'active')->orderBy('name')->get();
        $selectedFacility = $request->query('facility')
            ? Facility::where('status', 'active')->find($request->query('facility'))
            : null;

        $startSlots = Slot::startTimes();
        $endSlots   = Slot::endTimes();

        return view('reservations.create', compact('facilities', 'selectedFacility', 'startSlots', 'endSlots'));
    }

    /**
     * U1 — Simpan reservasi baru.
     * start_time dan end_time disusun dari date + slot (F1).
     */
    public function store(StoreReservationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $date  = $validated['date'];
        $start = Carbon::createFromFormat('Y-m-d H:i', "{$date} {$validated['start_slot']}");
        $end   = Carbon::createFromFormat('Y-m-d H:i', "{$date} {$validated['end_slot']}");

        $reservation = new Reservation([
            'facility_id' => $validated['facility_id'],
            'purpose'     => $validated['purpose'],
        ]);
        $reservation->user_id    = auth()->id();
        $reservation->start_time = $start;
        $reservation->end_time   = $end;
        $reservation->status     = 'pending';
        $reservation->save();

        return redirect()->route('reservations.show', $reservation)
            ->with('success', 'Reservasi berhasil diajukan dan sedang menunggu persetujuan.');
    }

    /**
     * U2 — Riwayat reservasi milik pengguna yang sedang login.
     */
    public function index(): View
    {
        $reservations = auth()->user()
            ->reservations()
            ->with('facility')
            ->orderByDesc('start_time')
            ->paginate(15);

        return view('reservations.index', compact('reservations'));
    }

    /**
     * U3 — Detail satu reservasi (dijaga ReservationPolicy::view).
     */
    public function show(Reservation $reservation): View
    {
        $this->authorize('view', $reservation);

        $reservation->load('facility', 'user');

        return view('reservations.show', compact('reservation'));
    }

    /**
     * U3 — Batalkan reservasi (A1: pending bebas, approved hanya sebelum hari-H).
     */
    public function cancel(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->authorize('cancel', $reservation);

        $request->validate([
            'status_reason' => ['nullable', 'string', 'max:500'],
        ]);

        if ($reservation->status === 'approved') {
            if (now()->toDateString() >= Carbon::parse($reservation->start_time)->toDateString()) {
                return back()->with('error', 'Sudah lewat batas pembatalan mandiri, hubungi petugas.');
            }
        } elseif ($reservation->status !== 'pending') {
            return back()->with('error', 'Reservasi ini tidak dapat dibatalkan.');
        }

        $reservation->status = 'cancelled_by_user';
        $reservation->status_reason = $request->input('status_reason');
        $reservation->save();

        return redirect()->route('reservations.show', $reservation)
            ->with('success', 'Reservasi berhasil dibatalkan.');
    }
}
