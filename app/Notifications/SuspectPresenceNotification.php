<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Utilisateur;
use App\Models\Presence;

class SuspectPresenceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $employe;
    protected $presence;

    public function __construct(Utilisateur $employe, Presence $presence)
    {
        $this->employe = $employe;
        $this->presence = $presence;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ Présence suspecte détectée')
            ->greeting('Bonjour Administrateur,')
            ->line('Le système anti-triche de géolocalisation a marqué un pointage comme suspect.')
            ->line('Nom de l\'employé: ' . $this->employe->nom)
            ->line('Date: ' . $this->presence->date)
            ->line('Motif: ' . ($this->presence->motif_suspicion ?? 'Non renseigné'))
            ->action('Examiner la présence suspecte', url('/admin/suspect-presences'))
            ->line('Merci d\'utiliser notre application de gestion de présence!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'presence_suspecte',
            'employe_id' => $this->employe->id,
            'employe_nom' => $this->employe->nom,
            'date' => $this->presence->date,
            'motif' => $this->presence->motif_suspicion,
            'message' => '⚠️ Présence suspecte : ' . $this->employe->nom . ' le ' . $this->presence->date
                       . ' (' . ($this->presence->motif_suspicion ?? 'motif non renseigné') . ')',
        ];
    }
}
