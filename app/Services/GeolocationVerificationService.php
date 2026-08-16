<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

/**
 * Service de vérification et de signature de la géolocalisation.
 *
 * Principe : /user/check-location valide la position et délivre un jeton
 * signé (HMAC) à usage unique, lié aux coordonnées et à un horodatage.
 * Les endpoints de pointage (mark-arrival / mark-departure) vérifient ce
 * jeton avant d'accepter la présence, ce qui empêche :
 *   - de soumettre des coordonnées forgées sans passer par le contrôle,
 *   - de rejouer une ancienne position (expiration + nonce à usage unique),
 *   - de falsifier l'horloge du client (écart contrôlé avec le serveur).
 */
class GeolocationVerificationService
{
    /** Clé secrète utilisée pour signer les jetons. */
    private function key(): string
    {
        return config('app.key');
    }

    /**
     * Vérifie la fraîcheur et la validité d'une signature HMAC.
     *
     * @param string $signature      jeton reçu du client
     * @param float  $latitude       latitude soumise
     * @param float  $longitude      longitude soumise
     * @param int    $clientTimestamp horodatage envoyé par le navigateur (secondes)
     * @return array{valid: bool, reason: ?string, skew: ?int}
     */
    public function verifySignature(
        string $signature,
        float $latitude,
        float $longitude,
        ?int $clientTimestamp = null
    ): array {
        $parts = explode('.', $signature);
        if (count($parts) !== 2) {
            return ['valid' => false, 'reason' => 'Signature invalide.', 'skew' => null];
        }

        [$payload, $providedHmac] = $parts;
        $decoded = json_decode(base64_decode($payload), true);

        if (!is_array($decoded) || !isset($decoded['ts'], $decoded['nonce'], $decoded['lat'], $decoded['lon'])) {
            return ['valid' => false, 'reason' => 'Signature invalide.', 'skew' => null];
        }

        $ttl = (int) config('geolocation.signature_ttl', 300);

        // 1. Fraîcheur : la signature doit être récente
        if (now()->timestamp - (int) $decoded['ts'] > $ttl) {
            return ['valid' => false, 'reason' => 'Signature expirée. Veuillez vérifier à nouveau votre position.', 'skew' => null];
        }

        // 2. Nonce à usage unique (empêche le rejeu d'un même jeton)
        $usedNonce = Session::get('geoloc_used_nonce');
        if ($usedNonce === $decoded['nonce']) {
            return ['valid' => false, 'reason' => 'Signature déjà utilisée. Veuillez vérifier à nouveau votre position.', 'skew' => null];
        }

        // 3. Intégrité HMAC : la signature doit correspondre aux données signées
        $expectedHmac = hash_hmac('sha256', $payload, $this->key());
        if (!hash_equals($expectedHmac, $providedHmac)) {
            return ['valid' => false, 'reason' => 'Signature altérée.', 'skew' => null];
        }

        // 4. Les coordonnées soumises doivent être celles qui ont été signées
        if (abs((float) $decoded['lat'] - $latitude) > 0.000001
            || abs((float) $decoded['lon'] - $longitude) > 0.000001) {
            return ['valid' => false, 'reason' => 'Coordonnées modifiées après validation.', 'skew' => null];
        }

        // 5. Écart d'horloge client / serveur
        $skew = null;
        if ($clientTimestamp !== null) {
            $maxSkew = (int) config('geolocation.max_clock_skew', 300);
            $skew = abs(now()->timestamp - $clientTimestamp);
            if ($skew > $maxSkew) {
                return ['valid' => false, 'reason' => 'Horloge de l\'appareil incohérente avec le serveur.', 'skew' => $skew];
            }
        }

        // Le nonce est consommé : il ne pourra pas être réutilisé
        Session::put('geoloc_used_nonce', $decoded['nonce']);

        return ['valid' => true, 'reason' => null, 'skew' => $skew];
    }

    /**
     * Crée une signature HMAC signée pour une position validée.
     *
     * @param float $latitude
     * @param float $longitude
     * @return string  signature au format "base64(payload).hmac"
     */
    public function createSignature(float $latitude, float $longitude): string
    {
        $payload = base64_encode(json_encode([
            'ts'    => now()->timestamp,
            'nonce' => bin2hex(random_bytes(16)),
            'lat'   => $latitude,
            'lon'   => $longitude,
        ]));

        return $payload . '.' . hash_hmac('sha256', $payload, $this->key());
    }

    /**
     * Distance en kilomètres entre deux points (formule de Haversine).
     */
    public function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371.0; // km

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($lonDelta / 2) ** 2;

        return 2 * $earthRadius * asin(sqrt($a));
    }

    /**
     * Vitesse moyenne en km/h entre deux pointages.
     */
    public function speedKmh(float $distanceKm, int $secondsElapsed): float
    {
        if ($secondsElapsed <= 0) {
            return 0.0;
        }

        return ($distanceKm / $secondsElapsed) * 3600;
    }
}
