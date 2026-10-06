<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Comment;

new class extends Component
{
    use WithPagination;
    
    public string $search = '';
    public string $statusFilter = 'all';


    public function with(): array
    {
        $query = Comment::with(['user', 'post'])
            ->latest();

        if ($this->search) {
            $query->where('content', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', function($q) {
                      $q->where('name', 'like', '%' . $this->search . '%');
                  });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if (auth()->user()->hasRole('author')) {
            $query->whereHas('post', function($q) {
                $q->where('user_id', auth()->id());
            });
        }

        return [
            'comments' => $query->paginate(20),
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function approveComment(Comment $comment): void
    {
        $comment->update(['status' => 'approved']);
        session()->flash('success', 'Comment approved!'); 
    }

    public function markAsSpam(Comment $comment): void
    {
        $comment->update(['status' => 'spam']);
        session()->flash('success', 'Comment marked as spam!');
    }

    public function deleteComment(Comment $comment): void
    {
        $comment->delete();
        session()->flash('success', 'Comment deleted!');
    }
};
?>

<div x-data="{ showDeleteModal: false, commentIdToDelete: null }" class="min-h-screen bg-white text-zinc-900 p-6 transition-colors duration-200">
    
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-zinc-900">Comments</h1>
        <p class="mt-1 text-sm text-zinc-600">Moderate and manage post comments</p>
    </div>

    <div class="mb-6 bg-white rounded-2xl border border-zinc-200 p-5 shadow-sm transition-all">
        <div class="flex flex-col sm:flex-row gap-4">
            
            <div class="flex-1">
                <input 
                    type="text"
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search comments..." 
                    class="w-full px-4 py-2.5 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 placeholder-zinc-400 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-inner"
                />
            </div>

            <div class="sm:w-48">
                <select 
                    wire:model.live="statusFilter" 
                    class="w-full px-4 py-2.5 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-inner"
                >
                    <option value="all">All Status</option>
                    <option value="approved">Approved</option>
                    <option value="pending">Pending</option>
                    <option value="spam">Spam</option>
                </select>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 rounded-2xl p-4 shadow-sm" wire:transition>
            <p class="text-sm font-medium text-emerald-800 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </p>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden shadow-sm transition-all">
        <div class="divide-y divide-zinc-100">
            
            @forelse($comments as $comment)
                <div class="p-6 hover:bg-zinc-50/50 transition-colors" wire:key="comment-{{ $comment->id }}">
                    
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center gap-3">
                            <img 
                                src="https://ui-avatars.com/api/?name={{ urlencode($comment->user->name) }}&background=6366f1&color=fff" 
                                alt="{{ $comment->user->name }}" 
                                class="w-10 h-10 rounded-full object-cover shadow-sm border border-zinc-100"
                            >
                            <div>
                                <p class="text-sm font-semibold text-zinc-900">{{ $comment->user->name }}</p>
                                <p class="text-xs text-zinc-500 mt-0.5">
                                    on <a href="{{ route('blog.show', $comment->post->slug) }}" target="_blank" class="font-medium text-indigo-600 hover:text-indigo-800 transition-colors">{{ Str::limit($comment->post->title, 50) }}</a>
                                </p>
                            </div>
                        </div>

                        <span class="px-2.5 py-1 inline-flex text-[11px] leading-none font-semibold rounded-full border
                            {{ $comment->status === 'approved' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}
                            {{ $comment->status === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}
                            {{ $comment->status === 'spam' ? 'bg-rose-50 text-rose-700 border-rose-200' : '' }}
                        ">
                            {{ ucfirst($comment->status) }}
                        </span>
                    </div>

                    <div class="mt-4 text-sm text-zinc-700 leading-relaxed">
                        {{ $comment->content }}
                    </div>

                    <div class="mt-4 pt-4 border-t border-zinc-100 flex items-center justify-between">
                        <p class="text-xs font-medium text-zinc-500">
                            {{ $comment->created_at->format('M d, Y \a\t g:i A') }}
                        </p>

                        <div class="flex gap-4">
                            @if($comment->status !== 'approved')
                                <button 
                                    wire:click="approveComment({{ $comment->id }})"
                                    class="text-sm text-emerald-600 hover:text-emerald-800 font-semibold transition-colors"
                                >
                                    Approve
                                </button>
                            @endif

                            @if($comment->status !== 'spam')
                                <button 
                                    wire:click="markAsSpam({{ $comment->id }})"
                                    class="text-sm text-amber-600 hover:text-amber-800 font-semibold transition-colors"
                                >
                                    Mark as Spam
                                </button>
                            @endif

                            <button 
                                @click="commentIdToDelete = {{ $comment->id }}; showDeleteModal = true"
                                class="text-sm text-rose-600 hover:text-rose-800 font-semibold transition-colors"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-16 text-center text-sm text-zinc-400">
                    No comments found.
                </div>
            @endforelse
            
        </div>
    </div>

    <div class="mt-6">
        {{ $comments->links() }}
    </div>

    <div 
        x-show="showDeleteModal" 
        style="display: none;" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
    >
        <div 
            x-show="showDeleteModal" 
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showDeleteModal = false"
            class="fixed inset-0 bg-zinc-900/40 backdrop-blur-sm transition-opacity"
        ></div>

        <div 
            x-show="showDeleteModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            @keydown.escape.window="showDeleteModal = false"
            class="relative w-full max-w-md bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] border border-zinc-200 p-6 sm:p-8 transform transition-all"
        >
            <div class="flex items-start gap-4 sm:gap-5">
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-rose-50 border border-rose-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="flex-1 mt-1">
                    <h3 class="text-lg font-bold text-zinc-900 tracking-tight">Delete Comment</h3>
                    <p class="mt-2 text-sm text-zinc-500 leading-relaxed">
                        Are you sure you want to delete this comment? This action cannot be undone and will permanently remove it from the post.
                    </p>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <button 
                    type="button" 
                    @click="showDeleteModal = false"
                    class="px-5 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-semibold text-zinc-700 hover:bg-zinc-50 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-zinc-200"
                >
                    Cancel
                </button>
                <button 
                    type="button" 
                    @click="$wire.deleteComment(commentIdToDelete); showDeleteModal = false"
                    class="px-5 py-2.5 bg-rose-600 border border-transparent rounded-xl text-sm font-semibold text-white shadow-md shadow-rose-500/20 hover:bg-rose-700 transition-colors focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2"
                >
                    Confirm Delete
                </button>
            </div>
        </div>
    </div>

</div>