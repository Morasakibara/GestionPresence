<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StockAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $type,       // 'tshirt' ou 'papier'
        public string $details,
        public float  $quantite,
        public float  $seuil,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = $this->type === 'tshirt' ? 'T-Shirt' : 'Papier d\'impression';
        return (new MailMessage)
            ->subject("⚠ Alerte stock — {$label}")
            ->line("Le stock de {$label} est en dessous du seuil d'alerte.")
            ->line("Détails : {$this->details}")
            ->line("Quantité restante : {$this->quantite} (seuil : {$this->seuil})")
            ->action('Voir le stock', url('/gestionnaire/dashboard'))
            ->line('Merci de réapprovisionner dès que possible.');
    }

    public function toArray(object $notifiable): array
    {
        $label = $this->type === 'tshirt' ? 'T-Shirt' : 'Papier';
        return [
            'type'    => 'stock_alert',
            'message' => "⚠ Alerte stock {$label} : {$this->details} — {$this->quantite} restant(s) (seuil: {$this->seuil})",
            'details' => $this->details,
        ];
    }
}
