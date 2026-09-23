<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Aturan 5 F1: end_slot harus lebih besar dari start_slot.
 * Dipasang pada field end_slot di StoreReservationRequest.
 */
class EndSlotAfterStart implements ValidationRule
{
    public function __construct(private readonly ?string $startSlot)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->startSlot === null || $value === null) {
            return;
        }

        if ($value <= $this->startSlot) {
            $fail('Jam selesai harus lebih besar dari jam mulai.');
        }
    }
}
