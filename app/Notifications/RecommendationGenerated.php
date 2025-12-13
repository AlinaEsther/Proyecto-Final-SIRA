<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Recommendation;

class RecommendationGenerated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $recommendationId;

    public function __construct($recommendationId)
    {
        $this->recommendationId = $recommendationId;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $recommendation = Recommendation::with('material')->find($this->recommendationId);

        return (new MailMessage)
            ->subject('Nueva recomendación de IA disponible')
            ->greeting('¡Hola ' . $notifiable->person?->first_name . '!')
            ->line('Se ha generado una nueva recomendación personalizada para ti basada en tu rendimiento académico.')
            ->line('**Curso:** ' . ($recommendation->course_name ?? 'General'))
            ->line('**Material recomendado:** ' . ($recommendation->book_title ?? $recommendation->material?->title ?? 'N/A'))
            ->line('**Razón:** ' . substr($recommendation->reason ?? '', 0, 200))
            ->action('Ver Recomendaciones', url('/recommendations'))
            ->line('Te recomendamos revisar este material para mejorar tu rendimiento académico.');
    }

    public function toDatabase($notifiable)
    {
        $recommendation = Recommendation::with('material')->find($this->recommendationId);

        return [
            'type' => 'recommendation_generated',
            'recommendation_id' => $this->recommendationId,
            'course_name' => $recommendation->course_name,
            'book_title' => $recommendation->book_title,
            'message' => 'Nueva recomendación de IA disponible para ' . $recommendation->course_name,
        ];
    }
}

