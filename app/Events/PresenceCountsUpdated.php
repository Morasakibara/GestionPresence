<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Diffusé à chaque changement de compteur (précédent/absent).
 * Permet de mettre à jour les stats en direct sans polling.
 */
class PresenceCountsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $present;
    public int $absent;

    public function __construct(int $present, int $absent)
    {
        $this->present = $present;
        $this->absent = $absent;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('presence-alerts'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'PresenceCountsUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'present' => $this->present,
            'absent' => $this->absent,
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}
