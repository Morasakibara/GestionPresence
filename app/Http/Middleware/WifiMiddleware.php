<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WifiMiddleware
{
    protected $allowedWifiSsid = 'actif';
    protected $allowedIpRange = '192.168.1.0/24'; // Exemple de plage IP du réseau autorisé

    public function handle(Request $request, Closure $next)
    {
        if (!$this->isConnectedToAllowedWifi()) {
            Log::warning('Tentative d\'accès non autorisée depuis l\'IP : ' . $request->ip());
            return response('Accès non autorisé. Veuillez vous connecter au réseau WiFi de l\'entreprise.', 403);
        }

        return $next($request);
    }

    private function isConnectedToAllowedWifi()
    {
        // Vérification de l'adresse IP
        if (!$this->isInAllowedIpRange()) {
            return false;
        }

        // Vérification du certificat client
        if (!$this->hasValidClientCertificate()) {
            return false;
        }

        // Vérification de l'en-tête personnalisé
        if (!$this->hasValidCustomHeader()) {
            return false;
        }

        // Si toutes les vérifications sont passées, on considère que l'utilisateur est sur le bon réseau
        return true;
    }

    private function isInAllowedIpRange()
    {
        $clientIp = request()->ip();
        list($subnet, $bits) = explode('/', $this->allowedIpRange);
        $ip = ip2long($clientIp);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;
        return ($ip & $mask) == $subnet;
    }

    private function hasValidClientCertificate()
    {
        // Vérification du certificat client SSL
        // Note: Ceci nécessite une configuration côté serveur pour demander et valider les certificats clients
        if (!isset($_SERVER['SSL_CLIENT_VERIFY']) || $_SERVER['SSL_CLIENT_VERIFY'] !== 'SUCCESS') {
            return false;
        }

        // Vérifiez également le nom commun du certificat si nécessaire
        // if ($_SERVER['SSL_CLIENT_S_DN_CN'] !== 'NomAttendu') {
        //     return false;
        // }

        return true;
    }

    private function hasValidCustomHeader()
    {
        // Vérification d'un en-tête HTTP personnalisé
        // Cet en-tête pourrait être défini par un proxy d'entreprise ou une application cliente
        $customHeader = request()->header('X-Company-Wifi');
        return $customHeader === hash('sha256', $this->allowedWifiSsid . env('APP_KEY'));
    }
}