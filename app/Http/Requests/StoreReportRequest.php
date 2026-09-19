<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'facility_id' => [
                'required',
                'exists:facilities,id',
            ],

            'category' => [
                'required',
                Rule::in([
                    'kerusakan_alat',
                    'kelistrikan',
                    'pendingin_ruangan',
                    'furnitur',
                    'kebersihan',
                    'lainnya',
                ]),
            ],

            'description' => [
                'required',
                'string',
                'min:10',
                'max:1000',
            ],

            'photos' => [
                'required',
                'array',
                'min:1',
                'max:3',
            ],

            'photos.*' => [
                'image',
                'mimes:jpg,jpeg,png',
                'max:3072',
            ],
        ];
    }
}
