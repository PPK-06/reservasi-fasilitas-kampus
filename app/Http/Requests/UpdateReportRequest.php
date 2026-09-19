<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in([
                    'baru',
                    'diproses',
                    'selesai',
                    'ditolak',
                ]),
            ],

            'resolution_note' => [
                'required_if:status,selesai,ditolak',
                'nullable',
                'string',
                'min:10',
                'max:1000',
            ],
        ];
    }
}
