<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Utilisateur;
use App\Models\Presence;

class RetardNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $employe;
    protected $presence;

    /**
     * Create a new notification instance.
     */
    public function __construct(Utilisateur $employe, Presence $presence)
    {
        $this->employe = $employe;
        $this->presence = $presence;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = '';
        $greeting = '';
        
        if ($notifiable->role === 'administrateur') {
            $subject = 'Notification de retard à un administrateur';
            $greeting = 'Bonjour Administrateur,';
        } elseif ($notifiable->role === 'Superviseur') {
            $subject = 'Notification de retard d\'un membre de votre équipe';
            $greeting = 'Bonjour Superviseur,';
        } else {
            $subject = 'Notification de retard';
            $greeting = 'Bonjour,';
        }
        
        return (new MailMessage)
                    ->subject($subject)
                    ->greeting($greeting)
                    ->line('Un employé est arrivé en retard.')
                    ->line('Nom de l\'employé: ' . $this->employe->nom)
                    ->line('Date: ' . $this->presence->date)
                    ->line('Heure d\'arrivée: ' . $this->presence->heureArrivee->format('H:i'))
                    ->action('Voir les détails', url('/notifications'))
                    ->line('Merci d\'utiliser notre application de gestion de présence!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        // Déterminer si c'est un superviseur qui reçoit une notification pour un membre de son équipe
        $isSupervisorTeamMember = false;
        if ($notifiable->role === 'Superviseur') {
            // Vérifier si l'employé en retard fait partie de l'équipe du superviseur
            $superviseurInfo = \App\Models\Superviseur::where('id', $notifiable->id)->first();
            $employerInfo = \App\Models\Employer::where('id', $this->employe->id)->first();
            
            if ($superviseurInfo && $employerInfo && $superviseurInfo->equipe === $employerInfo->equipe) {
                $isSupervisorTeamMember = true;
            }
        }
        
        $messagePrefix = '';
        if ($notifiable->role === 'administrateur') {
            $messagePrefix = '[Admin] ';
        } elseif ($notifiable->role === 'Superviseur' && $isSupervisorTeamMember) {
            $messagePrefix = '[Équipe] ';
        }
        
        return [
            'type' => 'retard',
            'employe_id' => $this->employe->id,
            'employe_nom' => $this->employe->nom,
            'date' => $this->presence->date,
            'heure_arrivee' => $this->presence->heureArrivee->format('H:i'),
            'message' => $messagePrefix . 'L\'employé ' . $this->employe->nom . ' est arrivé en retard à ' . $this->presence->heureArrivee->format('H:i') . ' le ' . $this->presence->date,
            'is_team_member' => $isSupervisorTeamMember
        ];
    }
}