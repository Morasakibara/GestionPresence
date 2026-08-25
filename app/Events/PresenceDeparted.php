<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Diffusé en temps réel quand un employé pointe son départ.
 */
class PresenceDeparted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $userId;
    public string $nom;
    public string $heure;

    public function __construct(int $userId, string $nom, string $heure)
    {
        $this->userId = $userId;
        $this->nom = $nom;
        $this->heure = $heure;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('presence-alerts'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'PresenceDeparted';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'nom' => $this->nom,
            'heure' => $this->heure,
            'type' => 'departure',
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}
