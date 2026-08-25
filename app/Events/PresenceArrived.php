<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Diffusé en temps réel quand un employé pointe son arrivée.
 * Écouteable sur le canal "presence-presence-alerts" (côté admin/superviseur).
 */
class PresenceArrived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $userId;
    public string $nom;
    public string $heure;
    public bool $retard;
    public string $status;

    public function __construct(int $userId, string $nom, string $heure, bool $retard, string $status = 'présent')
    {
        $this->userId = $userId;
        $this->nom = $nom;
        $this->heure = $heure;
        $this->retard = $retard;
        $this->status = $status;
    }

    /**
     * Les canaux sur lesquels l'événement est diffusé.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('presence-alerts'),
        ];
    }

    /**
     * Le nom de l'événement diffusé (côté client : Echo.listen('PresenceArrived')).
     */
    public function broadcastAs(): string
    {
        return 'PresenceArrived';
    }

    /**
     * Données transmises au client JS.
     */
    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'nom' => $this->nom,
            'heure' => $this->heure,
            'retard' => $this->retard,
            'status' => $this->status,
            'type' => 'arrival',
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}
