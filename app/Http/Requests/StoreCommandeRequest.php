<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommandeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $types = ['impression', 'photocopie', 'papeterie', 'scan', 'plastification', 'shooting', 'montage_photos', 'montage_agrandissement', 'agrandissement_photos', 'demi_carte_photo'];

        return [
            'type' => 'required|string|in:' . implode(',', $types),
            'montant' => 'required|numeric|min:0.01|max:99999999',
            'details' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Le type de commande est requis.',
            'type.in' => 'Ce type de commande n\'est pas valide.',
            'montant.required' => 'Le montant est requis.',
            'montant.min' => 'Le montant doit être supérieur à 0.',
        ];
    }
}
