<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class FicheRendementRappelNotification extends Notification
{
    use Queueable;

    public array $membresSansFiche;
    public string $semaine;

    public function __construct(array $membresSansFiche, string $semaine)
    {
        $this->membresSansFiche = $membresSansFiche;
        $this->semaine = $semaine;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('[Le Pharaon] Rappel : fiches de rendement manquantes — semaine du ' . $this->semaine)
            ->greeting('Bonjour ' . $notifiable->nom . ',')
            ->line("Certains membres de votre équipe n'ont pas rempli leur **fiche de rendement** cette semaine (du {$this->semaine}).")
            ->line('Membres concernés :');

        foreach ($this->membresSansFiche as $m) {
            $mail->line('- ' . $m['nom'] . ' (' . $m['manquantes'] . ' fiche(s) manquante(s))');
        }

        return $mail->line('Vous pouvez suivre le rendement de votre équipe dans l\'application.')
            ->action('Voir le rendement de l\'équipe', url('/superviseur/rendements'))
            ->line('Merci d\'utiliser Le Pharaon.');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => "🗓️ Rappel hebdomadaire ({$this->semaine}) : " . count($this->membresSansFiche) . " membre(s) de votre équipe n'ont pas rempli leur fiche de rendement.",
            'semaine' => $this->semaine,
            'membres' => $this->membresSansFiche,
        ];
    }
}
