<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ArrivalReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
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
        return (new MailMessage)
                    ->subject('Rappel: Marquage de présence')
                    ->greeting('Bonjour ' . $notifiable->nom . ',')
                    ->line('Il est presque 10h et vous n\'avez pas encore marqué votre arrivée pour aujourd\'hui.')
                    ->line('Veuillez vous connecter à l\'application pour marquer votre présence dès que possible.')
                    ->action('Marquer ma présence', url('/presence'))
                    ->line('Merci d\'utiliser notre application de gestion de présence!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'reminder',
            'message' => 'Rappel: Vous n\'avez pas encore marqué votre arrivée aujourd\'hui. Il est presque 10h!',
            'date' => now()->toDateString()
        ];
    }
}
