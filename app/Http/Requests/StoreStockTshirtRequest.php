<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockTshirtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'couleur' => 'required|string|max:100',
            'taille' => 'required|string|max:20',
            'quantite' => 'required|integer|min:0|max:99999',
            'seuil_alerte' => 'nullable|integer|min:0|max:9999',
        ];
    }
}
