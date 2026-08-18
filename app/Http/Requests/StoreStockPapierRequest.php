<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockPapierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'imprimante' => 'required|string|max:255',
            'metres_restants' => 'required|numeric|min:0|max:99999',
            'metres_total' => 'required|numeric|min:0|max:99999',
            'seuil_alerte' => 'nullable|numeric|min:0|max:99999',
        ];
    }
}
