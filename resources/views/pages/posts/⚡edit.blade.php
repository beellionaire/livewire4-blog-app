<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Post;
use App\Models\Tag;
use App\Models\Category;
use Livewire\Attributes\Validate;
use Illuminate\Support\Str;

new class extends Component
{
    use WithFileUploads;

    public Post $post;

    #[Validate('required|string|min:3|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:500')]
    public string $excerpt = '';

    #[Validate('required|string|min:10')]
    public string $content = '';

    #[Validate('nullable|image|max:2048')]
    public $featured_image;

    #[Validate('required|in:draft,published,archived')]
    public string $status = '';

    public string $existing_image = '';

    #[Validate('required|array|min:1')]
    public array $selectedCategories = [];

    #[Validate('nullable|array')]
    public array $selectedTags = [];

    public function mount(Post $post): void
    {
        // Authorization check
        if (!auth()->user()->can('edit all posts') && 
            !(auth()->user()->can('edit own posts') && $post->user_id === auth()->id())) {
            abort(403);
        }

        $this->post = $post;
        $this->title = $post->title;
        $this->excerpt = $post->excerpt ?? '';
        $this->content = $post->content;
        $this->status = $post->status;
        $this->existing_image = $post->featured_image ?? '';

        // Load existing categories and tags
        $this->selectedCategories = $post->categories->pluck('id')->toArray();
        $this->selectedTags = $post->tags->pluck('id')->toArray();
    }

    public function with(): array
    {
        return [
            'categories' => Category::all(), 
            'tags' => Tag::all(), 
        ];
    }

    public function update(): void
    {
        $this->validate();

        $this->post->title = $this->title;
        $this->post->slug = Str::slug($this->title);
        $this->post->excerpt = $this->excerpt;
        $this->post->content = $this->content;
        $this->post->status = $this->status;

        if ($this->featured_image) {
            // Delete old image if exists
            if ($this->existing_image) {
                \Storage::disk('public')->delete($this->existing_image);
            }
            
            $path = $this->featured_image->store('posts', 'public');
            $this->post->featured_image = $path;
            $this->existing_image = $path;
        }

        if ($this->status === 'published' && !$this->post->published_at) {
            $this->post->published_at = now();
        }

        $this->post->save();

        // Sync categories and tags
        $this->post->categories()->sync($this->selectedCategories);
        $this->post->tags()->sync($this->selectedTags);


        session()->flash('success', 'Post updated successfully!');
        
        $this->redirect(route('posts.index'), navigate: true);
    }

};
?>

