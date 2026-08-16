<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembresBloquesNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $membres;
    protected $total;

    public function __construct(array $membres)
    {
        $this->membres = $membres;
        $this->total = count($membres);
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('🚫 ' . $this->total . ' membre(s) de votre équipe bloqué(s) au pointage')
            ->greeting('Bonjour Superviseur,')
            ->line($this->total . ' membre(s) de votre équipe ont atteint le seuil de présences suspectes non justifiées et ne peuvent plus pointer. Ils ont besoin d\'un examen de leurs présences par l\'administrateur.');

        foreach ($this->membres as $m) {
            $mail->line('- ' . $m['nom'] . ' (' . $m['suspects'] . ' présence(s) suspecte(s) non justifiée(s))');
        }

        $mail->action('Voir les présences suspectes', url('/superviseur/suspect-presences'))
            ->line('Merci d\'utiliser notre application de gestion de présence!');

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'membres_bloques',
            'membres' => $this->membres,
            'total' => $this->total,
            'message' => '🚫 ' . $this->total . ' membre(s) de votre équipe sont bloqués au pointage'
                       . ' (' . collect($this->membres)->pluck('nom')->implode(', ') . ')'
                       . ' — leurs présences suspectes non justifiées doivent être examinées.',
        ];
    }
}
