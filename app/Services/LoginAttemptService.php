<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Service de gestion des tentatives de connexion.
 *
 * - Compteur d'échecs par email (cache file/redis)
 * - Lockout 15 minutes après 10 échecs consécutifs
 * - Reset sur login réussi
 *
 * Clé cache : login_attempts:{email}
 */
class LoginAttemptService
{
    /** Nombre max d'échecs avant lockout */
    private const MAX_ATTEMPTS = 10;

    /** Durée du lockout en minutes */
    private const LOCKOUT_MINUTES = 15;

    /** Durée de rétention du compteur (pour auto-nettoyage) */
    private const TTL_MINUTES = 30;

    /**
     * Vérifie si l'email est actuellement lockout.
     */
    public function isLockedOut(string $email): bool
    {
        $key = $this->cacheKey($email);
        $attempts = Cache::get($key, 0);

        return $attempts >= self::MAX_ATTEMPTS;
    }

    /**
     * Retourne le nombre de secondes restantes avant déblocage.
     * Retourne 0 si pas lockout.
     */
    public function secondsRemaining(string $email): int
    {
        $key = $this->cacheKey($email);
        $ttl = Cache::store('file')->ttl($key);

        if ($ttl === false || $ttl <= 0) {
            return 0;
        }

        // Le TTL restant est approximatif
        return max(0, $ttl);
    }

    /**
     * Enregistre un échec de connexion pour cet email.
     * Retourne le nombre total d'échecs après incrémentation.
     */
    public function recordFailure(string $email): int
    {
        $key = $this->cacheKey($email);
        $attempts = Cache::get($key, 0) + 1;

        // Stocker avec TTL pour auto-nettoyage
        Cache::put($key, $attempts, now()->addMinutes(self::TTL_MINUTES));

        return $attempts;
    }

    /**
     * Réinitialise le compteur après un login réussi.
     */
    public function reset(string $email): void
    {
        Cache::forget($this->cacheKey($email));
    }

    /**
     * Nombre d'échecs actuels pour cet email.
     */
    public function getAttempts(string $email): int
    {
        return Cache::get($this->cacheKey($email), 0);
    }

    /**
     * Nombre max d'autorisations.
     */
    public function maxAttempts(): int
    {
        return self::MAX_ATTEMPTS;
    }

    /**
     * Durée de lockout en minutes.
     */
    public function lockoutMinutes(): int
    {
        return self::LOCKOUT_MINUTES;
    }

    /**
     * Message d'erreur formaté pour le lockout.
     */
    public function lockoutMessage(string $email): string
    {
        $remaining = $this->secondsRemaining($email);
        $minutes = ceil($remaining / 60);

        return "Compte temporairement bloqué après 10 tentatives échouées. "
             . "Réessayez dans {$minutes} minute(s).";
    }

    private function cacheKey(string $email): string
    {
        return 'login_attempts:' . strtolower(trim($email));
    }
}
