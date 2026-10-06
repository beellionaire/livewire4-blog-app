<?php

use Livewire\Component;
use App\Models\Post;
use Livewire\Attributes\Layout;
use App\Models\PostView;

new #[Layout('layouts.public')] class extends Component
{
    public Post $post;

    public function mount($slug)
    {
        $this->post = Post::where('slug', $slug)
            ->where('status', 'published')
            ->with(['user','categories','tags'])
            ->firstOrFail();
        // track views
        $this->trackView();
    }

    protected function trackView(){
        // increment the counter
        $this->post->increment('views_count');

        // record the detailed view
        PostView::create([
            'post_id' => $this->post->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => auth()->id(),
            'viewed_at' => now(),
        ]);
    }
};
?>

<div class="py-12 bg-zinc-50 dark:bg-zinc-950 min-h-screen transition-colors duration-300">
    <article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8">
            <a href="{{ route('blog.index') }}" wire:navigate class="inline-flex items-center text-sm font-semibold text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors group">
                <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to posts
            </a>
        </div>

        <header class="mb-10">
            @if($post->categories->count() > 0)
                <div class="flex flex-wrap gap-2 mb-6">
                    @foreach($post->categories as $category)
                        <a 
                            href="{{ route('blog.index', ['category' => $category->slug]) }}" 
                            wire:navigate
                            class="inline-flex items-center gap-1.5 px-3.5 py-1 text-xs font-semibold rounded-full text-white shadow-sm hover:opacity-90 transition-all"
                            style="background-color: {{ $category->color }}"
                        >
                            <span class="w-1.5 h-1.5 rounded-full bg-white/80"></span>
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-zinc-900 dark:text-white tracking-tight leading-[1.15] mb-6">
                {{ $post->title }}
            </h1>

            <div class="flex items-center justify-between py-6 border-y border-zinc-200 dark:border-zinc-800/80">
                <div class="flex items-center gap-4">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($post->user->name) }}&background=6366f1&color=fff" alt="{{ $post->user->name }}" class="w-11 h-11 rounded-full object-cover shadow-sm">
                    <div>
                        <p class="font-semibold text-zinc-900 dark:text-zinc-100 text-sm">{{ $post->user->name }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 font-medium">
                            <time datetime="{{ $post->published_at->format('Y-m-d') }}">{{ $post->published_at->format('F d, Y') }}</time>
                            <span class="mx-1.5">•</span>
                            <span>{{ ceil(str_word_count(strip_tags($post->content)) / 200) }} min read</span>
                        </p>
                    </div>
                </div>

                @if ($post->views_count > 0)
                    <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-zinc-100 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-xs font-medium text-zinc-500 dark:text-zinc-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        {{ number_format($post->views_count) }} views
                    </div>
                @endif
            </div>
        </header>

        @if($post->featured_image)
            <div class="mb-12 rounded-3xl overflow-hidden border border-zinc-200/80 dark:border-zinc-800/80 shadow-[0_8px_30px_rgb(0,0,0,0.04)] bg-zinc-100 dark:bg-zinc-900">
                <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-auto max-h-[500px] object-cover">
            </div>
        @endif

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800/80 rounded-3xl p-6 sm:p-10 md:p-12 shadow-[0_4px_20px_rgb(0,0,0,0.02)] mb-12">
            <div class="prose prose-zinc dark:prose-invert max-w-none text-zinc-700 dark:text-zinc-300 leading-relaxed text-base sm:text-lg">
                {!! $post->content !!}
            </div>

            @if($post->tags->count() > 0)
                <div class="mt-10 pt-8 border-t border-zinc-100 dark:border-zinc-800/60 flex flex-wrap items-center gap-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400 mr-2">Tags:</span>
                    @foreach($post->tags as $tag)
                        <a 
                            href="{{ route('blog.index', ['tag' => $tag->slug]) }}" 
                            wire:navigate
                            class="px-3 py-1 rounded-xl bg-zinc-100 dark:bg-zinc-800/60 hover:bg-zinc-200 dark:hover:bg-zinc-800 text-xs font-medium text-zinc-600 dark:text-zinc-400 transition-colors"
                        >
                            #{{ $tag->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <footer class="bg-white/70 dark:bg-zinc-900/70 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800/80 rounded-3xl p-6 sm:p-8 mb-16 shadow-[0_4px_20px_rgb(0,0,0,0.02)]">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($post->user->name) }}&background=6366f1&color=fff" alt="{{ $post->user->name }}" class="w-14 h-14 rounded-full object-cover shadow-sm">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-1">Written by</p>
                        <h3 class="font-bold text-zinc-900 dark:text-white text-lg">{{ $post->user->name }}</h3>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Published on {{ $post->published_at->format('F d, Y') }}</p>
                    </div>
                </div>
            </div>
        </footer>

        <div class="mt-12">
            <livewire:blog.comment :post="$post" />
        </div>
    </article>
</div>