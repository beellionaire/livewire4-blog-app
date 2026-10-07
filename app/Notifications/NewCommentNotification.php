<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Comment $comment)
    {
        //
    }

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
        // Gunakan FQCN (Fully Qualified Class Name) agar PHPStan tidak bingung
        /** @var User $notifiable */

        /** @var Post $post */
        $post = $this->comment->post;

        /** @var User $commentAuthor */
        $commentAuthor = $this->comment->user;

        return (new MailMessage)
            ->subject('New comment on your post: '.$post->title)
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Someone has commented on your post "'.$post->title.'"')
            ->line('**'.$commentAuthor->name.'** wrote:')
            ->line('"'.$this->comment->content.'"')
            ->action('View Comment', route('blog.show', $post->slug))
            ->line('Thank you for using our application!');
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
