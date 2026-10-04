<?php

namespace Database\Factories;

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = now()->addDays(2)->setTime(9, 0, 0);

        return [
            'user_id' => User::factory(),
            'facility_id' => Facility::factory(),
            'start_time' => $start,
            'end_time' => (clone $start)->addHours(2),
            'purpose' => fake()->sentence(),
            'status' => 'pending',
            'status_reason' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'status_reason' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'status_reason' => null,
        ]);
    }

    public function rejected(?string $reason = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'status_reason' => $reason ?? 'Alasan penolakan reservasi.',
        ]);
    }

    public function cancelledByUser(?string $reason = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled_by_user',
            'status_reason' => $reason,
        ]);
    }

    public function cancelledByOfficer(?string $reason = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled_by_officer',
            'status_reason' => $reason ?? 'Alasan pembatalan oleh petugas.',
        ]);
    }
}
