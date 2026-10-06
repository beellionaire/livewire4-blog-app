<?php

use Livewire\Component;
use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use App\Models\PostView;
use Illuminate\Support\Facades\DB;

new class extends Component {

    public function with(): array
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin') || $user->hasRole('editor');

        $postsQuery = $isAdmin ? Post::query()
            : Post::where('user_id', $user->id);

        $stats = [
            'total_posts' => (clone $postsQuery)->count(),
            'published_posts' => (clone $postsQuery)->where('status', 'published')->count(),
            'draft_posts' => (clone $postsQuery)->where('status', 'draft')->count(),
            'total_views' => (clone $postsQuery)->sum('views_count'),
            'total_comments' => $isAdmin ? Comment::count() : Comment::whereHas('post', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->count(),
            'total_users' => $isAdmin ? User::count() : null,
        ];

        $mostViewedPosts = (clone $postsQuery)
            ->where('status', 'published')
            ->orderBy('views_count', 'desc')
            ->take(5)
            ->get();

        $recentComments = Comment::with(['user', 'post'])
            ->when(!$isAdmin, function ($q) use ($user) {
                $q->whereHas('post', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                });
            })
            ->latest()
            ->take(5)
            ->get();

        $RawviewsData = PostView::select(
                DB::raw('DATE(viewed_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->when(!$isAdmin, function($q) use ($user) {
                $q->whereHas('post', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                });
            })
            ->where('viewed_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date'); 
            
        $viewsData = collect();
            for ($i=6; $i >= 0; $i--) { 
                $date = now()->subDays($i);
                $dateKey = $date->format('Y-m-d');
                $dateLabel = $date->format('M d');

                $viewsData->push([
                    'date' => $dateLabel,
                    'count' => isset($RawviewsData[$dateKey]) ? $RawviewsData[$dateKey]->count : 0
                ]);
            }

        return compact('stats', 'mostViewedPosts', 'recentComments', 'viewsData', 'isAdmin');
    }
};
?>

