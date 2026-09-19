<?php

namespace App\Http\Requests;

use App\Models\Facility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFacilityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:100'],
            'type'        => ['required', Rule::in(array_keys(Facility::TYPES))],
            'location'    => ['required', Rule::in(array_keys(Facility::LOCATIONS))],
            'capacity'    => ['nullable', 'integer', 'min:1', 'max:65535'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status'      => ['required', Rule::in(array_keys(Facility::STATUSES))],
        ];
    }
}
