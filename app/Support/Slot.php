<?php

namespace App\Support;

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
    const OPEN  = '07:00';

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
     * Hasilkan daftar string jam "HH:MM" dari $from sampai $to, step 30 menit.
     *
     * @return list<string>
     */
    private static function generate(string $from, string $to): array
    {
        $slots   = [];
        $current = strtotime("1970-01-01 {$from}");
        $end     = strtotime("1970-01-01 {$to}");

        while ($current <= $end) {
            $slots[] = date('H:i', $current);
            $current += 30 * 60;
        }

        return $slots;
    }
}
