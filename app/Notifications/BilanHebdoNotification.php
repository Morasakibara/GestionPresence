<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Barryvdh\DomPDF\Facade\Pdf;

class BilanHebdoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $d = $this->data;

        $mail = (new MailMessage)
            ->subject('📊 Bilan hebdomadaire des présences — ' . $d['periode'])
            ->greeting('Bonjour Administrateur,')
            ->line('Voici le bilan de la semaine écoulée concernant les présences suspectes.');

        foreach ($d['lignes'] as $label => $value) {
            $mail->line($label . ': **' . $value . '**');
        }

        // Générer et joindre le PDF du bilan
        try {
            $details = $d['details'] ?? [];
            $pdf = Pdf::loadView('admin.bilan_hebdo_pdf', [
                'periode'       => $d['periode'],
                'lignes'        => $d['lignes'],
                'details'       => $details,
                'admin'         => $notifiable->nom,
                'generatedDate' => now()->format('d/m/Y H:i'),
            ]);
            $mail->attachData($pdf->output(), 'bilan_suspectes_' . now()->format('Y_m_d') . '.pdf', [
                'mime' => 'application/pdf',
            ]);
        } catch (\Throwable $e) {
            // Le PDF est optionnel : on envoie l'email sans pièce jointe en cas d'échec
        }

        $mail->action('Voir les présences suspectes', url('/admin/suspect-presences'))
            ->line('Merci d\'utiliser notre application de gestion de présence!');

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        $d = $this->data;

        return [
            'type' => 'bilan_hebdo',
            'periode' => $d['periode'],
            'nouveau' => $d['nouveau'],
            'examine' => $d['examine'],
            'justifie' => $d['justifie'],
            'rejete' => $d['rejete'],
            'total' => $d['total'],
            'message' => '📊 Bilan hebdomadaire : ' . $d['total'] . ' présence(s) suspecte(s) sur la semaine ('
                       . $d['nouveau'] . ' nouvelle(s), ' . $d['justifie'] . ' justifiée(s), ' . $d['rejete'] . ' rejetée(s)).',
        ];
    }
}
