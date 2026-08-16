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

        $isSuperviseur = $notifiable->role === 'Superviseur';
        $isEmploye = $notifiable->id === $this->employe->id;

        $greeting = $isEmploye ? 'Bonjour ' . $this->employe->nom . ',' : 'Bonjour Superviseur,';
        $subject = '🔍 Présence suspecte ' . $statutLabel . ' — ' . $this->employe->nom;
        $ligne1 = $isEmploye
            ? 'Le statut de votre présence suspecte du ' . $this->presence->date . ' a été mis à jour par l\'administrateur.'
            : 'La présence suspecte d\'un membre de votre équipe a été traitée par l\'administrateur.';
        $lien = $isSuperviseur
            ? url('/superviseur/suspect-presences')
            : url('/user/presence-report');

        return (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line($ligne1)
            ->line('Nom de l\'employé: ' . $this->employe->nom)
            ->line('Date: ' . $this->presence->date)
            ->line('Motif de suspicion: ' . ($this->presence->motif_suspicion ?? 'Non renseigné'))
            ->line('Nouveau statut: ' . $statutLabel)
            ->when($this->commentaire, fn ($message) => $message->line('Commentaire de l\'administrateur: ' . $this->commentaire))
            ->action('Voir les détails', $lien)
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
