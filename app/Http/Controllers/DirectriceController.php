<?php

namespace App\Http\Controllers;

/**
 * Directrice — superviseur spécialisé impression/photocopie/papeterie/scan/plastification.
 * Toute la logique CRUD est dans AbstractCaisseController.
 */
class DirectriceController extends AbstractCaisseController
{
    protected function module(): string
    {
        return 'directrice';
    }

    protected function types(): array
    {
        return [
            'impression'     => 'Impressions',
            'photocopie'     => 'Photocopies',
            'papeterie'      => 'Papeteries',
            'scan'           => 'Scan',
            'plastification' => 'Plastification',
        ];
    }

    protected function viewPrefix(): string
    {
        return 'directrice';
    }
}
