<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Utilisateur;
use App\Models\Presence;

class PresenceContesteeNotification extends Notification implements ShouldQueue
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
            ->subject('⚠️ Contestation d\'une présence suspecte — ' . $this->employe->nom)
            ->greeting('Bonjour Administrateur,')
            ->line('Un employé a contesté sa présence marquée suspecte.')
            ->line('Nom de l\'employé: ' . $this->employe->nom)
            ->line('Date: ' . $this->presence->date)
            ->line('Motif de suspicion: ' . ($this->presence->motif_suspicion ?? 'Non renseigné'))
            ->line('Explication de l\'employé: ' . ($this->presence->commentaire_contestation ?? 'Non renseignée'))
            ->action('Examiner la contestation', url('/admin/suspect-presences'))
            ->line('Merci d\'utiliser notre application de gestion de présence!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'presence_contestee',
            'employe_id' => $this->employe->id,
            'employe_nom' => $this->employe->nom,
            'date' => $this->presence->date,
            'message' => '⚠️ ' . $this->employe->nom . ' conteste sa présence suspecte du ' . $this->presence->date
                       . ' : "' . ($this->presence->commentaire_contestation ?? '') . '"',
        ];
    }
}
