<?php

namespace App\Notifications;

use App\Models\ServiceFourni;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Informe l'admin quand le solde d'un service est encaissé
 * (miroir de PaiementCommandeNotification).
 */
class PaiementServiceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ServiceFourni $service,
        public float $montantVerse,
        public string $nouveauStatut,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statutLabel = match($this->nouveauStatut) {
            'paye' => '✅ Entièrement payé',
            'partiel' => '🟠 Paiement partiel reçu',
            default => '📋 Statut mis à jour',
        };

        return (new MailMessage)
            ->subject("Paiement service #{$this->service->id} — Le Pharaon")
            ->greeting("Bonjour {$notifiable->nom},")
            ->line("**Service #{$this->service->id}** — {$this->service->type}")
            ->line("Montant versé : **" . number_format($this->montantVerse, 0, ',', '.') . " FCFA**")
            ->line("Statut : **{$statutLabel}**")
            ->line("Montant payé : " . number_format($this->service->montant_paye, 0, ',', '.') . " / " . number_format($this->service->montant, 0, ',', '.') . " FCFA")
            ->action('Voir le dashboard', url('/directrice/dashboard'))
            ->line('Le Pharaon — Système de Gestion');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'service_id' => $this->service->id,
            'type' => $this->service->type,
            'montant_verse' => $this->montantVerse,
            'nouveau_statut' => $this->nouveauStatut,
            'montant_paye' => $this->service->montant_paye,
            'montant_total' => $this->service->montant,
            'message' => "Paiement de " . number_format($this->montantVerse, 0, ',', '.') . " FCFA reçu pour le service #{$this->service->id} ({$this->service->type}).",
        ];
    }
}
