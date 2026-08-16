<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuspectRappelNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $count;
    protected $jours;

    public function __construct(int $count, int $jours)
    {
        $this->count = $count;
        $this->jours = $jours;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⏰ ' . $this->count . ' présence(s) suspecte(s) non traitées depuis ' . $this->jours . ' jour(s)')
            ->greeting('Bonjour Administrateur,')
            ->line('Des présences suspectes attendent un traitement depuis plus de ' . $this->jours . ' jours.')
            ->line('Nombre de présences en attente: ' . $this->count)
            ->line('Pour rappel : chaque présence suspecte doit être examinée (justifiée, rejetée ou en cours d\'examen) pour garantir l\'intégrité des données de pointage.')
            ->action('Traiter les présences suspectes', url('/admin/suspect-presences'))
            ->line('Merci d\'utiliser notre application de gestion de présence!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'rappel_suspectes',
            'count' => $this->count,
            'jours' => $this->jours,
            'message' => '⏰ ' . $this->count . ' présence(s) suspecte(s) non traitées depuis ' . $this->jours . ' jour(s) — pensez à les examiner.',
        ];
    }
}
