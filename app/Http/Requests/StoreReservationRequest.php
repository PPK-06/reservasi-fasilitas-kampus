<?php

namespace App\Http\Requests;

use App\Rules\EndSlotAfterStart;
use App\Support\Slot;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Kontrak form U1 — dokumen route bagian 17.
 *
 * Field yang dikirim: facility_id, date, start_slot, end_slot, purpose.
 * Controller menyusun start_time dan end_time dari date + slot sebelum simpan.
 */
class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<ValidationRule|string|object>>
     */
    public function rules(): array
    {
        return [
            'facility_id' => [
                'required',
                Rule::exists('facilities', 'id')->where('status', 'active'),
            ],
            'date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:' . now()->addDay()->toDateString(),
                'before_or_equal:' . now()->addDays(30)->toDateString(),
            ],
            'start_slot' => ['required', Rule::in(Slot::startTimes())],
            'end_slot'   => [
                'required',
                Rule::in(Slot::endTimes()),
                new EndSlotAfterStart($this->input('start_slot')),
            ],
            'purpose' => ['required', 'string', 'min:5', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'facility_id.required' => 'Pilih fasilitas terlebih dahulu.',
            'facility_id.exists'   => 'Fasilitas ini sedang tidak menerima reservasi.',
            'date.required'        => 'Tanggal penggunaan wajib diisi.',
            'date.date_format'     => 'Format tanggal tidak valid.',
            'date.after_or_equal'  => 'Reservasi paling cepat untuk besok.',
            'date.before_or_equal' => 'Reservasi paling jauh 30 hari ke depan.',
            'start_slot.required'  => 'Jam mulai wajib dipilih.',
            'start_slot.in'        => 'Jam mulai harus kelipatan 30 menit dalam jam operasional 07.00–20.00.',
            'end_slot.required'    => 'Jam selesai wajib dipilih.',
            'end_slot.in'          => 'Jam selesai harus kelipatan 30 menit dalam jam operasional 07.00–20.00.',
            'purpose.required'     => 'Tujuan penggunaan wajib diisi.',
            'purpose.min'          => 'Tujuan penggunaan minimal 5 karakter.',
            'purpose.max'          => 'Tujuan penggunaan maksimal 255 karakter.',
        ];
    }
}
