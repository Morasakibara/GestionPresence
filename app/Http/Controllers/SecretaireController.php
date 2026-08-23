<?php

namespace App\Http\Controllers;

/**
 * Secrétaire — superviseur spécialisé shooting/montage photos.
 * Toute la logique CRUD est dans AbstractCaisseController.
 */
class SecretaireController extends AbstractCaisseController
{
    protected function module(): string
    {
        return 'secretaire';
    }

    protected function types(): array
    {
        return [
            'shooting'                 => 'Shooting',
            'montage_photos'           => 'Montage photos',
            'montage_agrandissement'   => 'Montage & agrandissement photos',
            'agrandissement_photos'    => 'Agrandissement photos',
            'demi_carte_photo'         => 'Demi-carte photo',
        ];
    }

    protected function viewPrefix(): string
    {
        return 'secretaire';
    }

    /**
     * La secrétaire utilise 'typesPhoto' comme clé de vue pour les types.
     */
    protected function typesViewKey(): string
    {
        return 'typesPhoto';
    }
}
