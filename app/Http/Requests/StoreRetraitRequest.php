<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRetraitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'montant' => 'required|numeric|min:0.01|max:99999999',
            'motif' => 'required|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'motif.required' => 'Le motif du retrait est obligatoire.',
            'montant.min' => 'Le montant doit être supérieur à 0.',
        ];
    }
}
