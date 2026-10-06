<?php

namespace App\Livewire\Blog;

use App\Models\Comment as ModelsComment;
use App\Models\Post;
use App\Notifications\NewCommentNotification;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Comment extends Component
{
    public Post $post;

    #[Validate('required|string|min:3|max:1000')]
    public string $newComment = '';

    public ?int $replyingTo = null;

    #[Validate('required|string|min:3|max:1000')]
    public string $replyContent = '';

    public function mount(Post $post): void
    {
        $this->post = $post;
    }

    public function postComment()
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $this->validate(['newComment' => 'required|string|min:3|max:1000']);

        $comment = ModelsComment::create([
            'post_id' => $this->post->id,
            'user_id' => auth()->id(),
            'content' => $this->newComment,
            'status' => 'approved',
        ]);

        $this->newComment = '';

        if ($this->post->user_id !== auth()->id()) {
            $this->post->user->notify(new NewCommentNotification($comment));
        }

        $this->dispatch('comment-posted');

        session()->flash('comment-success', 'Komentar berhasil ditambahkan');
    }

    public function startComment($commentId)
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $this->replyingTo = $commentId;
        $this->replyContent = '';
    }

    public function cancelReply()
    {
        $this->replyingTo = null;
        $this->replyContent = '';
    }

    public function postReply($parentId)
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $this->validate(['replyContent' => 'required|string|min:3|max:1000']);

        $comment = ModelsComment::create([
            'post_id' => $this->post->id,
            'user_id' => auth()->id(),
            'parent_id' => $parentId,
            'content' => $this->replyContent,
            'status' => 'approved',
        ]);

        $this->replyingTo = null;
        $this->replyContent = '';

        if ($this->post->user_id !== auth()->id()) {
            $this->post->user->notify(new NewCommentNotification($comment));
        }

        $this->dispatch('comment-posted');

        session()->flash('comment-success', 'Komentar berhasil ditambahkan');
    }

    #[On('comment-posted')]
    public function render()
    {
        $comments = ModelsComment::where('post_id', $this->post->id)
            ->approved()
            ->topLevel()
            ->with(['user', 'replies.user'])
            ->latest()
            ->get();

        return view('livewire.blog.comment', [
            'comments' => $comments,
        ]);
    }
}
