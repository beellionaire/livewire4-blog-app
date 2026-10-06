<?php

namespace App\Observers;

use App\Models\Post;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Notification;

class PostObserver
{
    /**
     * Handle the Post "created" event.
     */
    public function created(Post $post): void
    {
        //
    }

    /**
     * Handle the Post "updated" event.
     */
    public function updated(Post $post): void
    {
        // check if post status is published and was not published before
        if ($post->isDirty('status') && $post->status == 'published' && $post->getOriginal('status') != 'published') {
            // send notification to all verified subscribers
            $subscribers = Subscriber::where('is_verified', true)->get();

            // send notification to each subscriber
            if ($subscribers->count() > 0) {
                Notification::send($subscribers, new \App\Notifications\NewPostPublished($post));
            }
        }
    }

    /**
     * Handle the Post "deleted" event.
     */
    public function deleted(Post $post): void
    {
        //
    }

    /**
     * Handle the Post "restored" event.
     */
    public function restored(Post $post): void
    {
        //
    }

    /**
     * Handle the Post "force deleted" event.
     */
    public function forceDeleted(Post $post): void
    {
        //
    }
}
