<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecapRequest;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RecapController extends Controller
{
    public function index(Request $request): View
    {
        $hasFilter = $request->hasAny([
            'start_date',
            'end_date',
        ]);

        if (! $hasFilter) {
            return view('admin.recap.index', [
                'hasFilter' => false,
                'startDate' => null,
                'endDate' => null,
                'dayCount' => null,
                'missedReservations' => null,
                'occupancyRows' => collect(),
                'damageRows' => collect(),
            ]);
        }

        $validated = validator(
            $request->query(),
            RecapRequest::ruleset()
        )->validate();

        $recap = $this->buildRecap(
            $validated['start_date'],
            $validated['end_date']
        );

        return view('admin.recap.index', array_merge(
            [
                'hasFilter' => true,
                'startDate' => $validated['start_date'],
                'endDate' => $validated['end_date'],
            ],
            $recap
        ));
    }


    public function export(
        RecapRequest $request
    ): StreamedResponse {
        $validated = $request->validated();

        $recap = $this->buildRecap(
            $validated['start_date'],
            $validated['end_date']
        );

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];

        $fileName = sprintf(
            'rekap-fasilitas-%s-sampai-%s.csv',
            $startDate,
            $endDate
        );

        return response()->streamDownload(
            function () use (
                $recap,
                $startDate,
                $endDate
            ) {
                $output = fopen('php://output', 'w');

                // UTF-8 BOM agar karakter tampil baik saat dibuka di Excel.
                fwrite($output, "\xEF\xBB\xBF");

                fputcsv($output, [
                    'REKAP FASILITAS KAMPUS',
                ]);

                fputcsv($output, [
                    'Periode',
                    $startDate.' s.d. '.$endDate,
                ]);

                fputcsv($output, [
                    'Jumlah hari',
                    $recap['dayCount'],
                ]);

                fputcsv($output, [
                    'Reservasi tidak sempat diproses',
                    $recap['missedReservations'],
                ]);

                fputcsv($output, []);


                // =========================
                // TABEL OKUPANSI
                // =========================

                fputcsv($output, [
                    'OKUPANSI FASILITAS',
                ]);

                fputcsv($output, [
                    'Fasilitas',
                    'Lokasi',
                    'Reservasi Approved',
                    'Slot Terpakai',
                    'Total Slot Periode',
                    'Okupansi (%)',
                ]);

                foreach ($recap['occupancyRows'] as $row) {
                    fputcsv($output, [
                        $row->name,
                        $row->location,
                        $row->reservation_count,
                        $row->used_slots,
                        $row->total_slots,
                        number_format(
                            $row->occupancy_percentage,
                            2,
                            '.',
                            ''
                        ),
                    ]);
                }

                fputcsv($output, []);


                // =========================
                // FREKUENSI KERUSAKAN
                // =========================

                fputcsv($output, [
                    'FREKUENSI KERUSAKAN',
                ]);

                fputcsv($output, [
                    'Fasilitas',
                    'Lokasi',
                    'Jumlah Laporan',
                ]);

                foreach ($recap['damageRows'] as $row) {
                    fputcsv($output, [
                        $row->name,
                        $row->location,
                        $row->report_count,
                    ]);
                }

                fclose($output);
            },
            $fileName,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]
        );
    }


    private function buildRecap(
        string $startDate,
        string $endDate
    ): array {
        $start = CarbonImmutable::parse($startDate)
            ->startOfDay();

        $end = CarbonImmutable::parse($endDate)
            ->endOfDay();

        $dayCount = (int) CarbonImmutable::parse($startDate)
            ->diffInDays(
                CarbonImmutable::parse($endDate)
            ) + 1;

        /*
         * E2:
         *
         * okupansi (%) =
         * slot terpakai /
         * (26 * jumlah hari periode)
         * * 100
         *
         * Hanya reservation berstatus approved.
         */
        $occupancyStats = DB::table('reservations')
            ->select(
                'facility_id',
                DB::raw(
                    'COUNT(*) AS reservation_count'
                ),
                DB::raw(
                    'SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time)) / 30 AS used_slots'
                )
            )
            ->where('status', 'approved')
            ->whereBetween(
                'start_time',
                [
                    $start->toDateTimeString(),
                    $end->toDateTimeString(),
                ]
            )
            ->groupBy('facility_id')
            ->get()
            ->keyBy('facility_id');


        /*
         * E3:
         * Semua report kecuali status ditolak.
         *
         * Periode menggunakan created_at sebagai
         * waktu laporan dibuat.
         */
        $damageStats = DB::table('reports')
            ->select(
                'facility_id',
                DB::raw(
                    'COUNT(*) AS report_count'
                )
            )
            ->where('status', '!=', 'ditolak')
            ->whereBetween(
                'created_at',
                [
                    $start->toDateTimeString(),
                    $end->toDateTimeString(),
                ]
            )
            ->groupBy('facility_id')
            ->get()
            ->keyBy('facility_id');


        /*
         * E5:
         *
         * status pending
         * start_time sudah lewat
         * DATE(start_time) ada dalam periode.
         */
        $missedReservations = DB::table('reservations')
            ->where('status', 'pending')
            ->where(
                'start_time',
                '<',
                now()
            )
            ->whereBetween(
                'start_time',
                [
                    $start->toDateTimeString(),
                    $end->toDateTimeString(),
                ]
            )
            ->count();


        $facilities = DB::table('facilities')
            ->select([
                'id',
                'name',
                'location',
            ])
            ->orderBy('name')
            ->get();


        $totalSlots = 26 * $dayCount;


        $occupancyRows = $facilities
            ->map(
                function ($facility) use (
                    $occupancyStats,
                    $totalSlots
                ) {
                    $stat = $occupancyStats
                        ->get($facility->id);

                    $reservationCount = $stat
                        ? (int) $stat->reservation_count
                        : 0;

                    $usedSlots = $stat
                        ? (float) $stat->used_slots
                        : 0;

                    $occupancyPercentage =
                        $totalSlots > 0
                            ? round(
                                ($usedSlots / $totalSlots) * 100,
                                2
                            )
                            : 0;

                    return (object) [
                        'facility_id' => $facility->id,
                        'name' => $facility->name,
                        'location' => $facility->location,
                        'reservation_count' => $reservationCount,
                        'used_slots' => $usedSlots,
                        'total_slots' => $totalSlots,
                        'occupancy_percentage' => $occupancyPercentage,
                    ];
                }
            );


        $damageRows = $facilities
            ->map(
                function ($facility) use (
                    $damageStats
                ) {
                    $stat = $damageStats
                        ->get($facility->id);

                    return (object) [
                        'facility_id' => $facility->id,
                        'name' => $facility->name,
                        'location' => $facility->location,
                        'report_count' => $stat
                            ? (int) $stat->report_count
                            : 0,
                    ];
                }
            )
            ->sortBy([
                [
                    'report_count',
                    'desc',
                ],
                [
                    'name',
                    'asc',
                ],
            ])
            ->values();


        return [
            'dayCount' => $dayCount,
            'missedReservations' => $missedReservations,
            'occupancyRows' => $occupancyRows,
            'damageRows' => $damageRows,
        ];
    }
}