<div class="space-y-8 pb-12">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-zinc-900 dark:text-white tracking-tight">Dashboard</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400 font-medium">Welcome back, {{ auth()->user()->name }}!</p>
        </div>
        <a href="{{ route('blog.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold rounded-xl transition-all duration-200 shadow-sm shadow-indigo-500/25">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Visit Blog
        </a>
    </div>
    
    @island
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white/70 dark:bg-zinc-900/70 backdrop-blur-xl rounded-2xl border border-zinc-200/80 dark:border-zinc-800/80 p-6 shadow-[0_4px_20px_rgb(0,0,0,0.03)] transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold tracking-wider text-zinc-400 dark:text-zinc-500 uppercase">Total Posts</p>
                    <p class="text-3xl font-extrabold text-zinc-900 dark:text-white mt-2 tracking-tight">{{ $stats['total_posts'] }}</p>
                </div>
                <div class="bg-indigo-500/10 dark:bg-indigo-500/20 rounded-2xl p-3 border border-indigo-500/20">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs font-medium pt-3 border-t border-zinc-100 dark:border-zinc-800/60">
                <span class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $stats['published_posts'] }} published</span>
                <span class="text-zinc-300 dark:text-zinc-700 mx-2">•</span>
                <span class="text-amber-600 dark:text-amber-400 font-semibold">{{ $stats['draft_posts'] }} drafts</span>
            </div>
        </div>

        <div class="bg-white/70 dark:bg-zinc-900/70 backdrop-blur-xl rounded-2xl border border-zinc-200/80 dark:border-zinc-800/80 p-6 shadow-[0_4px_20px_rgb(0,0,0,0.03)] transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold tracking-wider text-zinc-400 dark:text-zinc-500 uppercase">Total Views</p>
                    <p class="text-3xl font-extrabold text-zinc-900 dark:text-white mt-2 tracking-tight">{{ number_format($stats['total_views']) }}</p>
                </div>
                <div class="bg-emerald-500/10 dark:bg-emerald-500/20 rounded-2xl p-3 border border-emerald-500/20">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                        </path>
                    </svg>
                </div>
            </div>
            <p class="mt-4 text-xs font-medium text-zinc-500 dark:text-zinc-400 pt-3 border-t border-zinc-100 dark:border-zinc-800/60">Across all posts</p>
        </div>

        <div class="bg-white/70 dark:bg-zinc-900/70 backdrop-blur-xl rounded-2xl border border-zinc-200/80 dark:border-zinc-800/80 p-6 shadow-[0_4px_20px_rgb(0,0,0,0.03)] transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold tracking-wider text-zinc-400 dark:text-zinc-500 uppercase">Total Comments</p>
                    <p class="text-3xl font-extrabold text-zinc-900 dark:text-white mt-2 tracking-tight">{{ number_format($stats['total_comments']) }}</p>
                </div>
                <div class="bg-cyan-500/10 dark:bg-cyan-500/20 rounded-2xl p-3 border border-cyan-500/20">
                    <svg class="w-6 h-6 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                        </path>
                    </svg>
                </div>
            </div>
            <p class="mt-4 text-xs font-medium text-zinc-500 dark:text-zinc-400 pt-3 border-t border-zinc-100 dark:border-zinc-800/60">Engagement from readers</p>
        </div>

        @if($isAdmin)
            <div class="bg-white/70 dark:bg-zinc-900/70 backdrop-blur-xl rounded-2xl border border-zinc-200/80 dark:border-zinc-800/80 p-6 shadow-[0_4px_20px_rgb(0,0,0,0.03)] transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold tracking-wider text-zinc-400 dark:text-zinc-500 uppercase">Total Users</p>
                        <p class="text-3xl font-extrabold text-zinc-900 dark:text-white mt-2 tracking-tight">{{ number_format($stats['total_users']) }}</p>
                    </div>
                    <div class="bg-purple-500/10 dark:bg-purple-500/20 rounded-2xl p-3 border border-purple-500/20">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                    </div>
                </div>
                <p class="mt-4 text-xs font-medium text-zinc-500 dark:text-zinc-400 pt-3 border-t border-zinc-100 dark:border-zinc-800/60">Registered authors & readers</p>
            </div>
        @endif
    </div>
    @endisland

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white/70 dark:bg-zinc-900/70 backdrop-blur-xl rounded-2xl border border-zinc-200/80 dark:border-zinc-800/80 p-6 shadow-[0_4px_20px_rgb(0,0,0,0.03)] transition-all">
            <h2 class="text-base font-bold text-zinc-900 dark:text-white mb-4 tracking-tight">Views Last 7 Days</h2>
            <div class="h-64 w-full">
                <canvas id="viewsChart"></canvas>
            </div>
        </div>

        <div class="bg-white/70 dark:bg-zinc-900/70 backdrop-blur-xl rounded-2xl border border-zinc-200/80 dark:border-zinc-800/80 p-6 shadow-[0_4px_20px_rgb(0,0,0,0.03)] transition-all flex flex-col">
            <h2 class="text-base font-bold text-zinc-900 dark:text-white mb-4 tracking-tight">Most Viewed Posts</h2>
            <div class="space-y-3.5 flex-1 flex flex-col justify-between">
                @forelse($mostViewedPosts as $post)
                    <div class="flex items-center justify-between gap-4 p-2 rounded-xl hover:bg-zinc-100/50 dark:hover:bg-zinc-800/40 transition-colors">
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('posts.edit', $post) }}" class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 hover:text-indigo-600 dark:hover:text-indigo-400 truncate block transition-colors">
                                {{ $post->title }}
                            </a>
                            <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5 font-medium">{{ $post->published_at?->format('M d, Y') }}</p>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                                {{ number_format($post->views_count) }} views
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 italic">No published posts yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white/70 dark:bg-zinc-900/70 backdrop-blur-xl rounded-2xl border border-zinc-200/80 dark:border-zinc-800/80 p-6 shadow-[0_4px_20px_rgb(0,0,0,0.03)] transition-all">
        <h2 class="text-base font-bold text-zinc-900 dark:text-white mb-4 tracking-tight">Recent Comments</h2>
        <div class="space-y-4">
            @forelse($recentComments as $comment)
                <div class="flex items-start space-x-3.5 pb-4 border-b border-zinc-100 dark:border-zinc-800/60 last:border-0 last:pb-0">
                    <img 
                        src="https://ui-avatars.com/api/?name={{ urlencode($comment->user->name) }}&background=6366f1&color=fff" 
                        alt="{{ $comment->user->name }}" 
                        class="w-10 h-10 rounded-full object-cover shadow-sm flex-shrink-0"
                    >
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $comment->user->name }}</span>
                            <span class="text-zinc-400 dark:text-zinc-500 font-normal">commented on</span>
                            <a href="{{ route('posts.edit', $comment->post) }}" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                {{ Str::limit($comment->post->title, 30) }}
                            </a>
                        </p>
                        <p class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 mt-1 line-clamp-2 leading-relaxed">{{ Str::limit($comment->content, 100) }}</p>
                        <p class="text-[11px] text-zinc-400 dark:text-zinc-500 mt-1 font-medium">{{ $comment->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-zinc-500 dark:text-zinc-400 italic">No comments yet.</p>
            @endforelse
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <div 
        id="viewsChartData"
        data-labels='@json($viewsData->pluck('date')->toArray())'
        data-counts='@json($viewsData->pluck('count')->toArray())'
        style="display: none;"
    ></div>

    <script>
        document.addEventListener('livewire:navigated', function(){
            const ctx = document.getElementById('viewsChart');
            const chartDataEl = document.getElementById('viewsChartData');

            const labels = JSON.parse(chartDataEl.dataset.labels);
            const data = JSON.parse(chartDataEl.dataset.counts);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Views',
                        data: data,
                        borderColor: 'rgb(99, 102, 241)',
                        backgroundColor: 'rgba(99, 102, 241, 0.08)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: 'rgb(99, 102, 241)',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(161, 161, 170, 0.1)'
                            },
                            ticks: {
                                precision: 0,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</div>