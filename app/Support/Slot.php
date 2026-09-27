<?php

namespace App\Support;

use App\Models\Facility;
use App\Models\Reservation;
use Carbon\Carbon;
use DateTimeInterface;

/**
 * Konstanta slot waktu — F1.
 *
 * 26 slot per hari, masing-masing 30 menit, jam operasional 07.00–20.00.
 * Dipakai di tiga tempat: dropdown U1, grid P2, dan validasi StoreReservationRequest.
 * Satu sumber — perubahan di sini otomatis berlaku ke ketiganya.
 */
class Slot
{
    /** Jam buka operasional */
    const OPEN = '07:00';

    /** Jam tutup operasional */
    const CLOSE = '20:00';

    /**
     * 26 nilai jam mulai yang sah: 07:00, 07:30, …, 19:30.
     *
     * @return list<string>
     */
    public static function startTimes(): array
    {
        return self::generate('07:00', '19:30');
    }

    /**
     * 26 nilai jam selesai yang sah: 07:30, 08:00, …, 20:00.
     *
     * @return list<string>
     */
    public static function endTimes(): array
    {
        return self::generate('07:30', '20:00');
    }

    /**
     * Semua slot sebagai pasangan label → value untuk dropdown.
     * Contoh: ['07:00' => '07:00', '07:30' => '07:30', ...]
     *
     * @return array<string, string>
     */
    public static function startOptions(): array
    {
        return array_combine(self::startTimes(), self::startTimes());
    }

    /**
     * @return array<string, string>
     */
    public static function endOptions(): array
    {
        return array_combine(self::endTimes(), self::endTimes());
    }

    /**
     * @return array<string, array{start: string, end: string, status: string, is_available: bool, is_booked: bool, is_past_limit: bool}>
     */
    public static function availability(Facility|int $facility, string|DateTimeInterface $date): array
    {
        $facilityId = $facility instanceof Facility ? $facility->id : (int) $facility;
        $dateStr = $date instanceof DateTimeInterface ? $date->format('Y-m-d') : Carbon::parse($date)->toDateString();

        $minDate = Carbon::now()->addDay()->toDateString();
        $maxDate = Carbon::now()->addDays(30)->toDateString();
        $isDateBookable = ($dateStr >= $minDate && $dateStr <= $maxDate);

        $startOfDay = Carbon::createFromFormat('Y-m-d H:i:s', "{$dateStr} 00:00:00");
        $endOfDay = Carbon::createFromFormat('Y-m-d H:i:s', "{$dateStr} 23:59:59");

        $reservations = Reservation::query()
            ->select(['facility_id', 'start_time', 'end_time'])
            ->where('facility_id', $facilityId)
            ->where('status', 'approved')
            ->where('start_time', '<', $endOfDay)
            ->where('end_time', '>', $startOfDay)
            ->get();

        $startTimes = self::startTimes();
        $endTimes = self::endTimes();
        $slots = [];

        for ($i = 0; $i < count($startTimes); $i++) {
            $slotStart = $startTimes[$i];
            $slotEnd = $endTimes[$i];

            $slotStartCarbon = Carbon::createFromFormat('Y-m-d H:i', "{$dateStr} {$slotStart}");
            $slotEndCarbon = Carbon::createFromFormat('Y-m-d H:i', "{$dateStr} {$slotEnd}");

            $isBooked = $reservations->contains(function ($res) use ($slotStartCarbon, $slotEndCarbon) {
                return $res->start_time < $slotEndCarbon && $res->end_time > $slotStartCarbon;
            });

            if ($isBooked) {
                $status = 'booked';
            } elseif (! $isDateBookable) {
                $status = 'past_limit';
            } else {
                $status = 'available';
            }

            $slots[$slotStart] = [
                'start' => $slotStart,
                'end' => $slotEnd,
                'status' => $status,
                'is_available' => $status === 'available',
                'is_booked' => $status === 'booked',
                'is_past_limit' => $status === 'past_limit',
            ];
        }

        return $slots;
    }

    /**
     * Hasilkan daftar string jam "HH:MM" dari $from sampai $to, step 30 menit.
     *
     * @return list<string>
     */
    private static function generate(string $from, string $to): array
    {
        $slots = [];
        $current = strtotime("1970-01-01 {$from}");
        $end = strtotime("1970-01-01 {$to}");

        while ($current <= $end) {
            $slots[] = date('H:i', $current);
            $current += 30 * 60;
        }

        return $slots;
    }
}
