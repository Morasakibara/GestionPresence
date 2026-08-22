<?php

namespace App\Notifications;

use App\Models\Commande;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaiementCommandeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Commande $commande,
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
            'paye' => '✅ Entièrement payée',
            'partiel' => '🟠 Paiement partiel reçu',
            default => '📋 Statut mis à jour',
        };

        return (new MailMessage)
            ->subject("Paiement commande #{$this->commande->id} — Le Pharaon")
            ->greeting("Bonjour {$notifiable->nom},")
            ->line("**Commande #{$this->commande->id}** — {$this->commande->type}")
            ->line("Montant versé : **" . number_format($this->montantVerse, 0, ',', '.') . " FCFA**")
            ->line("Statut : **{$statutLabel}**")
            ->line("Montant payé : " . number_format($this->commande->montant_paye, 0, ',', '.') . " / " . number_format($this->commande->montant, 0, ',', '.') . " FCFA")
            ->action('Voir le dashboard', url('/directrice/dashboard'))
            ->line('Le Pharaon — Système de Gestion');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'commande_id' => $this->commande->id,
            'type' => $this->commande->type,
            'montant_verse' => $this->montantVerse,
            'nouveau_statut' => $this->nouveauStatut,
            'montant_paye' => $this->commande->montant_paye,
            'montant_total' => $this->commande->montant,
            'message' => "Paiement de " . number_format($this->montantVerse, 0, ',', '.') . " FCFA reçu pour la commande #{$this->commande->id} ({$this->commande->type}).",
        ];
    }
}
