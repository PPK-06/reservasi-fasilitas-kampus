<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Report;
use Illuminate\View\View;

/**
 * O1 — Dashboard Antrian petugas.
 * Dua panel: reservasi pending yang belum lewat, dan laporan belum selesai.
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        $pendingReservations = Reservation::with('facility', 'user')
            ->where('status', 'pending')
            ->where('start_time', '>', now())
            ->orderBy('start_time')
            ->limit(10)
            ->get();

        $pendingCount = Reservation::where('status', 'pending')
            ->where('start_time', '>', now())
            ->count();

        $pendingReports = Report::with('facility', 'user')
            ->whereIn('status', ['baru', 'diproses'])
            ->orderBy('created_at')
            ->limit(10)
            ->get();

        $pendingReportCount = Report::whereIn('status', ['baru', 'diproses'])->count();

        return view('officer.dashboard', compact(
            'pendingReservations',
            'pendingCount',
            'pendingReports',
            'pendingReportCount',
        ));
    }
}