<div class="min-h-screen bg-white text-zinc-900 p-6 transition-colors duration-200">
    
    <div class="mb-8 w-full">
        <h1 class="text-3xl font-extrabold tracking-tight text-zinc-900">Edit Post</h1>
        <p class="mt-1 text-sm text-zinc-600">Update and refine your blog post.</p>
    </div>

    <div class="w-full bg-white rounded-3xl border border-zinc-200 p-6 sm:p-8 md:p-10 shadow-sm transition-all">
        <form wire:submit="update" class="space-y-10">
            
            <div class="space-y-6">
                <div>
                    <label for="title" class="block text-sm font-semibold text-zinc-700 mb-2">
                        Post Title
                    </label>
                    <input 
                        type="text"
                        id="title"
                        wire:model.live.debounce="title" 
                        placeholder="Enter post title"
                        class="w-full px-4 py-3 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 placeholder-zinc-400 text-base focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-inner font-medium"
                    />
                    @error('title')
                        <p class="mt-2 text-sm font-medium text-rose-600 flex items-center gap-1">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label for="excerpt" class="block text-sm font-semibold text-zinc-700 mb-2">
                        Excerpt <span class="text-zinc-400 font-normal ml-1">(Optional)</span>
                    </label>
                    <textarea 
                        id="excerpt"
                        wire:model="excerpt" 
                        placeholder="A short summary of your post..."
                        rows="2"
                        class="w-full px-4 py-3 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 placeholder-zinc-400 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-inner resize-y"
                    ></textarea>
                    @error('excerpt')
                        <p class="mt-2 text-sm font-medium text-rose-600 flex items-center gap-1">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="content" class="block text-sm font-semibold text-zinc-700 mb-2">
                    Main Content
                </label>
                <div class="relative rounded-xl overflow-hidden shadow-inner border border-zinc-200 bg-zinc-50 focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500 transition-all"
                    wire:ignore
                    x-data="{
                        content: $wire.entangle('content'),
                    }"
                    x-init="
                        let editor = $refs.trixEditor.editor;
                        editor.loadHTML(content);
                        $refs.trixEditor.addEventListener('trix-change', function(e){
                            content = e.target.value;
                        });
                    "
                >
                    <input id="x-content" type="hidden" name="content">
                    <trix-editor
                        input="x-content"
                        class="trix-content w-full border-0 bg-transparent text-zinc-900 text-sm min-h-[400px] p-4 focus:outline-none prose prose-indigo max-w-none"
                        x-ref="trixEditor"
                    ></trix-editor>
                </div>
                
                @error('content')
                    <p class="mt-2 text-sm font-medium text-rose-600 flex items-center gap-1">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <hr class="border-zinc-100">

            <div>
                <label class="block text-sm font-semibold text-zinc-700 mb-3">
                    Featured Image
                </label>
                
                <div class="flex flex-col md:flex-row items-start gap-8">
                    @if ($existing_image && !$featured_image)
                        <div class="flex-shrink-0">
                            <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-2">Current Image</p>
                            <div class="relative">
                                <img src="{{ Storage::url($existing_image) }}" class="h-36 w-56 object-cover rounded-2xl border border-zinc-200 shadow-sm" alt="Current image">
                                <div class="absolute inset-0 rounded-2xl ring-1 ring-inset ring-black/10"></div>
                            </div>
                        </div>
                    @endif
                    
                    <div class="flex-1 w-full">
                        <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-2">Upload New Image</p>
                        <input 
                            type="file" 
                            wire:model="featured_image"
                            accept="image/*"
                            class="block w-full text-sm text-zinc-500 cursor-pointer
                                file:mr-4 file:py-2.5 file:px-5
                                file:rounded-xl file:border-0
                                file:text-sm file:font-semibold
                                file:bg-indigo-50 file:text-indigo-700
                                hover:file:bg-indigo-100 transition-all"
                        />
                        <div wire:loading wire:target="featured_image" class="mt-3 text-sm font-medium text-indigo-600 flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Uploading image...
                        </div>
                        @error('featured_image')
                            <p class="mt-2 text-sm font-medium text-rose-600 flex items-center gap-1">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    @if ($featured_image)
                        <div class="flex-shrink-0" wire:transition>
                            <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wider mb-2">New Image Preview</p>
                            <div class="relative">
                                <img src="{{ $featured_image->temporaryUrl() }}" class="h-36 w-56 object-cover rounded-2xl border border-indigo-200 shadow-sm" alt="Preview">
                                <div class="absolute inset-0 rounded-2xl ring-1 ring-inset ring-indigo-500/20"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <hr class="border-zinc-100">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-8">
                
                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-3">
                        Categories <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-80 overflow-y-auto p-1 -m-1">
                        @foreach($categories as $category)
                            <label class="relative flex cursor-pointer rounded-2xl border border-zinc-200 bg-zinc-50/50 p-3.5 shadow-sm hover:border-indigo-300 hover:bg-indigo-50/50 transition-colors focus-within:ring-2 focus-within:ring-indigo-500/20 group items-center">
                                <input 
                                    type="checkbox" 
                                    wire:model="selectedCategories" 
                                    value="{{ $category->id }}"
                                    class="h-4 w-4 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 transition-colors"
                                />
                                <span class="ml-3 flex items-center flex-1 truncate">
                                    <span 
                                        class="inline-block w-3 h-3 rounded-full mr-2.5 shadow-sm flex-shrink-0" 
                                        style="background-color: {{ $category->color ?? '#6366f1' }}"
                                    ></span>
                                    <span class="text-sm font-bold text-zinc-900 group-hover:text-indigo-900 transition-colors truncate">{{ $category->name }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('selectedCategories')
                        <p class="mt-3 text-sm font-medium text-rose-600 flex items-center gap-1">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-3">
                        Tags <span class="text-zinc-400 font-normal ml-1">(Optional)</span>
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 max-h-80 overflow-y-auto p-1 -m-1">
                        @foreach($tags as $tag)
                            <label class="relative flex cursor-pointer rounded-xl border border-zinc-200 bg-zinc-50/50 p-2.5 shadow-sm hover:border-indigo-300 hover:bg-indigo-50/50 transition-colors focus-within:ring-2 focus-within:ring-indigo-500/20 group items-center">
                                <input 
                                    type="checkbox" 
                                    wire:model="selectedTags" 
                                    value="{{ $tag->id }}"
                                    class="h-3.5 w-3.5 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 transition-colors"
                                />
                                <span class="ml-2 text-xs font-semibold text-zinc-700 group-hover:text-indigo-900 transition-colors truncate">{{ $tag->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('selectedTags')
                        <p class="mt-3 text-sm font-medium text-rose-600 flex items-center gap-1">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
                
            </div>

            <hr class="border-zinc-100">

            <div>
                <label class="block text-sm font-semibold text-zinc-700 mb-4">
                    Publication Status
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    
                    <label class="relative flex cursor-pointer rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm hover:border-indigo-300 hover:bg-indigo-50/30 transition-all focus-within:ring-2 focus-within:ring-indigo-500/20 group has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50 has-[:checked]:ring-1 has-[:checked]:ring-indigo-600">
                        <div class="flex h-5 items-center mt-0.5">
                            <input 
                                type="radio" 
                                wire:model="status" 
                                value="draft"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-zinc-300 transition-colors"
                            />
                        </div>
                        <div class="ml-3 flex flex-col">
                            <span class="block text-sm font-bold text-zinc-900 transition-colors group-has-[:checked]:text-indigo-900">Draft</span>
                            <span class="block text-xs text-zinc-500 mt-1 leading-relaxed">Keep it hidden from readers.</span>
                        </div>
                    </label>
                    
                    @can('publish posts')
                    <label class="relative flex cursor-pointer rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm hover:border-indigo-300 hover:bg-indigo-50/30 transition-all focus-within:ring-2 focus-within:ring-indigo-500/20 group has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/50 has-[:checked]:ring-1 has-[:checked]:ring-emerald-500">
                        <div class="flex h-5 items-center mt-0.5">
                            <input 
                                type="radio" 
                                wire:model="status" 
                                value="published"
                                class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-zinc-300 transition-colors"
                            />
                        </div>
                        <div class="ml-3 flex flex-col">
                            <span class="block text-sm font-bold text-zinc-900 transition-colors group-has-[:checked]:text-emerald-900">Published</span>
                            <span class="block text-xs text-zinc-500 mt-1 leading-relaxed">Visible to all readers.</span>
                        </div>
                    </label>

                    <label class="relative flex cursor-pointer rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm hover:border-indigo-300 hover:bg-indigo-50/30 transition-all focus-within:ring-2 focus-within:ring-indigo-500/20 group has-[:checked]:border-slate-500 has-[:checked]:bg-slate-50/50 has-[:checked]:ring-1 has-[:checked]:ring-slate-500">
                        <div class="flex h-5 items-center mt-0.5">
                            <input 
                                type="radio" 
                                wire:model="status" 
                                value="archived"
                                class="h-4 w-4 text-slate-600 focus:ring-slate-500 border-zinc-300 transition-colors"
                            />
                        </div>
                        <div class="ml-3 flex flex-col">
                            <span class="block text-sm font-bold text-zinc-900 transition-colors group-has-[:checked]:text-slate-900">Archived</span>
                            <span class="block text-xs text-zinc-500 mt-1 leading-relaxed">Store away from public view.</span>
                        </div>
                    </label>
                    @endcan
                    
                </div>
                @error('status')
                    <p class="mt-3 text-sm font-medium text-rose-600 flex items-center gap-1">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-zinc-100">
                <a 
                    href="{{ route('posts.index') }}" 
                    class="px-5 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-semibold text-zinc-700 hover:bg-zinc-50 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-zinc-200"
                    wire:navigate
                >
                    Cancel
                </a>
                <button 
                    type="submit" 
                    class="inline-flex items-center px-6 py-2.5 bg-indigo-600 border border-transparent rounded-xl text-sm font-semibold text-white shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Update Post
                </button>
            </div>
            
        </form>
    </div>
</div>