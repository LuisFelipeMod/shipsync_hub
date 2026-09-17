<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShippingQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'origin' => ['required', 'string'],
            'destination' => ['required', 'string'],
            'declared_value_cents' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'packages' => ['required', 'array', 'min:1'],
            'packages.*.weight_grams' => ['required', 'integer', 'min:1'],
            'packages.*.length_cm' => ['required', 'integer', 'min:1'],
            'packages.*.width_cm' => ['required', 'integer', 'min:1'],
            'packages.*.height_cm' => ['required', 'integer', 'min:1'],
        ];
    }
}
