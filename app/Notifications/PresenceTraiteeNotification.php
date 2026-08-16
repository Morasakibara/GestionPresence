<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Utilisateur;
use App\Models\Presence;

class PresenceTraiteeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $employe;
    protected $presence;
    protected $statut;
    protected $commentaire;

    public function __construct(Utilisateur $employe, Presence $presence, string $statut, ?string $commentaire = null)
    {
        $this->employe = $employe;
        $this->presence = $presence;
        $this->statut = $statut;
        $this->commentaire = $commentaire;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statutLabel = match ($this->statut) {
            'examiné' => 'examinée',
            'justifié' => 'justifiée',
            'rejeté' => 'rejetée',
            default => 'traitée',
        };

        return (new MailMessage)
            ->subject('🔍 Présence suspecte ' . $statutLabel . ' — ' . $this->employe->nom)
            ->greeting('Bonjour Superviseur,')
            ->line('La présence suspecte d\'un membre de votre équipe a été traitée par l\'administrateur.')
            ->line('Nom de l\'employé: ' . $this->employe->nom)
            ->line('Date: ' . $this->presence->date)
            ->line('Motif de suspicion: ' . ($this->presence->motif_suspicion ?? 'Non renseigné'))
            ->line('Nouveau statut: ' . $statutLabel)
            ->when($this->commentaire, fn ($message) => $message->line('Commentaire de l\'administrateur: ' . $this->commentaire))
            ->action('Voir les présences suspectes', url('/superviseur/suspect-presences'))
            ->line('Merci d\'utiliser notre application de gestion de présence!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'presence_traitee',
            'employe_id' => $this->employe->id,
            'employe_nom' => $this->employe->nom,
            'date' => $this->presence->date,
            'statut' => $this->statut,
            'message' => '🔍 Présence suspecte de ' . $this->employe->nom . ' le ' . $this->presence->date
                       . ' traitée (' . $this->statut . ')' . ($this->commentaire ? ' : ' . $this->commentaire : ''),
        ];
    }
}
