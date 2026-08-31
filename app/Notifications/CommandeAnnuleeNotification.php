<?php

namespace App\Notifications;

use App\Models\Commande;
use App\Models\Utilisateur;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommandeAnnuleeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Commande $commande,
        public string $typeModule,
        public float $montantPaye
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $montantStr = number_format($this->montantPaye, 0, ',', ' ') . ' FCFA';
        $action = $this->montantPaye > 0 ? "Remboursement de {$montantStr} comptabilisé." : 'Aucun remboursement.';

        return (new MailMessage)
            ->subject("⚠️ Commande #{$this->commande->id} annulée — Le Pharaon")
            ->greeting("Commande annulée — {$this->typeModule}")
            ->line("La commande #{$this->commande->id} ({$this->commande->type}) a été annulée.")
            ->line("Montant total : " . number_format($this->commande->montant, 0, ',', ' ') . " FCFA")
            ->line($action)
            ->line("Date : {$this->commande->date}")
            ->action('Voir le tableau de bord', url('/admin/dashboard'))
            ->line('Le Pharaon — Système de Gestion de Présence');
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => "Commande #{$this->commande->id} ({$this->commande->type}) annulée — {$this->typeModule}.",
            'montant' => $this->commande->montant,
            'montant_paye' => $this->montantPaye,
            'type_module' => $this->typeModule,
            'commande_id' => $this->commande->id,
        ];
    }
}
