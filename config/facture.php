<?php

/*
|--------------------------------------------------------------------------
| Configuration Facture — Le Pharaon
|--------------------------------------------------------------------------
| Modèle visuel du PDF (Étape 3.1) et règles du flux acompte / solde (Étape 2).
*/

return [
    'societe' => [
        'nom'     => 'Le Pharaon',
        'adresse' => 'Salle de jeux Le Pharaon',
        'phone'   => '+2376XXXXXXX',
    ],

    'devise' => 'FCFA',

    // Numérotation séquentielle annuelle : FAC-2026-00001
    'numerotation' => [
        'prefixe'  => 'FAC-',
        'longueur' => 5,
    ],

    // Validation stricte du numéro WhatsApp (format international E.164, ex: +2376XXXXXXX)
    'whatsapp_regex' => '/^\+[1-9]\d{7,14}$/',
];
