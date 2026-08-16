<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class EvaluationRougeNotification extends Notification
{
    use Queueable;

    public $employeNom;
    public $mois;
    public $note;

    public function __construct(string $employeNom, string $mois, float $note)
    {
        $this->employeNom = $employeNom;
        $this->mois = $mois;
        $this->note = $note;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('[Le Pharaon] Alerte évaluation rouge — ' . $this->employeNom)
            ->greeting('Bonjour ' . $notifiable->nom . ',')
            ->line("L'évaluation de **{$this->employeNom}** pour le mois de **{$this->mois}** est passée en **rouge** (note : {$this->note}/20).")
            ->line('Il est recommandé d\'examiner sa discipline et son rendement rapidement.')
            ->action('Voir les rapports', url('/admin/generate-report'))
            ->line('Merci d\'utiliser Le Pharaon.');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => "🔴 Alerte : l'évaluation de {$this->employeNom} ({$this->mois}) est passée en rouge ({$this->note}/20).",
            'employe' => $this->employeNom,
            'mois' => $this->mois,
            'note' => $this->note,
        ];
    }
}
