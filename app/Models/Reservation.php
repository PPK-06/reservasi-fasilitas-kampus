<?php

namespace App\Models;

use App\Support\Slot;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['facility_id', 'start_time', 'end_time', 'purpose'])]
class Reservation extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * @return array<string, array{start: string, end: string, status: string, is_available: bool, is_booked: bool, is_past_limit: bool}>
     */
    public static function availability(Facility|int $facility, string|DateTimeInterface $date): array
    {
        return Slot::availability($facility, $date);
    }
}
