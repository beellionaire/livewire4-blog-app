<?php

use Livewire\Component;
use App\Models\Post;
use Livewire\WithPagination;
new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $status = 'all';

    public function with(): array
    {
        $query = Post::with(['user','categories','tags'])
        ->latest();
        
        if ($this->search) {
            $query->where('title', 'like', '%' . $this->search . '%')
                ->orWhere('content', 'like', '%' . $this->search . '%');
        }

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if (auth()->user()->hasRole('author')) {
            $query->where('user_id', auth()->id());
        }

        return [
            'posts' => $query->paginate(10),
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function deletePost(Post $post)
    {
        if (
            auth()->user()->can('delete all posts')
            || (auth()->user()->can('delete own posts') && $post->user_id === auth()->id())
        ) {
            $post->delete();

            session()->flash('success', 'Post deleted successfully!');
        }
    }
};
?>

<div class="min-h-screen bg-white text-zinc-900 p-6 transition-colors duration-200">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-zinc-900">Posts</h1>
        <p class="mt-1 text-sm text-zinc-600">Manage your blog posts</p>
    </div>

    <div class="mb-6 bg-white rounded-2xl border border-zinc-200 p-5 shadow-sm transition-all">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search posts..."
                    class="w-full px-4 py-2.5 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 placeholder-zinc-400 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-inner" />
            </div>

            <div class="sm:w-48">
                <select wire:model.live="status"
                    class="w-full px-4 py-2.5 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-inner">
                    <option value="all">All Posts</option>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                </select>
            </div>

            @can('create posts')
                <div>
                    <a href="{{ route('posts.create') }}"
                        class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs uppercase tracking-wider rounded-xl shadow-md shadow-indigo-500/20 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        New Post
                    </a>
                </div>
            @endcan
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
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200">
                <thead class="bg-zinc-50/70">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-zinc-400">
                            Title
                        </th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-zinc-400">
                            Categories
                        </th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-zinc-400">
                            Author
                        </th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-zinc-400">
                            Status
                        </th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-zinc-400">
                            Created
                        </th>
                        <th class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-zinc-400">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($posts as $post)
                        <tr wire:key="post-{{ $post->id }}" wire:transition class="hover:bg-zinc-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-semibold text-zinc-900">{{ $post->title }}</div>
                                <div class="text-xs text-zinc-500 mt-0.5">{{ Str::limit($post->excerpt, 50) }}</div>
                                @if($post->comments_count > 0)
                                    <div class="mt-1.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-cyan-50 text-cyan-700 border border-cyan-200">
                                            <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $post->comments_count }} {{ Str::plural('comment', $post->comments_count) }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($post->categories as $category)
                                        <span 
                                            class="px-2.5 py-0.5 text-xs font-semibold rounded-full text-white shadow-sm"
                                            style="background-color: {{ $category->color }}"
                                        >
                                            {{ $category->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-zinc-400 italic">No category</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-zinc-900">{{ $post->user->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 inline-flex text-xs leading-none font-semibold rounded-full 
                                    {{ $post->status === 'published' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                                    {{ $post->status === 'draft' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}
                                    {{ $post->status === 'archived' ? 'bg-zinc-100 text-zinc-700 border border-zinc-200' : '' }}
                                ">
                                    {{ ucfirst($post->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-zinc-500 font-medium">
                                {{ $post->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-3">
                                    @if(auth()->user()->can('edit all posts') || 
                                        (auth()->user()->can('edit own posts') && $post->user_id === auth()->id()))
                                        <a href="{{ route('posts.edit', $post) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold transition-colors">
                                            Edit
                                        </a>
                                    @endif

                                    @if(auth()->user()->can('delete all posts') || 
                                        (auth()->user()->can('delete own posts') && $post->user_id === auth()->id()))
                                        <button 
                                            wire:click="deletePost({{ $post->id }})"
                                            wire:confirm="Are you sure you want to delete this post?"
                                            class="text-rose-600 hover:text-rose-800 font-semibold transition-colors"
                                        >
                                            Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-zinc-400 text-sm">
                                No post found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-6">
        {{ $posts->links() }}
    </div>
</div>