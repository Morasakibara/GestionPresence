<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Utilisateur;

class MissingDepartureNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $employe;
    protected $date;

    /**
     * Create a new notification instance.
     */
    public function __construct(Utilisateur $employe, $date)
    {
        $this->employe = $employe;
        $this->date = $date;
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
            $subject = 'Notification d\'absence de départ - Administrateur';
            $greeting = 'Bonjour Administrateur,';
        } elseif ($notifiable->role === 'Superviseur') {
            $subject = 'Notification d\'absence de départ d\'un membre de votre équipe';
            $greeting = 'Bonjour Superviseur,';
        } else {
            $subject = 'Notification d\'absence de départ';
            $greeting = 'Bonjour,';
        }

        return (new MailMessage)
                    ->subject($subject)
                    ->greeting($greeting)
                    ->line('Un employé n\'a pas marqué son départ aujourd\'hui avant 18h30.')
                    ->line('Nom de l\'employé: ' . $this->employe->nom)
                    ->line('Date: ' . $this->date)
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
            // Vérifier si l'employé fait partie de l'équipe du superviseur
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
            'type' => 'absence_depart',
            'employe_id' => $this->employe->id,
            'employe_nom' => $this->employe->nom,
            'date' => $this->date,
            'message' => $messagePrefix . 'L\'employé ' . $this->employe->nom . ' n\'a pas marqué son départ avant 18h30 le ' . $this->date,
            'is_team_member' => $isSupervisorTeamMember
        ];
    }
}
