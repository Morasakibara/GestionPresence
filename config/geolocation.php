<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Durée de validité d'une signature de géolocalisation (secondes)
    |--------------------------------------------------------------------------
    | La signature délivrée par /user/check-location doit être utilisée dans
    | ce délai. Passé ce délai, elle est rejetée et l'utilisateur doit
    | refaire valider sa position.
    */
    'signature_ttl' => env('GEOLOC_SIGNATURE_TTL', 300),

    /*
    |--------------------------------------------------------------------------
    | Tolérance d'écart d'horloge client/serveur (secondes)
    |--------------------------------------------------------------------------
    | Le navigateur envoie son horodatage. Si l'écart avec l'heure du serveur
    | dépasse cette valeur, la requête est rejetée (horloge de l'appareil
    | modifiée ou tentative de falsification).
    */
    'max_clock_skew' => env('GEOLOC_MAX_CLOCK_SKEW', 300),

    /*
    |--------------------------------------------------------------------------
    | Vitesse maximale de déplacement raisonnable (km/h)
    |--------------------------------------------------------------------------
    | Distance Haversine entre le point d'arrivée et le point de départ,
    | divisée par le temps écoulé. Au-delà, la présence est marquée suspecte
    | (ex. arrivée pointée à Paris et départ à Lyon le même jour).
    */
    'max_speed_kmh' => env('GEOLOC_MAX_SPEED_KMH', 40),

    /*
    |--------------------------------------------------------------------------
    | Précision GPS maximale raisonnable (mètres)
    |--------------------------------------------------------------------------
    | Si la précision déclarée par le navigateur dépasse cette valeur (ou plus
    | du double du rayon du lieu), la présence est marquée suspecte : une
    | position très imprécise ne permet pas de prouver la présence physique.
    */
    'max_accuracy_m' => env('GEOLOC_MAX_ACCURACY_M', 300),

    /*
    |--------------------------------------------------------------------------
    | Rappel des présences suspectes non traitées (jours)
    |--------------------------------------------------------------------------
    | La commande planifiée presence:rappel-suspectes notifie l'administrateur
    | des présences marquées suspectes dont le statut de traitement est encore
    | "nouveau" depuis plus de ce nombre de jours.
    */
    'rappel_suspectes_jours' => env('GEOLOC_RAPPEL_SUSPECTES_JOURS', 7),

];
