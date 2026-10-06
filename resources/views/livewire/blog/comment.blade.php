<div class="mt-12 border-t border-zinc-200/80 dark:border-zinc-800/80 pt-10">
    
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight flex items-center gap-3">
            Comments 
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                {{ $comments->count() + $comments->sum(fn($c) => $c->replies->count()) }}
            </span>
        </h2>
    </div>

    @if (session('comment-success'))
        <div class="mb-6 bg-emerald-500/10 border border-emerald-500/20 backdrop-blur-xl rounded-2xl p-4 shadow-sm" wire:transition>
            <p class="text-sm font-medium text-emerald-800 dark:text-emerald-300 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('comment-success') }}
            </p>
        </div>
    @endif

    @auth
        <div class="mb-10 bg-white/70 dark:bg-zinc-900/70 backdrop-blur-xl rounded-3xl p-6 sm:p-8 border border-zinc-200/80 dark:border-zinc-800/80 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-none transition-all">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4 tracking-tight">Leave a comment</h3>
            
            <form wire:submit="postComment">
                <textarea 
                    wire:model="newComment"
                    rows="4"
                    placeholder="Share your thoughts..."
                    class="w-full rounded-2xl bg-zinc-50/80 dark:bg-zinc-950/80 border border-zinc-200/80 dark:border-zinc-800 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-inner transition-all duration-200 p-4"
                ></textarea>
                
                @error('newComment')
                    <p class="mt-2 text-xs font-medium text-rose-500 dark:text-rose-400">{{ $message }}</p>
                @enderror
                
                <div class="mt-4 flex justify-end">
                    <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-wider shadow-sm shadow-indigo-500/20 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900 transition-all duration-200">
                        Post Comment
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="mb-10 bg-white/70 dark:bg-zinc-900/70 backdrop-blur-xl rounded-3xl p-8 border border-zinc-200/80 dark:border-zinc-800/80 shadow-[0_8px_30px_rgb(0,0,0,0.04)] text-center transition-all">
            <p class="text-zinc-600 dark:text-zinc-400 text-sm mb-4 font-medium">You must be logged in to comment.</p>
            <a href="{{ route('login') }}" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-wider shadow-sm shadow-indigo-500/20 transition-all duration-200">
                Login to Comment
            </a>
        </div>
    @endauth

    <div class="space-y-6">
        @forelse($comments as $comment)
            <div class="bg-white/70 dark:bg-zinc-900/70 backdrop-blur-xl rounded-2xl border border-zinc-200/80 dark:border-zinc-800/80 p-6 sm:p-7 shadow-[0_4px_20px_rgb(0,0,0,0.03)] transition-all">
                
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <img 
                            src="https://ui-avatars.com/api/?name={{ urlencode($comment->user->name) }}&background=6366f1&color=fff" 
                            alt="{{ $comment->user->name }}" 
                            class="w-10 h-10 rounded-full object-cover shadow-sm flex-shrink-0"
                        >
                        <div>
                            <p class="font-semibold text-zinc-900 dark:text-zinc-100 text-sm">{{ $comment->user->name }}</p>
                            <p class="text-xs text-zinc-400 dark:text-zinc-500 font-medium">{{ $comment->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>

                <div class="text-zinc-700 dark:text-zinc-300 text-sm sm:text-base leading-relaxed mb-4 pl-1">
                    {{ $comment->content }}
                </div>

                <div class="flex items-center gap-4 pl-1">
                    @auth
                        @if($replyingTo === $comment->id)
                            <button wire:click="cancelReply" class="text-xs font-semibold text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200 transition-colors">
                                Cancel
                            </button>
                        @else
                            <button wire:click="startComment({{ $comment->id }})" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                Reply
                            </button>
                        @endif
                    @endauth
                </div>

                @if($replyingTo === $comment->id)
                    <div class="mt-4 bg-zinc-50/80 dark:bg-zinc-950/60 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-zinc-200/80 dark:border-zinc-800/80 transition-all" wire:transition>
                        <form wire:submit="postReply({{ $comment->id }})">
                            <textarea 
                                wire:model="replyContent"
                                rows="3"
                                placeholder="Write your reply..."
                                class="w-full rounded-xl bg-white/80 dark:bg-zinc-900/80 border border-zinc-200/80 dark:border-zinc-800 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-inner transition-all duration-200 p-3"
                            ></textarea>
                            
                            @error('replyContent')
                                <p class="mt-2 text-xs font-medium text-rose-500 dark:text-rose-400">{{ $message }}</p>
                            @enderror
                            
                            <div class="mt-3 flex justify-end gap-2">
                                <button type="button" wire:click="cancelReply" class="inline-flex items-center px-3.5 py-2 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl font-semibold text-xs text-zinc-700 dark:text-zinc-300 uppercase tracking-wider shadow-sm hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-all duration-200">
                                    Cancel
                                </button>
                                <button type="submit" class="inline-flex items-center px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-wider shadow-sm shadow-indigo-500/20 transition-all duration-200">
                                    Post Reply
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                @if($comment->replies->count() > 0)
                    <div class="mt-5 ml-4 sm:ml-6 space-y-3 border-l-2 border-indigo-500/20 dark:border-indigo-500/30 pl-4 sm:pl-6">
                        @foreach($comment->replies as $reply)
                            <div class="bg-zinc-50/80 dark:bg-zinc-950/60 backdrop-blur-md rounded-2xl p-4 border border-zinc-200/60 dark:border-zinc-800/60 transition-all">
                                <div class="flex items-start mb-2.5">
                                    <img 
                                        src="https://ui-avatars.com/api/?name={{ urlencode($reply->user->name) }}&background=6366f1&color=fff" 
                                        alt="{{ $reply->user->name }}" 
                                        class="w-7 h-7 rounded-full object-cover mr-3 shadow-sm flex-shrink-0"
                                    >
                                    <div>
                                        <p class="font-semibold text-zinc-900 dark:text-zinc-100 text-xs sm:text-sm">{{ $reply->user->name }}</p>
                                        <p class="text-[11px] text-zinc-400 dark:text-zinc-500 font-medium">{{ $reply->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <div class="text-zinc-700 dark:text-zinc-300 text-xs sm:text-sm leading-relaxed pl-10">
                                    {{ $reply->content }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>
        @empty
            <div class="text-center py-16 bg-white/50 dark:bg-zinc-900/50 backdrop-blur-xl rounded-3xl border border-zinc-200/80 dark:border-zinc-800/80 shadow-sm">
                <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                </div>
                <p class="text-zinc-600 dark:text-zinc-400 font-medium text-sm">No comments yet. Be the first to share your thoughts!</p>
            </div>
        @endforelse
    </div>
</div>