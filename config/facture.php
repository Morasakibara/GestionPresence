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

    // Montant minimal pour générer une facture (PDF + WhatsApp).
    // En dessous ou égal, la vente est simplement enregistrée sans facture.
    'montant_minimum' => 1000,

    // Pourcentage minimal d'acompte exigé sur une COMMANDES facturée
    // (uniquement les commandes — les services ne prennent pas d'acompte).
    'acompte_minimum_pct' => 35,

    // Préfixe téléphonique ajouté automatiquement quand l'oubli du pays est probable.
    'prefixe_defaut' => '+237',

    // Numérotation séquentielle annuelle : FAC-2026-00001 (commandes), SVC-2026-00001 (services)
    'numerotation' => [
        'prefixe'         => 'FAC-',
        'prefixe_service' => 'SVC-',
        'longueur'        => 5,
    ],

    // Validation stricte du numéro WhatsApp (format international E.164, ex: +2376XXXXXXX)
    'whatsapp_regex' => '/^\+[1-9]\d{7,14}$/',
];
