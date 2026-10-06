<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPostPublished extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Post $post) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Post'.$this->post->title)
            ->greeting('Hello')
            ->line('Postingan baru sudah tersedia untuk dibaca di'.config('app.name'))
            ->line('**'.$this->post->title.'**')
            ->line($this->post->excerpt ?? 'Click below to read full post')
            ->action('Read Post', url('/'))
            ->line('Terima kasih sudah berlangganan!')
            ->line('[Unsubscribed]('.route('unsubscribed', $notifiable->token).')');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
