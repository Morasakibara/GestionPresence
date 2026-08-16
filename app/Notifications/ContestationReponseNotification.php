<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Utilisateur;
use App\Models\Presence;

class ContestationReponseNotification extends Notification implements ShouldQueue
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
        $accord = $this->presence->reponse_contestation === 'accordé';

        return (new MailMessage)
            ->subject(($accord ? '✅' : '❌') . ' Réponse à votre contestation — ' . $this->presence->date)
            ->greeting('Bonjour ' . $this->employe->nom . ',')
            ->line($accord
                ? 'L\'administrateur a accepté votre contestation concernant votre présence du ' . $this->presence->date . '.'
                : 'L\'administrateur a refusé votre contestation concernant votre présence du ' . $this->presence->date . '.')
            ->line('Motif de suspicion initial: ' . ($this->presence->motif_suspicion ?? 'Non renseigné'))
            ->when($this->presence->commentaire_reponse_contestation, fn ($m) => $m->line('Commentaire de l\'administrateur: ' . $this->presence->commentaire_reponse_contestation))
            ->line('Statut de la présence: ' . $this->presence->statut_traitement ?? 'nouveau')
            ->action('Voir mon bilan de présence', url('/user/presence-report'))
            ->line('Merci d\'utiliser notre application de gestion de présence!');
    }

    public function toArray(object $notifiable): array
    {
        $accord = $this->presence->reponse_contestation === 'accordé';

        return [
            'type' => 'contestation_reponse',
            'employe_id' => $this->employe->id,
            'date' => $this->presence->date,
            'reponse' => $this->presence->reponse_contestation,
            'message' => ($accord ? '✅' : '❌') . ' Votre contestation de la présence du ' . $this->presence->date
                       . ' a été ' . ($accord ? 'acceptée' : 'refusée') . ' par l\'administrateur'
                       . ($this->presence->commentaire_reponse_contestation ? ' : ' . $this->presence->commentaire_reponse_contestation : ''),
        ];
    }
}
