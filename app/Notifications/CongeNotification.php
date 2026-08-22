<?php

namespace App\Notifications;

use App\Models\Conge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CongeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Conge $conge,
        public string $action,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $typeLabel = match ($this->conge->type) {
            'conge' => 'Congé',
            'absence_justifiee' => 'Absence justifiée',
            'absence_injustifiee' => 'Absence injustifiée',
            default => 'Demande',
        };

        $statutLabel = match ($this->action) {
            'approuve' => '✅ Approuvée',
            'refuse' => '❌ Refusée',
            default => '⏳ En attente',
        };

        return [
            'title' => "Demande de {$typeLabel} {$statutLabel}",
            'message' => "Votre demande du {$this->conge->date_debut->format('d/m/Y')} au {$this->conge->date_fin->format('d/m/Y')} a été {$statutLabel}.",
            'url' => route('user.dashboard'),
        ];
    }
}
